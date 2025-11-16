<?php
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

    $serviceId = intval($_POST["service_id"] ?? 0);
    if ($serviceId <= 0) {
        throw new Exception("Invalid or missing service ID.");
    }

    $check = json_decode(select_from(
        "extra_services",
        ["service_id", "service_name", "user_id"],
        ["service_id" => $serviceId],
        ["fetch_first" => true]
    ), true);

    if (empty($check["success"]) || empty($check["data"])) {
        throw new Exception("Extra service not found or already deleted.");
    }

    $serviceName = $check["data"]["service_name"] ?? "Unknown";

    $delete = delete_from("extra_services", ["service_id" => $serviceId]);
    $deleteResult = json_decode($delete, true);

    if (empty($deleteResult["success"]) || !$deleteResult["success"]) {
        throw new Exception("Failed to delete extra service. Please try again.");
    }

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