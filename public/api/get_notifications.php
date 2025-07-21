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
    $notifData = json_decode(select_from("notifications", ["*"], [
            "to_user_id" => $userId,
		    "is_read" => 0
        ],
        [
            "order_by" => "created_at",
            "order_direction" => "DESC"
        ]
    ), true);

    if ($notifData["success"] && !empty($notifData["data"])) {
        $response = [
            "success"   => true,
            "message"   => "Notifications retrieved successfully.",
            "count"     => (int)$notifData["count"] ?? 0,
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