<?php
use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesCompanyService;

require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No sales companies found.",
	"data" => []
];

try {
	$search = trim($_GET["search"] ?? '');

	$repository = new SalesCompanyRepository();

	$service = new SalesCompanyService(
		$repository
	);

	$companies = $service->getCompanies(
		$search
	);

	$response = [
		"success" => true,
		"message" => empty($companies)
			? "No sales companies found."
			: "Sales companies loaded.",
		"data" => $companies
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;