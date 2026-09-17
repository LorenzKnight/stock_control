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
    "message" => "Deletion failed",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

$transactionStarted = false;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $userId = $authUser["user_id"] ?? null;

    if ($userId <= 0) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'platform_admin')) {
		throw new Exception("Access denied. You do not have permission to delete data.");
	}

    if (empty($_POST["sale_id"])) {
        throw new Exception("Sale ID is required.");
    }

    $saleId = (int)$_POST["sale_id"] ?? 0;

    if ($saleId <= 0) {
		throw new Exception("Sale ID is required.");
	}

    if (!pg_query($sql, "BEGIN")) {
		throw new Exception("Could not start sale deletion transaction.");
	}

    $transactionStarted = true;

    /*
	 * Lock the sale while we process deletion.
	 */
    $saleResult = select_from("sales",
		[
			"sales_id",
			"customer_id"
		],
		[
			"sales_id" => $saleId
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
	 * IMPORTANT:
	 * A sale with registered payments cannot be deleted.
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
		throw new Exception("This sale cannot be deleted because it has registered payments.");
	}

	if (empty($paymentResult["success"]) && ($paymentResult["message"] ?? "") !== "No records found") {
		throw new RuntimeException($paymentResult["message"] ?? "Unable to verify sale payments.");
	}

    /*
	 * Extra protection:
	 * don't delete a sale containing financial
	 * interest history either.
	 */
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
		throw new RuntimeException("Invalid database response while checking interest records.");
	}

    if (!empty($interestResult["success"]) && !empty($interestResult["data"])) {
		throw new Exception("This sale cannot be deleted because it has financial records.");
	}

	if (empty($interestResult["success"]) && ($interestResult["message"] ?? "") !== "No records found") {
		throw new RuntimeException($interestResult["message"] ?? "Unable to verify sale financial records.");
	}

    /*
	 * Get products sold in this sale.
	 */
	$productResult = select_from("purchased_products",
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

	if (!is_array($productResult)) {
		throw new RuntimeException("Invalid database response while checking sale products.");
	}

    $inventoryRepository = new InventoryRepository();
    $inventoryService = new InventoryService($inventoryRepository);

    if (!empty($productResult["success"]) && !empty($productResult["data"])) {
		$productsToRestore = $productResult["data"];

		/*
		 * Restore quantities to inventory.
		 */
		$inventoryService->restoreStockFromSale($productsToRestore);

		/*
		 * Remove sale/product associations.
		 */
		$deleteProductsResult = delete_from("purchased_products",
            [
                "sales_id" => $saleId
            ],
            [
                "return_type" => "array"
            ]
        );

		if (!is_array($deleteProductsResult) || empty($deleteProductsResult["success"])) {
			throw new RuntimeException("Failed to delete associated products.");
		}
	}
    elseif (($productResult["message"] ?? "") !== "No records found") {
		throw new RuntimeException($productResult["message"] ?? "Unable to read associated products.");
	}

    /*
	 * Finally delete the sale itself.
	 */
	$deleteSaleResult = delete_from("sales",
        [
            "sales_id" => $saleId
        ],
        [
            "return_type" => "array"
        ]
    );

	if (!is_array($deleteSaleResult) || empty($deleteSaleResult["success"])) {
		throw new RuntimeException("Failed to delete sale.");
	}

	if ((int)($deleteSaleResult["count"] ?? 0) !== 1) {
		throw new RuntimeException("Sale was not deleted.");
	}

	/*
	 * Everything succeeded.
	 */
	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete sale deletion.");
	}

	$transactionStarted = false;

    log_activity(
        $userId,
        "delete sale",
        "Sale ID $saleId and associated products deleted.",
        "sales",
        $saleId
    );

    $response = [
        "success" => true,
        "message" => "Sale and associated products deleted successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];
} catch (Exception $e) {
    if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

    $response = [
		"success" => false,
		"message" => $e->getMessage(),
		"img_gif" =>
			"images/sys-img/error.gif",
		"redirect_url" => null
	];
}

echo json_encode($response);
exit;