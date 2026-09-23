<?php
use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesLeadService;

require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No sales leads found.",
	"data" => []
];

try {
	$salesCompanyId = (int)($_GET["sales_company_id"] ?? 0);

	$repository = new SalesLeadRepository();
	$service = new SalesLeadService($repository);

	$leads = $service->getLeadsByCompany(
        $salesCompanyId
    );

	$response = [
		"success" => true,
		"message" => empty($leads)
			? "No sales leads found."
			: "Sales leads loaded.",
		"data" => $leads
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;