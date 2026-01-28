<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");

try {
    $authUser = requireAuth();
    $userId = intval($authUser["user_id"] ?? 0);
    $chatId = intval($_GET["chat_id"] ?? 0);

    if (!$userId || !$chatId) {
        throw new Exception("Invalid request");
    }

    // Obtener last_read_at del OTRO usuario
    $other = json_decode(select_from(
        "chat_participants",
        ["last_read_at"],
        [
            "chat_id" => $chatId,
            "user_id !=" => $userId
        ],
        ["fetch_first" => true]
    ), true);

    echo json_encode([
        "success" => true,
        "last_read_at" => $other["data"]["last_read_at"] ?? null
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}