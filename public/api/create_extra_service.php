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
    $servicePrice = floatval($_POST["service_price"] ?? 0);
    $status = isset($_POST["service_status"]) && $_POST["service_status"] == 1 ? 1 : 0;

    // 🧠 Validaciones
    if ($userId <= 0) throw new Exception("Invalid or missing user ID.");
    if (empty($serviceName)) throw new Exception("Service name is required.");
    if ($servicePrice <= 0) throw new Exception("Service price must be greater than zero.");

    // 🧭 Insertar nuevo servicio
    $insert = insert_into("extra_services", [
        "user_id"       => $userId,
        "service_name"  => $serviceName,
        "service_price" => $servicePrice,
        "status"        => $status,
        "create_by"     => $creatorId,
        "created_at"    => date("Y-m-d H:i:s")
    ]);

    $insertResult = json_decode($insert, true);
    if (!$insertResult["success"]) {
        throw new Exception("Failed to create service. Please try again.");
    }

    // 📝 Registrar actividad (opcional)
    log_activity($creatorId, "create_service", "Created service: {$serviceName}", "extra_services", $insertResult["insert_id"] ?? null);

    $response = [
        "success" => true,
        "message" => "Service created successfully.",
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