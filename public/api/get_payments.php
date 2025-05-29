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
	$paymentId = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : null;

    $where = [];

    if (!empty($search)) {
        $where['OR'] = [
            'CAST(ord_no AS TEXT) ILIKE' => "%$search%",
            'CAST(payment_no AS TEXT) ILIKE' => "%$search%",
			'CAST(payment_date AS TEXT) ILIKE' => "%$search%"
        ];
    } elseif ($paymentId) {
		$where["payment_id"] = $paymentId;
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
		$customerId = $payment["customer_id"] ?? null;
		if (!$customerId) continue;

		$customerResult = json_decode(select_from("customers", [
			"customer_name",
			"customer_surname",
			"customer_document_type",
			"customer_document_no",
			"customer_status",
			"customer_image"
		], ["customer_id" => $customerId], ["fetch_first" => true]), true);

		if (!$customerId) {
			error_log("No customer_id in payment_id: {$payment['payment_id']}");
			continue;
		}

		if (!$customerResult["success"]) {
			error_log("Customer not found for ID: $customerId");
			continue;
		}

		$customer = $customerResult["data"];

		$payment["payment_id"] = (int)$payment["payment_id"];
		$payment["ord_no"] = $payment["ord_no"] ?? '';
		$payment["payment_no"] = $payment["payment_no"] ?? '';
		$payment["sales_id"] = $payment["sales_id"] ?? null;
		$payment["full_name"] = trim(($customer["customer_name"] ?? '') . ' ' . ($customer["customer_surname"] ?? ''));

		$docType = $customer["customer_document_type"] ?? null;
	    $payment["document_type"] = GlobalArrays::$documentTypes[$docType] ?? "Unknown";

		$payment["document_no"] = $customer["customer_document_no"] ?? '';
		$payment["currency"] = $payment["currency"] ?? '';

		$payMethod = $payment["payment_method"] ?? null;
		$payment["payment_method"] = GlobalArrays::$paymentMethods[$payMethod] ?? "Unknown";

		$payment["amount"] = number_format((float)$payment["amount"], 2, '.', '');
		$payment["interest"] = number_format((float)$payment["interest"], 2, '.', '');
		$payment["installments_month"] = number_format((float)$payment["installments_month"], 2, '.', '');
		$payment["no_installments"] = number_format((float)$payment["no_installments"], 2, '.', '');
		$payment["payment_date"] = date('Y-m-d', strtotime($payment["payment_date"]));
		$payment["due"] = number_format((float)$payment["due"], 2, '.', '');
		$payment["status"] = $payment["status"] ?? null;
		$payment["created_by"] = $payment["created_by"] ?? null;
		$payment["created_at"] = date('Y-m-d', strtotime($payment["created_at"]));
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