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

    // 🔒 Autenticación JWT obligatoria
    $authUser = requireAuth();
    $editorId = $authUser["user_id"] ?? null;
    if (!$editorId) throw new Exception("Unauthorized access.");

    // 🧩 Datos del formulario
    $rightId     = intval($_POST["edit_right_id"] ?? 0);
    $serviceName = trim($_POST["edit_service_name"] ?? "");
    $canAccess   = isset($_POST["edit_can_access"]) && $_POST["edit_can_access"] == 1 ? 1 : 0;

    // 🧠 Validaciones
    if ($rightId <= 0) throw new Exception("Invalid or missing right ID.");
    if (empty($serviceName)) throw new Exception("Service name is required.");

    // ⚙️ Verificar que el registro exista antes de actualizar
    $check = json_decode(select_from(
        "service_rights",
        ["right_id"],
        ["right_id" => $rightId],
        ["fetch_first" => true]
    ), true);

    if (empty($check["success"]) || empty($check["data"])) {
        throw new Exception("Service right not found or already deleted.");
    }

    // 🧭 Actualizar derecho
    $update = update_table("service_rights", [
        "service_name" => $serviceName,
        "can_access"   => $canAccess
    ], [
        "right_id" => $rightId
    ]);

    $updateResult = json_decode($update, true);
    if (empty($updateResult["success"]) || !$updateResult["success"]) {
        throw new Exception("Failed to update user right. Please try again.");
    }

    // 📝 Registrar actividad
    log_activity(
        $editorId,
        "update user right",
        "Updated user right '{$serviceName}' (ID: {$rightId}) — can_access={$canAccess}",
        "service_rights",
        $rightId
    );

    $response = [
        "success" => true,
        "message" => "User right updated successfully.",
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