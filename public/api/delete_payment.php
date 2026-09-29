<?php
use App\Payments\PaymentRepository;
use App\Payments\PaymentService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "../images/sys-img/error.gif",
	"redirect_url" => ""
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

	$paymentId = (int)($_POST["payment_id"] ?? 0);

	if ($paymentId <= 0) {
		throw new Exception("Missing or invalid payment ID.");
	}

	$repository = new PaymentRepository();
	$service = new PaymentService($repository);

	/*
	 * Payment deletion, interest deletion
	 * and sale balance restoration must
	 * succeed or fail together.
	 */
	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start payment deletion transaction.");
	}

	$transactionStarted = true;

	$result = $service->deletePayment(
		$userId,
		$companyId,
		$paymentId
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete payment deletion.");
	}

	$transactionStarted = false;

	/*
	 * Activity logging happens after COMMIT.
	 * A logging failure must not undo an
	 * otherwise successful payment deletion.
	 */
	try {
		log_activity(
			$userId,
			"delete_payment",
			"User deleted a payment (ID: {$paymentId}).",
			"payment",
			$paymentId
		);
	} catch (Throwable $e) {
		error_log("Could not log payment deletion for payment {$paymentId}: " . $e->getMessage());
	}

	$response = [
		"success" => true,
		"message" => "Payment deleted successfully.",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => ""
	];

} catch (Throwable $e) {
	if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

	$response = [
		"success" => false,
		"message" => $e->getMessage(),
		"img_gif" => "../images/sys-img/error.gif",
		"redirect_url" => ""
	];
}

echo json_encode($response);
exit;
