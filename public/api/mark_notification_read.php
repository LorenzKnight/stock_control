<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "No se pudo marcar como leída.",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido.");
    }

    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("Sesión no encontrada.");

    $notificationId = isset($_POST["notification_id"]) ? (int)$_POST["notification_id"] : null;

    if (!$notificationId) {
        throw new Exception("ID de notificación inválido.");
    }

    $result = update_table("notifications", ["is_read" => 1], [
        "notification_id" => $notificationId,
        "to_user_id" => $userId
    ]);

    if (is_string($result)) {
        $result = json_decode($result, true);
    }

    if (!$result || !$result["success"]) {
        throw new Exception("No se pudo actualizar la notificación. " . ($result["message"] ?? ""));
    }

    $response = [
        "success" => true,
        "message" => "Notificación marcada como leída.",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => null
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;