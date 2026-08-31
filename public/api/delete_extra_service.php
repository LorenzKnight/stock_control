<?php
use App\ExtraServices\ExtraServiceRepository;
use App\ExtraServices\ExtraServiceService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $deleterId = $authUser["user_id"] ?? null;

    if (empty($deleterId)) {
        throw new Exception("Unauthorized access.");
    }

    $serviceId = (int)($_POST["service_id"] ?? 0);

    $repository = new ExtraServiceRepository();
	$service = new ExtraServiceService($repository);

    $serviceName = $service->deleteExtraService(
		$serviceId
	);
    
    log_activity(
        $deleterId,
        "delete extra service",
        "Deleted extra service '{$serviceName}' (ID: {$serviceId})",
        "extra_services",
        $serviceId
    );

    $response = [
        "success" => true,
        "message" => "Extra service deleted successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;