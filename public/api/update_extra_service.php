<?php
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

    $serviceId     = intval($_POST["edit_service_id"] ?? 0);
    $serviceName   = trim($_POST["edit_extra_service_name"] ?? "");
    $servicePrice  = trim($_POST["edit_extra_service_price"] ?? "");
    $status        = isset($_POST["edit_service_status"]) && $_POST["edit_service_status"] == 1 ? 1 : 0;

    if ($serviceId <= 0) throw new Exception("Invalid or missing service ID.");
    if (empty($serviceName)) throw new Exception("Service name is required.");
    if ($servicePrice === "" || !is_numeric($servicePrice)) {
        throw new Exception("Valid service price is required.");
    }

    $check = json_decode(select_from(
        "extra_services",
        ["service_id", "user_id"],
        ["service_id" => $serviceId],
        ["fetch_first" => true]
    ), true);

    if (empty($check["success"]) || empty($check["data"])) {
        throw new Exception("Service not found or already deleted.");
    }

    $userId = intval($check["data"]["user_id"]);

    $duplicateCheck = json_decode(select_from(
        "extra_services",
        ["service_id"],
        [
            "user_id" => $userId,
            "service_name" => $serviceName
        ]
    ), true);

    if (!empty($duplicateCheck["success"]) && !empty($duplicateCheck["data"])) {
        foreach ($duplicateCheck["data"] as $dup) {
            if (intval($dup["service_id"]) !== $serviceId) {
                throw new Exception("This user already has an extra service with the same name.");
            }
        }
    }

    $update = update_table("extra_services", [
        "service_name"  => $serviceName,
        "service_price" => $servicePrice,
        "status"        => $status
    ], [
        "service_id" => $serviceId
    ]);

    $updateResult = json_decode($update, true);
    if (empty($updateResult["success"]) || !$updateResult["success"]) {
        throw new Exception("Failed to update extra service. Please try again.");
    }

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