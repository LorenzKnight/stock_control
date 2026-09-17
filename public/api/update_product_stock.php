<?php
use App\Inventory\InventoryRepository;
use App\Inventory\InventoryService;
 
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Unknown error",
	"data"    => null
];

try {

	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed.");
	}

	$authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;

	if ($userId <= 0) throw new Exception("Unauthorized access.");

	$productId = (int)($_POST["product_id"] ?? 0);
	$amount    = (int)($_POST["amount"] ?? 0);

	$repository = new InventoryRepository();
	$service = new InventoryService($repository);

	$data = $service->addStock(
		$userId,
		$productId,
		$amount
	);

	// Respuesta OK
	$response = [
		"success" => true,
		"message" => "Stock updated successfully.",
		"data" => $data
	];

} catch (Exception $e) {
	$response = [
		"success" => false,
		"message" => $e->getMessage()
	];
}

echo json_encode($response);
exit;