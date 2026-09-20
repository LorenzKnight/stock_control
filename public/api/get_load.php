<?php
use App\Shippings\LoadRepository;
use App\Shippings\LoadService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Load not found",
    "data" => null
];

try {
    $authUser = requireAuth();
    $companyId = $authUser["company_id"] ?? null;

    if ($companyId <= 0) throw new Exception("Company ID is required.");

    $loadId = (int)($_GET["load_id"] ?? null);

    if ($loadId <= 0) throw new Exception("Load ID is required.");

	$repository = new LoadRepository();
	$service = new LoadService($repository);

	$load = $service->getLoadById(
		$companyId,
		$loadId
	);

	$response = [
		"success" => true,
		"message" =>
			"Load data retrieved successfully.",
		"data" => $load
	];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;