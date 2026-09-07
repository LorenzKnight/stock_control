<?php
use App\Customers\CustomerRepository;
use App\Customers\CustomerService;

require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No customers found",
	"data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;

	if (!$userId) throw new Exception("User session not found.");

	$search = trim($_GET["search"] ?? '');

	$repository = new CustomerRepository();
	$service = new CustomerService($repository);

	$customers = $service->getCustomers(
		(int)$userId,
		$search
	);

	$response = [
		"success" => true,
		"message" => "Customers loaded.",
		"data" => $customers
	];

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;