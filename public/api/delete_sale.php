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
    $userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) throw new Exception("User session not found.");
	if ($companyId <= 0) throw new Exception("User company not found.");

    if (!check_user_permission($userId, 'platform_admin')) {
		throw new Exception("Access denied. You do not have permission to delete data.");
	}

	$saleId = (int)($_POST["sale_id"] ?? 0);

    if ($saleId <= 0) {
        throw new Exception("Sale ID is required.");
    }

	$saleRepository = new SaleRepository();
	$inventoryRepository = new InventoryRepository();

    $inventoryService = new InventoryService($inventoryRepository);
	$saleService = new SaleService(
		$saleRepository,
		$inventoryService
	);

    if (!pg_query($sql, "BEGIN")) {
		throw new Exception("Could not start sale deletion transaction.");
	}

    $transactionStarted = true;

	$result = $saleService->deleteSale(
		$userId,
		$companyId,
		$saleId
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete sale deletion.");
	}

	$transactionStarted = false;

	try {
		log_activity(
			$userId,
			"delete sale",
			"Sale ID " . (int)$result["sale_id"] . " and associated products deleted.",
			"sales",
			(int)$result["sale_id"]
		);
	} catch (Throwable $e) {
		error_log(
			"Could not log sale deletion for sale {$saleId}: " .
				$e->getMessage()
		);
	}

    $response = [
        "success" => true,
        "message" => "Sale and associated products deleted successfully.",
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
		"img_gif" =>
			"images/sys-img/error.gif",
		"redirect_url" => null
	];
}

echo json_encode($response);
exit;