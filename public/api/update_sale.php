<?php
use App\Inventory\InventoryRepository;
use App\Inventory\InventoryService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Update failed",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

$transactionStarted = false;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $userId = (int)($authUser["user_id"] ?? 0);
    $companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) throw new Exception("User session not found.");
    if ($companyId <= 0) throw new Exception("User company not found.");

    if (!check_user_permission($userId, 'sales_handler')) {
		throw new Exception("Access denied. You do not have permission to edit data.");
	}

    $saleId = (int)($_POST["sale_id"] ?? 0);
    $customerId = (int)($_POST["customer_id"] ?? 0);

    if ($saleId <= 0 || $customerId <= 0) {
        throw new Exception("Incomplete data to update the sale.");
    }

	$customerCheck = select_from("customers",
		[
			"customer_id"
		],
		[
			"customer_id" => $customerId,
			"company_id" => $companyId
		],
		[
			"fetch_first" => true,
			"return_type" => "array"
		]
	);

	if (!is_array($customerCheck)) {
		throw new RuntimeException("Invalid database response while checking customer.");
	}

	if (empty($customerCheck["success"]) || empty($customerCheck["data"])) {
		throw new Exception("The selected customer does not exist or does not belong to this company.");
	}

    /*
	 * Validate new products before changing anything.
	 */
	if (empty($_POST["products"])) {
		throw new Exception("No products received.");
	}

    $products = json_decode($_POST["products"], true);
	
    if (!is_array($products) || empty($products)) {
		throw new Exception("Invalid products format.");
	}

    foreach ($products as $product) {
		if (
			!isset(
				$product["product_id"],
				$product["quantity"],
				$product["price"],
				$product["discount"],
				$product["total"]
			)
		) {
			throw new Exception("Invalid product data.");
		}

		if ((int)$product["product_id"] <= 0) {
			throw new Exception("Invalid product ID.");
		}

		if ((int)$product["quantity"] <= 0) {
			throw new Exception("Product quantity must be greater than zero.");
		}
	}

    $updateFields = [
        "customer_id" => $customerId,
        "price_sum" => number_format((float)$_POST["edit_price_sum"], 2, '.', ''),
        "initial" => number_format((float)$_POST["edit_initial"], 2, '.', ''),
        "delivery_date" => date('Y-m-d H:i:s', strtotime($_POST["edit_delivery_date"])),
        "remaining" => number_format((float)$_POST["edit_remaining"], 2, '.', ''),
        "interest" => (int)$_POST["edit_interest"],
		"installments_month" => (int)$_POST["edit_installments_month"],
		"no_installments" => (int)$_POST["edit_installments_month"],
        "payment_date" => date('Y-m-d H:i:s', strtotime($_POST["edit_payment_date"])),
        "due" => number_format((float)$_POST["edit_due"], 2, '.', '')
    ];

    /*
	 * From here everything must succeed together.
	 */
	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start sale update transaction.");
	}

	$transactionStarted = true;

    /*
	 * Lock and verify sale.
	 */
	$saleResult = select_from("sales",
        [
            "sales_id"
        ],
        [
            "sales_id" => $saleId,
            "company_id" => $companyId
        ],
        [
            "fetch_first" => true,
            "for_update" => true,
            "return_type" => "array"
        ]
    );

	if (!is_array($saleResult)) {
		throw new RuntimeException("Invalid database response while checking sale.");
	}

	if (empty($saleResult["success"]) || empty($saleResult["data"])) {
		throw new Exception("Sale not found.");
	}

    /*
	 * Don't allow financial history to be rewritten.
	 */
	$paymentResult = select_from("payments",
        [
            "payment_id"
        ],
        [
            "sales_id" => $saleId
        ],
        [
            "fetch_first" => true,
            "return_type" => "array"
        ]
    );

	if (!is_array($paymentResult)) {
		throw new RuntimeException("Invalid database response while checking payments.");
	}

	if (!empty($paymentResult["success"]) && !empty($paymentResult["data"])) {
		throw new Exception("This sale cannot be edited because it has registered payments.");
	}

	if (empty($paymentResult["success"]) && ($paymentResult["message"] ?? "") !== "No records found") {
		throw new RuntimeException($paymentResult["message"] ?? "Unable to verify sale payments.");
	}

	$interestResult = select_from("interest_earnings",
        [
            "earnings_id"
        ],
        [
            "sales_id" => $saleId
        ],
        [
            "fetch_first" => true,
            "return_type" => "array"
        ]
    );

	if (!is_array($interestResult)) {
		throw new RuntimeException("Invalid database response while checking financial records.");
	}

	if (!empty($interestResult["success"]) && !empty($interestResult["data"])) {
		throw new Exception("This sale cannot be edited because it has financial records.");
	}

	if (empty($interestResult["success"]) && ($interestResult["message"] ?? "") !== "No records found") {
		throw new RuntimeException($interestResult["message"] ?? "Unable to verify financial records.");
	}

    /*
	 * Get the products currently attached to the sale.
	 */
	$existingProducts = select_from("purchased_products",
        [
            "product_id",
            "quantity"
        ],
        [
            "sales_id" => $saleId
        ],
        [
            "return_type" => "array"
        ]
    );

	if (!is_array($existingProducts)) {
		throw new RuntimeException("Invalid database response while reading sale products.");
	}

	if (empty($existingProducts["success"]) && ($existingProducts["message"] ?? "") !== "No records found") {
		throw new RuntimeException($existingProducts["message"] ?? "Unable to read existing sale products.");
	}

	$oldProducts = !empty($existingProducts["success"])
        ? $existingProducts["data"]
        : [];

	$inventoryRepository = new InventoryRepository();
	$inventoryService = new InventoryService($inventoryRepository);

    /*
	 * First undo the old sale's stock movement.
	 */
	if (!empty($oldProducts)) {
		$inventoryService->restoreStockFromSale($oldProducts);
	}

    /*
	 * Then apply the stock movement
	 * corresponding to the edited sale.
	 */
	foreach ($products as $product) {
		$inventoryService->consumeStockForSale((int)$product["product_id"], (int)$product["quantity"]);
	}

    /*
	 * Update sale data.
	 */
	$result = update_table("sales",
        $updateFields,
        [
            "sales_id" => $saleId,
			"company_id" => $companyId
        ],
        [
            "return_type" => "array"
        ]
    );

	if (!is_array($result) || empty($result["success"])) {
		throw new RuntimeException("Failed to update sale. " . ($result["message"] ?? "Unknown error."));
	}

    /*
	 * Remove previous purchased-products records.
	 */
	$deleteResult = delete_from("purchased_products",
        [
            "sales_id" => $saleId
        ],
        [
            "return_type" => "array"
        ]
    );

	if (!is_array($deleteResult) ||
		(
			empty($deleteResult["success"]) &&
			stripos(
				$deleteResult["message"] ?? '',
				'No records deleted'
			) === false
		)
	) {
		throw new RuntimeException("Failed to delete old products. " . ($deleteResult["message"] ?? "Unknown error."));
	}

    foreach ($products as $product) {
        $productFields = [
            "sales_id" => $saleId,
            "customer_id" => $customerId,
            "product_id" => (int)$product["product_id"],
            "quantity" => (int)$product["quantity"],
            "price" => number_format((float)$product["price"], 2, '.', ''),
            "discount" => number_format((float)$product["discount"], 2, '.', ''),
            "total" => number_format((float)$product["total"], 2, '.', ''),
            "create_by" => $userId
        ];
        
        $insertResult = insert_into("purchased_products",
            $productFields,
            [
                "return_type" => "array"
            ]
        );

        if (!is_array($insertResult) || !$insertResult["success"]) {
            throw new RuntimeException(
				"Failed to add product: " .
				$product["product_id"] .
				". " .
				(
					$insertResult["message"] ??
					"Unknown error."
				)
			);
        }
    }

    if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete sale update.");
	}

	$transactionStarted = false;

	log_activity(
		$userId,
		"update sale",
		"Updated sale ID {$saleId}.",
		"sales",
		$saleId
	);

    $response = [
        "success" => true,
        "message" => "Sale updated successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];
} catch (Throwable $e) {
    if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

	$response = [
		"success" => false,
		"message" => $e->getMessage(),
		"img_gif" => "images/sys-img/error.gif",
		"redirect_url" => null
	];
}

echo json_encode($response);
exit;