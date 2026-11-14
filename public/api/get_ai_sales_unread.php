<?php
declare(strict_types=1);

require_once '../logic/stock_be.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

    global $sql;

    $query = '
        SELECT
            sl.sales_company_id,
            COUNT(*)::int AS unread_count
        FROM sales_messages sm
        INNER JOIN sales_conversations sc
            ON sc.conversation_id = sm.conversation_id
        INNER JOIN sales_leads sl
            ON sl.sales_lead_id = sc.sales_lead_id
        LEFT JOIN sales_message_reads smr
            ON smr.sales_message_id = sm.sales_message_id
            AND smr.user_id = $1
        WHERE sm.direction = \'INBOUND\'
          AND sm.status = \'RECEIVED\'
          AND smr.sales_message_id IS NULL
        GROUP BY sl.sales_company_id
    ';

    $result = pg_query_params($sql, $query, [$userId]);

    if ($result === false) {
        throw new RuntimeException(
            'Could not retrieve unread AI Sales messages.'
        );
    }

    $companies = [];
    $total = 0;

    while ($row = pg_fetch_assoc($result)) {
        $companyId = (int)$row['sales_company_id'];
        $count = (int)$row['unread_count'];

        $companies[$companyId] = $count;
        $total += $count;
    }

    echo json_encode([
        'success' => true,
        'total_unread' => $total,
        'has_unread' => $total > 0,
        'companies' => $companies
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
