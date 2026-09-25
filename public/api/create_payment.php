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
    "success"       => false,
    "message"       => "Payment creation failed",
    "img_gif"       => "images/sys-img/error.gif",
    "redirect_url"  => null
];

$transactionStarted = false;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
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

    if (!check_user_permission($userId, 'data_handler')) {
        throw new Exception("Access denied. You do not have permission to edit data.");
    }

    $paymentRepository = new PaymentRepository();
	$paymentService = new PaymentService($paymentRepository);

	/*
	 * Payment + interest earning + sale due
	 * must succeed or fail together.
	 */
	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start payment transaction.");
	}

	$transactionStarted = true;

	$result = $paymentService->createPayment(
		$userId,
		$companyId,
		$_POST
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete payment transaction.");
	}

	$transactionStarted = false;

	try {
		log_activity(
			$userId,
			"create_payment",
			"Payment created for order " . (int)$result["ord_no"],
			"payments",
			(int)$result["payment_id"]
		);
	} catch (Throwable $e) {
		error_log("Could not log payment creation for payment " . (int)$result["payment_id"] . ": " . $e->getMessage());
	}

    $response = [
        "success"       => true,
        "message"       => "Payment created successfully",
        "img_gif"       => "images/sys-img/loading1.gif",
        "redirect_url"  => "payments.php"
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