<?php

use App\AiSales\AiSalesAgentClient;
use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesContactRepository;
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesLeadRepository;
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


	$hasRootAccess = check_user_permission(
        $userId,
        "root_access"
    );

	$hasSystemAdminAccess = check_user_permission(
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

	if ($conversationId <= 0) {
		throw new InvalidArgumentException(
			"Invalid conversation ID."
		);
	}


	$messageRepository =
		new SalesMessageRepository();

	$conversationRepository =
		new SalesConversationRepository();

	$leadRepository =
		new SalesLeadRepository();

	$companyRepository =
		new SalesCompanyRepository();

	$contactRepository =
		new SalesContactRepository();

	$aiSalesAgentClient =
		new AiSalesAgentClient();


	$service =
		new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository,
			$companyRepository,
			$contactRepository,
			$aiSalesAgentClient
		);


	$result =
		$service->generateAiDraft(
			$conversationId
		);


	try {
		$salesMessageId = (int)($result["sales_message_id"] ?? 0);

		log_activity(
			$userId,
			"generate_ai_sales_reply",
			"Generated AI Sales draft message ID: {$salesMessageId}",
			"sales_messages",
			$salesMessageId
		);

	} catch (Throwable $e) {
		error_log(
			"Could not log AI Sales draft generation: " .
			$e->getMessage()
		);
	}


	$response = [
		"success" => true,
		"message" => "AI sales reply generated successfully.",
		"data" => $result
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}


echo json_encode(
	$response
);

exit;