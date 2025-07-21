<?php
// usa esto en el navegador para ver los cambios recientes
// http://localhost:8889/api/test_notify.php?to_user_id=1
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$userId = isset($_GET["to_user_id"]) ? intval($_GET["to_user_id"]) : null;

if (!$userId || $userId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Parámetro user_id inválido o ausente."
    ]);
    exit;
}

try {
    // Llamar la función que lanza la notificación WebSocket
    triggerRealtimeNotification($userId);

    echo json_encode([
        "success" => true,
        "message" => "🔔 Notificación enviada correctamente al usuario ID $userId"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al enviar notificación: " . $e->getMessage()
    ]);
}
exit;