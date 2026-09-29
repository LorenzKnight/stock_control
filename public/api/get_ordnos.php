<?php
use App\Payments\PaymentRepository;
use App\Payments\PaymentService;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "No orders found",
    "data" => []
];

try {

	$authUser = requireAuth();
	$userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

	if ($userId <= 0) throw new Exception("User session not found");
	if ($companyId <= 0) throw new Exception("User company not found.");

	$search = trim((string)($_GET["search"] ?? ''));

	$repository = new PaymentRepository();
	$service = new PaymentService($repository);

	$orders = $service->getOrderSuggestions(
		$companyId,
		$search
	);

	$response = [
        "success" => true,
        "message" => "Orders found",
		"data" => $orders
    ];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;