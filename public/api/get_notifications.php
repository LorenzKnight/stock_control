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

	$search = strtolower(trim($_GET["search"] ?? ""));
	$where = [];
	$whereName = [];

	$where = ["to_user_id" => $userId];

	if (!empty($search)) {
		$whereName["OR"] = [
			"name ILIKE" => "%$search%",
			"surname ILIKE" => "%$search%"
		];

		$userMatches = json_decode(select_from("users", ["user_id"], $whereName), true);
		$userIds = array_column($userMatches["data"] ?? [], "user_id");

		$orBlock = [
			"notification_content ILIKE" => $search,
			"notification_type ILIKE" => $search
		];

		if (!empty($userIds)) {
			$orBlock["user_id IN"] = $userIds;
		}

		$where["OR"] = $orBlock;	
	}

	$allNotifications = json_decode(select_from("notifications", ["*"], $where, [
		"order_by" => "created_at",
		"order_direction" => "DESC"
	]), true);

	if (!$allNotifications["success"]) {
		throw new Exception("Failed to fetch all notifications.");
	}

	foreach ($allNotifications["data"] as &$notification) {
		$notifUserId = $notification["user_id"] ?? null;

		if (empty($notifUserId)) {
			$notification["from_user_name"] = "System";
			$notification["from_user_image"] = "NonProfilePic.png";
			continue;
		}

		$userData = json_decode(select_from("users", ["user_id", "name", "surname", "image"], [
			"user_id" => $notifUserId
		], ["fetch_first" => true]), true);

		$user = $userData["data"] ?? null;

		if (!$userData["success"] || !$user) {
			$notification["from_user_name"] = "Unknown User";
			$notification["from_user_image"] = "NonProfilePic.png";
			continue;
		}

		$notification["from_user_name"] = trim($user["name"] . " " . $user["surname"]);
		$notification["from_user_image"] = $user["image"] ?? "NonProfilePic.png";
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

	$response = [
		"success"   => true,
		"message"   => "Notifications retrieved successfully.",
		"count"     => (int)$notifData["count"] ?? 0,
		"data"      => $allNotifications["data"]
	];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>