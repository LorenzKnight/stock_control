<?php
use App\AiSales\SalesMessageRepository;
use App\AiSales\SalesMessageService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request."
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception(
			"Method not allowed."
		);
	}

	$authUser = requireAuth();

	$userId = (int)($authUser["user_id"] ?? 0);

	if ($userId <= 0) {
		throw new Exception(
			"Unauthorized access."
		);
	}

	$hasRootAccess =
		check_user_permission(
			$userId,
			"root_access"
		);

	$hasSystemAdminAccess =
		check_user_permission(
			$userId,
			"system_admin"
		);

	if (
		!$hasRootAccess &&
		!$hasSystemAdminAccess
	) {
		throw new Exception(
			"Permission denied."
		);
	}

	$salesMessageId = (int)($_POST["sales_message_id"] ?? 0);

	if ($salesMessageId <= 0) {
		throw new InvalidArgumentException(
			"Invalid sales message ID."
		);
	}

	$repository =
		new SalesMessageRepository();

	$service =
		new SalesMessageService(
			$repository
		);

	$service->sendMessage(
		$salesMessageId
	);

	log_activity(
		$userId,
		"send_ai_sales_message",
		"Marked AI Sales message ID: {$salesMessageId} as sent.",
		"sales_messages",
		$salesMessageId
	);

	$response = [
		"success" => true,
		"message" => "Sales message marked as sent successfully."
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;