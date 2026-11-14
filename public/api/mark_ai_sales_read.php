<?php
declare(strict_types=1);

require_once '../logic/stock_be.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new RuntimeException('Method not allowed.');
    }

    $authUser = requireAuth();
    $userId = (int)($authUser['user_id'] ?? 0);

    if (
        $userId <= 0 ||
        (
            !check_user_permission($userId, 'root_access') &&
            !check_user_permission($userId, 'system_admin')
        )
    ) {
        http_response_code(403);
        throw new RuntimeException('Permission denied.');
    }

    $conversationId = (int)(
        $_POST['conversation_id'] ?? 0
    );

    if ($conversationId <= 0) {
        http_response_code(400);
        throw new InvalidArgumentException(
            'Invalid conversation ID.'
        );
    }

    global $sql;

    $result = pg_query_params(
        $sql,
        '
        INSERT INTO sales_message_reads (
            sales_message_id,
            user_id
        )
        SELECT
            sm.sales_message_id,
            $1
        FROM sales_messages sm
        WHERE sm.conversation_id = $2
          AND sm.direction = \'INBOUND\'
          AND sm.status = \'RECEIVED\'
        ON CONFLICT (sales_message_id, user_id)
        DO NOTHING
        ',
        [$userId, $conversationId]
    );

    if ($result === false) {
        throw new RuntimeException(
            'Could not update AI Sales read status.'
        );
    }

    echo json_encode([
        'success' => true,
        'conversation_id' => $conversationId,
        'marked_read' => pg_affected_rows($result)
    ]);

} catch (Throwable $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
