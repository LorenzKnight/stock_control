<?php
use App\Sales\SaleRepository;
use App\Sales\SaleService;
use App\Inventory\InventoryRepository;
use App\Inventory\InventoryService;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No sales found",
	"data" => []
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		throw new Exception("Method not allowed.");
	}

	$authUser = requireAuth();
	$userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) {
        throw new Exception("Unauthorized access. User not found or invalid token.");
    }

	if ($companyId <= 0) {
		throw new Exception("Company ID is required.");
	}

	$search = trim((string)($_GET["search"] ?? ''));

	$saleRepository = new SaleRepository();
	$inventoryRepository = new InventoryRepository();

	$inventoryService = new InventoryService($inventoryRepository);
	$saleService = new SaleService(
		$saleRepository,
		$inventoryService
	);

	$sales = $saleService->getSales(
		$companyId,
		$search
	);

	$response = [
		"success" => true,
		"message" => "Sales loaded successfully.",
		"data" => $sales
	];
} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;