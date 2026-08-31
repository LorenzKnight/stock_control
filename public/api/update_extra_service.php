<?php
use App\ExtraServices\ExtraServiceRepository;
use App\ExtraServices\ExtraServiceService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $editorId = $authUser["user_id"] ?? null;

    if (!$editorId) throw new Exception("Unauthorized access.");

    $serviceId     = (int)($_POST["edit_service_id"] ?? 0);
    $serviceName   = trim($_POST["edit_extra_service_name"] ?? "");
    $servicePrice  = trim($_POST["edit_extra_service_price"] ?? "");
    $status        = isset($_POST["edit_service_status"]) && $_POST["edit_service_status"] == 1 ? 1 : 0;

    $repository = new ExtraServiceRepository();
	$service = new ExtraServiceService($repository);

    $service->updateExtraService(
		$serviceId,
		$serviceName,
		$servicePrice,
		$status
	);

    log_activity(
        $editorId,
        "update_extra_service",
        "Updated extra service '{$serviceName}' (ID: {$serviceId}) — status={$status}, price={$servicePrice}",
        "extra_services",
        $serviceId
    );

    $response = [
        "success" => true,
        "message" => "Extra service updated successfully.",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;