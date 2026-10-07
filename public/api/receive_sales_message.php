<?php
use App\AiSales\SalesMessageRepository;
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesMessageService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request."
];

$transactionStarted = false;

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


	$conversationId = (int)($_POST["conversation_id"] ?? 0);
	$message = (string)($_POST["message"] ?? '');
	$subject = isset($_POST["subject"]) ? (string)$_POST["subject"] : null;
	$providerMessageId = isset($_POST["provider_message_id"]) ? (string)$_POST["provider_message_id"] : null;


	if ($conversationId <= 0) {
		throw new InvalidArgumentException(
			"Invalid conversation ID."
		);
	}


	$messageRepository = new SalesMessageRepository();

	$conversationRepository = new SalesConversationRepository();

	$leadRepository = new SalesLeadRepository();

	$service = new SalesMessageService(
        $messageRepository,
        $conversationRepository,
        $leadRepository
    );


	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException(
			"Could not start AI Sales inbound message transaction."
		);
	}

	$transactionStarted = true;


	$result = $service->receiveMessage(
		$conversationId,
		$message,
		$subject,
		$providerMessageId
	);


	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException(
			"Could not complete AI Sales inbound message transaction."
		);
	}

	$transactionStarted = false;


	try {
		$salesMessageId = (int)($result["sales_message_id"] ?? 0);

		log_activity(
			$userId,
			"receive_ai_sales_message",
			"Received AI Sales inbound message ID: {$salesMessageId}",
			"sales_messages",
			$salesMessageId
		);

	} catch (Throwable $e) {
		error_log(
			"Could not log AI Sales inbound message: " .
			$e->getMessage()
		);
	}


	$response = [
		"success" => true,
		"message" => "Inbound sales message received successfully.",
		"data" => $result
	];

} catch (Throwable $e) {
	if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

	$response["message"] = $e->getMessage();
}


echo json_encode($response);

exit;