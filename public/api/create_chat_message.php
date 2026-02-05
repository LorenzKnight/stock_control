<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request"
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$authUser = requireAuth();
	$userId = intval($authUser["user_id"] ?? 0);

	if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

	$chatId   = intval($_POST["chat_id"] ?? 0);
	$toUserId = intval($_POST["to_user_id"] ?? 0);
	$message  = trim($_POST["message"] ?? "");

	$max = 40;
	$shortMessage = (mb_strlen($message) > $max)
		? mb_substr($message, 0, $max) . '...'
		: $message;

	if ($message === "") {
		throw new Exception("Message is required.");
	}

	if (!$chatId && !$toUserId) {
		throw new Exception("chat_id or to_user_id is required.");
	}

	if ($chatId > 0) {
		$chatExists = json_decode(select_from(
			"chats",
			["chat_id"],
			["chat_id" => $chatId],
			["fetch_first" => true]
		), true);

		if (empty($chatExists["success"])) {
			// ⚠️ Chat fue borrado manualmente → forzar recreación
			$chatId = 0;
		}
	}

	if ($chatId > 0) {
		$validChat = json_decode(select_from(
			"chat_participants",
			["chat_id"],
			[
				"chat_id" => $chatId,
				"user_id IN" => [$userId, $toUserId]
			],
			["fetch_all" => true]
		), true);

		if (empty($validChat["success"]) || $validChat["count"] < 2) {
			$chatId = 0; // fuerza creación
		}
	}

	if ($chatId === 0 && $toUserId === 0) {
		throw new Exception("Invalid chat. Recipient user is missing.");
	}

	if (!$chatId) {
		// 🔎 Buscar chat directo existente
		$existingChat = json_decode(select_from(
            "chats c
            JOIN chat_participants cp1 ON cp1.chat_id = c.chat_id
            JOIN chat_participants cp2 ON cp2.chat_id = c.chat_id",
            ["c.chat_id AS chat_id"],
            [
                "RAW" => "
                    c.chat_type = 'direct'
                    AND cp1.user_id = {$userId}
                    AND cp2.user_id = {$toUserId}
                "
            ],
            ["fetch_first" => true]
        ), true);

		if (!empty($existingChat["success"]) && !empty($existingChat["data"]["chat_id"])) {
			// ✅ Chat ya existe
			$chatId = intval($existingChat["data"]["chat_id"]);
		} else {
			// 🆕 Crear chat nuevo
			pg_query($sql, "BEGIN");

			$insertChat = json_decode(insert_into("chats", [
				"chat_type"     => "direct",
				"created_at"    => date("Y-m-d H:i:s")
			],
            [
                "id" => "chat_id"
            ]), true);

			if (empty($insertChat["success"]) || empty($insertChat["id"])) {
				throw new Exception("Failed to create chat.");
			}

			$chatId = intval($insertChat["id"]);

			// Participante emisor
			$p1 = json_decode(insert_into("chat_participants", [
                "chat_id"   => $chatId,
                "user_id"   => $userId,
                "joined_at" => date("Y-m-d H:i:s")
            ]), true);

            $p2 = json_decode(insert_into("chat_participants", [
                "chat_id"   => $chatId,
                "user_id"   => $toUserId,
                "joined_at" => date("Y-m-d H:i:s")
            ]), true);

            if (empty($p1["success"]) || empty($p2["success"])) {
                throw new Exception("Failed to create chat participants.");
            }

			pg_query($sql, "COMMIT");

			notify_user(
				$toUserId,
				$userId,
				$shortMessage,
				null,
				'Direct Message',
				0
			);

			// 📩 Notificación para el emisor (YA leída)
			notify_user(
				$userId,
				$toUserId,
				$shortMessage,
				"Direct message started with user ID {$toUserId} by {$userId}.",
				'Direct Message',
				1
			);
		}
	}

	$belongs = json_decode(select_from(
		"chat_participants",
		["chat_p_id"],
		[
			"chat_id"   => $chatId,
			"user_id"   => $userId
		],
		["fetch_first" => true]
	), true);

	if (empty($belongs["success"]) || empty($belongs["data"])) {
		throw new Exception("Access denied.");
	}

	$insertMessage = json_decode(insert_into("chat_messages", [
		"chat_id"       => $chatId,
		"from_user_id"  => $userId,
		"message"       => $message,
		"created_at"    => date("Y-m-d H:i:s")
	],
	[
		"id" => "message_id"
	]), true);

	if (empty($insertMessage["success"]) || empty($insertMessage["id"])) {
		throw new Exception("Failed to send message.");
	}

	$messageId = $insertMessage["id"] ?? null;

	// 📩 Actualizacion de Notificación
	$notif = json_decode(select_from(
		"notifications",
		["notification_id", "to_user_id"],
		[
			"notification_link"=> $chatId,
			"notification_type"=> "Direct Message"
		],
		["fetch_all" => true]
	), true);

	if (!empty($notif["success"])) {
		foreach ($notif["data"] as $n) {

			$isMine = ((int)$n["to_user_id"] === $userId);

			update_table(
				"notifications",
				[
					"notification_content"	=> $isMine ? null : $shortMessage,
					"is_read"				=> $isMine ? 1 : 0,
					"created_at"			=> date("Y-m-d H:i:s")
				],
				[
					"notification_id" => $n["notification_id"]
				]
			);
		}
	}

	// 1️⃣ Evento realtime para el chat
	triggerRealtimeDirectMessage(
		$chatId,
		$userId,
		$toUserId,
		$message
	);

	// ⚡ Push realtime (WebSocket)
	triggerRealtimeNotification($toUserId);

	$updateRead = json_decode(update_table(
        "chat_participants",
        ["last_read_at" => date("Y-m-d H:i:s")],
        [
            "chat_id" => $chatId,
            "user_id" => $userId
        ]
    ), true);

    if (empty($updateRead["success"])) {
        // ⚠️ No rompemos el flujo del chat
        // Solo registramos para debugging o auditoría
        error_log("[CHAT] Failed to update last_read_at | chat_id={$chatId} | user_id={$userId}");
    }

	$response = [
		"success" => true,
		"message" => "Message sent successfully.",
		"chat_id" => $chatId,
		"message_id" => $messageId
	];

} catch (Exception $e) {

	@pg_query($sql, "ROLLBACK");

	$response = [
		"success" => false,
		"message" => $e->getMessage()
	];
}

echo json_encode($response);
exit;