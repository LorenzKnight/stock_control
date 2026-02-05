<?php
function isLocalEnv(): bool
{
	$env = getenv('APP_ENV') ?: 'production';
	return strtolower($env) === 'local';
}

function isProductionEnv(): bool
{
	return !isLocalEnv();
}

function getWsBridgeUrl(): string
{
	if (isLocalEnv()) {
		return 'http://127.0.0.1:3002/notify';
	}

	// Producción (backend → ws bridge local)
	return 'http://127.0.0.1:3002/notify';
}

function getNotifyHeaders(): array
{
	$headers = ['Content-Type: application/json'];

	if (isProductionEnv()) {
		$token = getenv('NOTIFY_TOKEN');
		if ($token) {
			$headers[] = 'X-Notify-Token: ' . $token;
		}
	}

	return $headers;
}



// Llama a esta función para enviar una notificación en tiempo real a un usuario específico
// Asegúrate de que el servidor WebSocket esté en funcionamiento y accesible
function triggerRealtimeNotification(int $userId): void
{
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

	$url     = getWsBridgeUrl();
	$headers = getNotifyHeaders();

	$ch = curl_init($url);
	curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 2,
    ]);
	$result		= curl_exec($ch);
	$httpCode	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error		= curl_error($ch);
	curl_close($ch);

	if ($result === false || $httpCode >= 400) {
        if (isProductionEnv()) {
            error_log(
                "❌ WS notification error $httpCode: $error | payload=" .
                json_encode($payload)
            );
        }
    }
}

function triggerRealtimeDirectMessage(
	int $chatId,
	int $fromUserId,
	int $toUserId,
	string $message
): void {
	$payload = [
		"type"         => "direct_message",
		"chat_id"      => $chatId,
		"from_user_id" => $fromUserId,
		"to_user_id"   => $toUserId,
		"message"      => $message
	];

	$url     = getWsBridgeUrl();
	$headers = getNotifyHeaders();

	$ch = curl_init($url);
	curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 2,
    ]);
	$result		= curl_exec($ch);
	$httpCode	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error		= curl_error($ch);
	curl_close($ch);

	if ($result === false || $httpCode >= 400) {
        if (isProductionEnv()) {
            error_log(
                "❌ WS direct_message error $httpCode: $error | payload=" .
                json_encode($payload)
            );
        }
    }
}


function sendForceLogout(int $userId): void
{
    if ($userId <= 0) {
		return;
	}

	$payload = [
		"type"    => "force_logout",
		"user_id" => $userId
	];

	$url     = getWsBridgeUrl();
	$headers = getNotifyHeaders();

	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_POST           => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER     => $headers,
		CURLOPT_POSTFIELDS     => json_encode($payload),
		CURLOPT_TIMEOUT        => 1,
	]);

	$result   = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error    = curl_error($ch);

	curl_close($ch);

	if (($result === false || $httpCode >= 400) && isProductionEnv()) {
		error_log(
			"❌ WS force_logout error $httpCode: $error | payload=" .
			json_encode($payload)
		);
	}
}