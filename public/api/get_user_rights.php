<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success"   => false,
    "message"   => "No service rights found",
    "count"     => 0,
    "data"      => []
];

try {
    // 🔐 Autenticación obligatoria
    $authUser = requireAuth();
    $authUserId = $authUser["user_id"] ?? null;

    if (empty($authUserId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

    // 📥 Si se envía un user_id en GET, se usa ese; si no, el del token
    $paramUserId = $_GET["user_id"] ?? $authUserId;
    $rightId = isset($_GET['right_id']) ? (int)$_GET['right_id'] : null;
    $canAccess = isset($_GET["can_access"]) ? $_GET["can_access"] : '';

    $where = [];

    if (!empty($rightId)) {
        $where["right_id"] = $rightId;
    } else {
        $where = ["user_id" => intval($paramUserId)];

        if ($canAccess !== '') {
            $where["can_access"] = intval($canAccess);
        }
    }

    // 📋 Consultar derechos de servicio
    $rightsResponse = select_from(
        "service_rights",
        [
            "right_id",
            "user_id",
            "service_name",
            "can_access",
            "create_by",
            "created_at"
        ],
        $where,
        [
            "order_by" => "right_id",
            "order_direction" => "DESC"
        ]
    );

    $rightsData = json_decode($rightsResponse, true) ?? [];

    if (!empty($rightsData["success"]) && !empty($rightsData["data"])) {
        $response = [
            "success"   => true,
            "message"   => "Service rights loaded successfully.",
            "count"     => $rightsData["count"] ?? count($rightsData["data"]),
            "data"      => array_values($rightsData["data"])
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "No active service rights found.",
            "count"   => 0,
            "data"    => []
        ];
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;