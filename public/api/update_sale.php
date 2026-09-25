<?php
use App\Sales\SaleRepository;
use App\Sales\SaleService;
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

	/*
	 * Products continue arriving from sales.js
	 * as JSON inside FormData.
	 */
	$productsRaw = (string)($_POST["products"] ?? '');

	if ($productsRaw === '') {
		throw new Exception("No products received.");
	}

	$products = json_decode($productsRaw, true);

	if (!is_array($products) || empty($products)) {
		throw new Exception("Invalid products format.");
	}

	/*
	 * Translate the current frontend field
	 * names into SaleService field names.
	 */
	$data = [
		"customer_id" => $customerId,
		"products" => $products
	];

	$fieldMap = [
		"edit_price_sum" => "price_sum",
		"edit_initial" => "initial",
		"edit_delivery_date" => "delivery_date",
		"edit_interest_type" => "interest_type",
		"edit_interest" => "interest",
		"edit_installments_month" => "installments_month",
		"edit_payment_date" => "payment_date"
	];

	foreach ($fieldMap as $postField => $serviceField) {
		if (array_key_exists($postField, $_POST)) {
			$data[$serviceField] = $_POST[$postField];
		}
	}

	$saleRepository = new SaleRepository();
	$inventoryRepository = new InventoryRepository();

	$inventoryService = new InventoryService($inventoryRepository);
	$saleService = new SaleService(
		$saleRepository,
		$inventoryService
	);

	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start sale update transaction.");
	}

	$transactionStarted = true;

	$result = $saleService->updateSale(
		$userId,
		$companyId,
		$saleId,
		$data
	);

    if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete sale update.");
	}

	$transactionStarted = false;

	/*
	 * The sale is already committed.
	 * Logging must not turn a valid update
	 * into a failed API response.
	 */
	try {
		log_activity(
			$userId,
			"update sale",
			"Updated sale ID " . (int)$result["sale_id"] . ".",
			"sales",
			(int)$result["sale_id"]
		);
	} catch (Throwable $e) {
		error_log(
			"Could not log sale update for sale {$saleId}: " .
				$e->getMessage()
		);
	}

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