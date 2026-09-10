<?php
use App\Storages\StorageRepository;
use App\Storages\StorageService;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No storages found",
	"data" => []
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		throw new Exception("Method not allowed.");
	}

	$authUser = requireAuth();
	$companyId = (int)($authUser["company_id"] ?? 0);

	if ($companyId <= 0) {
		throw new Exception("Unauthorized access: company not found.");
	}

	$search = trim($_GET["search"] ?? '');
	$slotIdFilter = isset($_GET["slot_id"]) ? (int)$_GET["slot_id"] : 0;

	$repository = new StorageRepository();
	$service = new StorageService($repository);

	$data = $service->getStorages(
		$companyId,
		$search,
		$slotIdFilter
	);

    $response = [
		"success"	=> true,
		"message"	=> "Storages loaded successfully.",
		"data"		=> $data
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;