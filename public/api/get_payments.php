<?php
use App\Payments\PaymentRepository;
use App\Payments\PaymentService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Could not fetch payments",
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
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

	if ($companyId <= 0) {
		throw new Exception("User company not found.");
	}

	$search = trim((string)($_GET["search"] ?? ''));
	$paymentId = isset($_GET["payment_id"]) ? (int)$_GET["payment_id"] : null;

	$repository = new PaymentRepository();
	$service = new PaymentService($repository);

	$payments = $service->getPayments(
		$companyId,
		$search,
		$paymentId
	);

    $response = [
        "success" => true,
        "message" => "Payments fetched successfully",
        "data" => $payments
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;