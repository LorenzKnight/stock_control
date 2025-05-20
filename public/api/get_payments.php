<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Could not fetch payments",
    "data" => []
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

    $search = $_GET['search'] ?? '';
    
    $where = [];

    if (!empty($search)) {
        $where['OR'] = [
            'CAST(ord_no AS TEXT) ILIKE' => "%$search%",
            'CAST(payment_no AS TEXT) ILIKE' => "%$search%"
        ];
    }

    $paymentsResult = select_from('payments', [
        'payment_id', 
        'ord_no',
        'payment_no',
        'sales_id',
        'customer_id',
        'currency',
        'payment_method',
        'amount',
        'interest',
        'installments_month',
        'no_installments',
        'payment_date',
        'due',
        'status',
        'created_by',
        'created_at'
    ], $where, [
        'order_by' => 'created_at',
        'order_direction' => 'desc'
    ]);

    $parsedPayments = json_decode($paymentsResult, true);
	if (!$parsedPayments["success"] || empty($parsedPayments["data"])) {
		throw new Exception("No products available.");
	}

    foreach ($parsedPayments["data"] as &$payment) {
        $payment["ord_no"] = $payment["ord_no"] ?? '';
        $payment["payment_no"] = $payment["payment_no"] ?? '';
        $payment["sales_id"] = $payment["sales_id"] ?? null;
        $payment["customer_id"] = $payment["customer_id"] ?? null;
        $payment["currency"] = $payment["currency"] ?? '';
        $payment["payment_method"] = $payment["payment_method"] ?? '';
        $payment["amount"] = number_format((float)$payment["amount"], 2, '.', '');
        $payment["interest"] = number_format((float)$payment["interest"], 2, '.', '');
        $payment["installments_month"] = number_format((float)$payment["installments_month"], 2, '.', '');
        $payment["no_installments"] = number_format((float)$payment["no_installments"], 2, '.', '');
        $payment["payment_date"] = date('Y-m-d H:i:s', strtotime($payment["payment_date"]));
        $payment["due"] = number_format((float)$payment["due"], 2, '.', '');
        $payment["status"] = $payment["status"] ?? null;
        $payment["created_by"] = $payment["created_by"] ?? null;
        $payment["created_at"] = date('Y-m-d H:i:s', strtotime($payment["created_at"]));
    }

    $response = [
        "success" => true,
        "message" => "Payments fetched successfully",
        "data" => $parsedPayments["data"]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;