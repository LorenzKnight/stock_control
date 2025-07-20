<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success"   => false,
    "message"   => "Error fetching notifications",
    "count"     => 0,
    "data"      => []
];

try {
    $userId = $_SESSION['sc_UserId'] ?? null;
    if (!$userId) {
        throw new Exception("Unauthorized: No user session found.");
    }

    // Cargar las notificaciones más recientes no leídas para el usuario
    $notifResponse = select_from(
        "notifications", ["*"],
        [
            "user_id" => $userId
        ],
        [
            "order_by" => "created_at",
            "order_direction" => "DESC",
            // "limit" => 10
        ]
    );

    $notifData = json_decode($notifResponse, true);

    if ($notifData["success"] && !empty($notifData["data"])) {

        require_once('../logic/realtime.php'); // si triggerRealtimeNotification está en otro archivo
        triggerRealtimeNotification($userId);
        
        $response = [
            "success"   => true,
            "message"   => "Notifications retrieved successfully.",
            "count"     => count($notifData["data"]),
            "data"      => $notifData["data"]
        ];
    } else {
        $response["message"] = "No notifications found.";
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;