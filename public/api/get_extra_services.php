<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success"   => false,
    "message"   => "No extra services found",
    "count"     => 0,
    "data"      => []
];

try {
    $paramUserId = $_GET["user_id"] ?? null;

    if ($paramUserId) {
        $where = ["user_id" => intval($paramUserId)];
    } else {
        // fallback: usar usuario actual
        $userId = $_SESSION["sc_UserId"] ?? null;

        if (!$userId) {
            throw new Exception("Missing user_id parameter or invalid session.");
        }
        
        $where = ["user_id" => intval($userId)];
    }

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