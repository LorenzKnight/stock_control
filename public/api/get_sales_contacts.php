<?php
use App\AiSales\SalesContactRepository;
use App\AiSales\SalesContactService;

require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No sales contacts found.",
	"data" => []
];

try {
	$salesCompanyId = (int)($_GET["sales_company_id"] ?? 0);

	$search = trim($_GET["search"] ?? '');

	$repository = new SalesContactRepository();
	$service = new SalesContactService($repository);

	$contacts = $service->getContactsByCompany(
        $salesCompanyId,
        $search
    );

	$response = [
		"success" => true,
		"message" => empty($contacts)
			? "No sales contacts found."
			: "Sales contacts loaded.",
		"data" => $contacts
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;