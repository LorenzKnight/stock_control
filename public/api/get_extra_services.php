<?php
use App\ExtraServices\ExtraServiceRepository;
use App\ExtraServices\ExtraServiceService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success"   => false,
    "message"   => "No extra services found",
    "count"     => 0,
    "data"      => []
];

try {
    $authUser = requireAuth();
    $authUserId = $authUser["user_id"] ?? null;

    if (empty($authUserId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

    $paramUserId = $_GET["user_id"] ?? $authUserId;
    $serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;

    $statusParam = $_GET["status"] ?? '';
    $status = $statusParam !== ''
		? (int)$statusParam
		: null;

    $repository = new ExtraServiceRepository();
	$service = new ExtraServiceService($repository);

	$extraServices = $service->getExtraServices(
		$paramUserId,
		$serviceId,
		$status
	);

	$response = [
		"success"   => true,
		"message"   => "Extra services loaded successfully.",
		"count" 	=> count($extraServices),
		"data" 		=> $extraServices
	];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;