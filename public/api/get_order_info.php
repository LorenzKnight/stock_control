<?php
use App\Payments\PaymentRepository;
use App\Payments\PaymentService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header('Content-Type: application/json');

$response = [
	"success" => false,
	"message" => "Order not found",
	"data" => null
];

try {
	if (!isset($_GET["ord_no"]) || !is_numeric($_GET["ord_no"])) {
		throw new Exception("Invalid order number.");
	}

	$authUser = requireAuth();
	$userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

	if ($userId <= 0) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

	if ($companyId <= 0) {
		throw new Exception("User company not found.");
	}

	if (!isset($_GET["ord_no"]) || !is_numeric($_GET["ord_no"])) {
		throw new InvalidArgumentException("Invalid order number.");
	}

	$ordNo = (int)$_GET["ord_no"];

	if ($ordNo <= 0) {
		throw new InvalidArgumentException("Invalid order number.");
	}

	$paymentRepository = new PaymentRepository();
	$paymentService = new PaymentService($paymentRepository);

	$order = $paymentService
		->getOrderInfo(
			$companyId,
			$ordNo
		);

	$response = [
		"success" => true,
		"message" => "Order found",
		"data" => $order
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;