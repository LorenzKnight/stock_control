<?php
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesConversationService;

require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No sales conversations found.",
	"data" => []
];

try {
	$salesLeadId = (int)(
		$_GET["sales_lead_id"] ?? 0
	);

	$repository = new SalesConversationRepository();
	$service = new SalesConversationService($repository);

	$conversations = $service->getConversationsByLead(
		$salesLeadId
	);

	$response = [
		"success" => true,
		"message" => empty($conversations)
			? "No sales conversations found."
			: "Sales conversations loaded.",
		"data" => $conversations
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;