<?php
declare(strict_types=1);

require_once '../logic/stock_be.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
	
	$repository = new \App\AiSales\SalesMessageRepository();

	$messages = $repository->findUnreadByUser($userId);

	$companies = [];
	$conversations = [];
	$total = 0;

	foreach ($messages as $message) {
		$companyId = (int)$message["sales_company_id"];
		$conversationId = (int)$message["conversation_id"];

		$companies[$companyId] =
			($companies[$companyId] ?? 0) + 1;

		$conversations[$conversationId] =
			($conversations[$conversationId] ?? 0) + 1;

		$total++;
	}

    echo json_encode([
		"success" => true,
		"total_unread" => $total,
		"has_unread" => $total > 0,
		"companies" => $companies,
		"conversations" => $conversations
	]);

} catch (Throwable $e) {
    $status = http_response_code();

    if (!in_array($status, [400, 401, 403, 405], true)) {
        http_response_code(500);
    }

    echo json_encode([
        'success' => false,
        'message' => $status >= 500 || $status === false
			? 'Internal server error.'
			: $e->getMessage()
    ]);
}
