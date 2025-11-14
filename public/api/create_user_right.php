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
    // 🚫 Solo permitir POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    // 🔒 Autenticación por token JWT
    $authUser = requireAuth();
    $creatorId = $authUser["user_id"];

    // 🧩 Recibir datos del formulario
    $userId = intval($_POST["user_id"] ?? 0);
    $serviceName = trim($_POST["service_name"] ?? "");
    $canAccess = isset($_POST["can_access"]) && $_POST["can_access"] == 1 ? 1 : 0;

    // 🧠 Validaciones
    if ($userId <= 0) throw new Exception("Invalid or missing user ID.");
    if (empty($serviceName)) throw new Exception("Service name is required.");

    // 🧭 Insertar nuevo derecho (service_right)
    $insert = insert_into("service_rights", [
        "user_id"      => $userId,
        "service_name" => $serviceName,
        "can_access"   => $canAccess,
        "create_by"    => $creatorId,
        "created_at"   => date("Y-m-d H:i:s")
    ]);

    $insertResult = json_decode($insert, true);
    if (empty($insertResult["success"]) || !$insertResult["success"]) {
        throw new Exception("Failed to create user right. Please try again.");
    }

    // 📝 Registrar actividad
    log_activity(
        $creatorId,
        "create user right",
        "Created user right '{$serviceName}' with can_access={$canAccess}",
        "service_rights",
        $insertResult["insert_id"] ?? null
    );

    $response = [
        "success" => true,
        "message" => "User right created successfully.",
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