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
    // 🔐 Verificar sesión
    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    // 🔎 Obtener el usuario padre si existe
    $userData = json_decode(select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]), true);
    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }

    $userInfo = $userData["data"];
    $altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];

    // 🔸 Condiciones base
    $where = [
        "user_id" => $altUser
    ];

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

    $extraServicesData = json_decode($extraServicesResponse, true);

    if ($extraServicesData["success"] && !empty($extraServicesData["data"])) {
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