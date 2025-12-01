<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "data"    => []
];

try {

    // ✅ Validar método
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    // 🔐 Usuario autenticado
    $authUser = requireAuth();
    $userId   = $authUser["user_id"] ?? null;

    if (empty($userId)) {
        throw new Exception("Unauthorized");
    }

    // 📦 Datos desde frontend
    $endpoint = $_POST["endpoint"] ?? null;
    $p256dh   = $_POST["p256dh"] ?? null;
    $authKey  = $_POST["auth"] ?? null;

    if (empty($endpoint) || empty($p256dh) || empty($authKey)) {
        throw new Exception("INVALID_SUBSCRIPTION_DATA");
    }

    $deviceType = $_POST["device_type"] ?? null;   // mobile | desktop
    $deviceName = $_POST["device_name"] ?? null;   // Chrome, iPhone, etc
    $userAgent  = $_SERVER["HTTP_USER_AGENT"] ?? null;

    // 🔁 Desactivar suscripciones duplicadas (mismo endpoint)
    update_table(
        "push_subscriptions",
        ["is_active" => false],
        [
            "user_id"  => $userId,
            "endpoint" => $endpoint
        ]
    );

    // ✅ Insertar nueva suscripción
    $insertResult = insert_into("push_subscriptions", [
        "user_id"     => $userId,
        "endpoint"    => $endpoint,
        "p256dh"      => $p256dh,
        "auth"        => $authKey,
        "device_type" => $deviceType,
        "device_name" => $deviceName,
        "user_agent"  => $userAgent,
        "is_active"   => true
    ]);

    $insertData = json_decode($insertResult, true);

    if (!$insertData["success"]) {
        throw new Exception("ERROR_SAVING_SUBSCRIPTION");
    }

    $response = [
        "success" => true,
        "message" => "Push subscription saved successfully",
        "data"    => []
    ];

} catch (Exception $e) {

    http_response_code(400);

    $response = [
        "success" => false,
        "reason"  => $e->getMessage(),
        "data"    => []
    ];
}

echo json_encode($response);
exit;
