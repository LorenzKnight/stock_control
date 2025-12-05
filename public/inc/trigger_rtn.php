<?php
// Llama a esta función para enviar una notificación en tiempo real a un usuario específico
// Asegúrate de que el servidor WebSocket esté en funcionamiento y accesible
function triggerRealtimeNotification($userId) {
	$headers = ['Content-Type: application/json'];

	$res = json_decode(select_from("notifications", ["*"], [
		"to_user_id" => $userId,
		"is_read" => 0
	], [
		"order_by" => "created_at",
		"order_direction" => "DESC",
		"limit" => 1,
		"fetch_first" => true
	]), true);

	if (!$res["success"] || empty($res["data"])) {
		return;
	}

	$notif = $res["data"];

	$userData = json_decode(select_from("users", ["user_id", "name", "surname", "image"], [
		"user_id" => $notif["from_user_id"] ?? null
	], [
		"fetch_first" => true
	]), true);
	
	$userInfo = $userData["data"] ?? [];
	
	if (empty($notif["from_user_id"])) {
		$userInfo["from_user_name"] = "System";
		$userInfo["from_user_image"] = "NonProfilePic.png";
	} else if (!$userData["success"] || empty($userInfo)) {
		$userInfo["from_user_name"] = "Unknown User";
		$userInfo["from_user_image"] = "NonProfilePic.png";
	} else {
		$userInfo["from_user_name"] = trim(($userInfo["name"] ?? '') . ' ' . ($userInfo["surname"] ?? ''));
		$userInfo["from_user_image"] = $userInfo["image"] ?? "NonProfilePic.png";
	}

	$payload = [
		"type" => "notification",
		"notification_type" => ($notif["notification_type"] ?? "Info") . " from " . ($userInfo["from_user_name"] ?? "System"),
		"to_user_id" => (int)$userId,
		"message" => $notif["notification_content"] ?? "Notificación sin contenido",
		"link" => $notif["notification_link"] ?? null
	];

	// LOCAL (dev) 
	if (file_exists('/.dockerenv') || getenv('USE_DOCKER_BRIDGE') === '1') {
		$url = 'http://host.docker.internal:3002/notify';
		// $url = 'https://www.allstockcontrol.com/notify';
	} else {
		$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
		$hostname = explode(':', $hostname)[0];
		$url = "http://{$hostname}:3002/notify";
	}

	// Recomendado en producción: llamar al bridge local (127.0.0.1:3002)
	// if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
	// 	// Prod detrás de Nginx SSL
	// 	$url = 'http://127.0.0.1:3002/notify';

	// 	// Si tu ws-server exige token:
	// 	$notifyToken = getenv('NOTIFY_TOKEN');
	// 	if (!empty($notifyToken)) {
	// 		$headers[] = 'X-Notify-Token: ' . $notifyToken;
	// 	}
	// } else {
	// 	// Local/dev
	// 	$url = 'http://127.0.0.1:3002/notify';
	// }

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	$result		= curl_exec($ch);
	$httpCode	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error		= curl_error($ch);
	curl_close($ch);

	if ($result === false || $httpCode >= 400) {
		error_log("❌ WS bridge error $httpCode: $error | url=$url | payload=" . json_encode($payload));
	} else {
		error_log("✅ WS bridge OK ($httpCode): $result");
	}
}


function sendForceLogout($userId) {
    // $url = "http://127.0.0.1:3002/notify";
	$url = 'http://host.docker.internal:3002/notify';

    $payload = [
        "message" => "force_logout",
        "user_id" => $userId
    ];

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($payload),
            "timeout" => 1
        ]
    ];

    $context  = stream_context_create($options);
    @file_get_contents($url, false, $context);
}