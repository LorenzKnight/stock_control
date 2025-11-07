<?php
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

    $where = ["user_id" => intval($paramUserId)];

    // 🔍 Filtro opcional por status
    $status = $_GET["status"] ?? '';
    if ($status !== '') {
        $where["status"] = intval($status);
    }

    // 📦 Consultar servicios extra
    $extraServicesResponse = select_from(
        "extra_services",
        [
            "service_id",
            "user_id",
            "service_name",
            "service_price",
            "status",
            "create_by",
            "created_at"
        ],
        $where,
        [
            "order_by" => "service_id",
            "order_direction" => "ASC"
        ]
    );

    $extraServicesData = json_decode($extraServicesResponse, true) ?? [];

    if (!empty($extraServicesData["success"]) && !empty($extraServicesData["data"])) {
        $response = [
            "success"   => true,
            "message"   => "Extra services loaded successfully.",
            "count"     => $extraServicesData["count"] ?? count($extraServicesData["data"]),
            "data"      => array_values($extraServicesData["data"])
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "No active extra services found.",
            "count"   => 0,
            "data"    => []
        ];
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;