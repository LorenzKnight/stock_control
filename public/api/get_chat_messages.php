<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");

try {

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $userId = intval($authUser["user_id"] ?? 0);
    $chatId = intval($_GET["chat_id"] ?? 0);

    if (!$userId || !$chatId) {
        throw new Exception("Invalid request");
    }

    /**
     * 1️⃣ Validar que el usuario pertenece al chat
     */
    $belongs = json_decode(select_from(
        "chat_participants",
        ["chat_p_id"],
        [
            "chat_id" => $chatId,
            "user_id" => $userId
        ],
        ["fetch_first" => true]
    ), true);

    if (empty($belongs["success"])) {
        throw new Exception("Access denied");
    }

    /**
     * 2️⃣ Obtener mensajes del chat
     */
    $messages = json_decode(select_from(
		"chat_messages",
		[
			"message_id",
			"from_user_id",
			"message",
			"created_at",

			// 🔹 is_read SIN romper arquitectura
			"(
				SELECT
					CASE
						WHEN chat_messages.created_at <= cp.last_read_at THEN 1
						ELSE 0
					END
				FROM chat_participants cp
				WHERE cp.chat_id = chat_messages.chat_id
				AND cp.user_id != chat_messages.from_user_id
				LIMIT 1
			) AS is_read"
		],
		[
			"chat_id" => $chatId
		],
		[
			"order_by" => "created_at",
			"order_direction" => "asc"
		]
	), true);

    /**
     * 3️⃣ Marcar chat como leído (solo este usuario)
     */
    update_table(
        "chat_participants",
        ["last_read_at" => date("Y-m-d H:i:s")],
        [
            "chat_id" => $chatId,
            "user_id" => $userId
        ]
    );

    echo json_encode([
        "success"  => true,
        "messages" => $messages["data"] ?? []
    ]);
    exit;

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}