<?php
use App\AiSales\SalesMessageRepository;
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesMessageService;

require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No sales messages found.",
	"data" => []
];

try {
	$conversationId = (int)(
		$_GET["conversation_id"] ?? 0
	);

	$messageRepository =
		new SalesMessageRepository();

	$conversationRepository =
		new SalesConversationRepository();

	$leadRepository =
		new SalesLeadRepository();

	$service =
		new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

	$messages =
		$service->getMessagesByConversation(
			$conversationId
		);

	$response = [
		"success" => true,
		"message" => empty($messages)
			? "No sales messages found."
			: "Sales messages loaded.",
		"data" => $messages
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;