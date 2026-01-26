<?php
require_once 'db.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;
$chatId = $_GET['chat_id'] ?? null;

if (!$userId || !$chatId) {
	echo json_encode(['success' => false]);
	exit;
}

/**
 * 1️⃣ Validar acceso
 */
$sql = "
	SELECT 1
	FROM chat_participants
	WHERE chat_id = :chat_id
	AND user_id = :user_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
	':chat_id' => $chatId,
	':user_id' => $userId
]);

if (!$stmt->fetch()) {
	echo json_encode(['success' => false, 'error' => 'Access denied']);
	exit;
}

/**
 * 2️⃣ Obtener mensajes
 */
$sql = "
	SELECT 
		m.message_id,
		m.chat_id,
		m.user_id,
		m.message,
		m.created_at
	FROM chat_messages m
	WHERE m.chat_id = :chat_id
	ORDER BY m.created_at ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
	':chat_id' => $chatId
]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * 3️⃣ Marcar como leído
 */
$sql = "
	UPDATE chat_participants
	SET last_read_at = NOW()
	WHERE chat_id = :chat_id
	AND user_id = :user_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
	':chat_id' => $chatId,
	':user_id' => $userId
]);

echo json_encode([
	'success' => true,
	'messages' => $messages
]);