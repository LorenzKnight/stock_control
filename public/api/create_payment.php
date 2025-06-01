<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Payment creation failed",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $requiredFields = ["ord_no", "amount", "customer_email"];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $ordNo = (int)$_POST['ord_no'];
    $personWhoPaid = $_POST['person_who_paid'] ?? null;
    $documentType = $_POST['payer_document_type'] ?? null;
    $documentNo = $_POST['payer_document_no'] ?? null;
    $phone = $_POST['payer_phone'] ?? null;
    $email = $_POST['customer_email'] ?? null;
    $paymentMethod = $_POST['payment_method'] ?? null;
    $amount = (float)$_POST['amount'];
    $interest = isset($_POST['interest']) ? (float)$_POST['interest'] : 0;
    $status = isset($_POST['payment_status']) ? (int)$_POST['payment_status'] : 0;
    $userId = $_SESSION['sc_UserId'] ?? null;

    if (!$userId) throw new Exception("User not authenticated.");

    // Buscar datos de la orden para obtener customer_id y sales_id
    $saleRes = json_decode(select_from("sales", [
        "sales_id", 
        "customer_id", 
        "price_sum", 
        "interest", 
        "installments_month", 
        "no_installments", 
        "due", 
        "currency"
    ], ["ord_no" => $ordNo], ["fetch_first" => true]), true);
    if (!$saleRes['success']) throw new Exception("Order not found."); 

    $sale = $saleRes['data'];

    $newPaymentNo = get_next_increment_value("payments", "payment_no", 20000);

    $month = get_next_increment_value("payments", "no_installments", 1);
    $noInstallments = $month < $sale["installments_month"] ? $month : 0;
    if ($noInstallments && $noInstallments < 1) {
        throw new Exception("All payments were made.");
    }

	$saleDue = (float)($sale['due'] ?? 0);
	$paymentNet = (float)($amount ?? 0) - (float)($interest ?? 0);

	if ($saleDue < $paymentNet) {
        throw new Exception(
			"The debt (" . number_format($saleDue, 2) . " " . ($sale['currency'] ?? '') . 
			") is less than the amount being paid (" . number_format($paymentNet, 2) . ")."
		);
    }

	$due = $saleDue - $paymentNet;

    $paymentData = [
        "ord_no" => $ordNo,
        "payment_no" => $newPaymentNo,
        "sales_id" => $sale["sales_id"],
        "customer_id" => $sale["customer_id"],
        "person_who_paid" => $personWhoPaid,
        "payer_document_type" => $documentType,
        "payer_document_no" => $documentNo,
        "payer_phone" => $phone,
        "customer_email" => $email,
        "currency" => $_POST['currency'] ?? $sale["currency"] ?? null,
        "payment_method" => $paymentMethod,
        "amount" => $paymentNet,
        "interest" => $interest,
        "installments_month" => $sale["installments_month"] ?? null,
        "no_installments" => $noInstallments,
        "payment_date" => date("Y-m-d H:i:s"),
        "due" => $due,
        "status" => $status,
        "created_by" => $userId
    ];
    
    $insert = json_decode(insert_into("payments", $paymentData, ["id" => "payment_id"]), true);
    if (!$insert['success']) {
        throw new Exception("Failed to insert payment record.");
    }

	$interestData = [
		"sales_id" => $sale["sales_id"],
		"payment_id" => $insert["id"] ?? null,
		"customer_id" => $sale["customer_id"],
		"payment_no" => $newPaymentNo,
		"ord_no" => $ordNo,
		"interest" => $interest,
		"installments_month" => $sale["installments_month"] ?? null,
		"no_installments" => $noInstallments,
		"payment_date" => date("Y-m-d H:i:s"),
		"initial_debt" => $sale["price_sum"] ?? 0,
		"created_by" => $userId,
		"created_at" => date("Y-m-d H:i:s")
	];

	$insertInterest = json_decode(insert_into("interest_earnings", $interestData), true);
	if (!$insertInterest["success"]) {
		throw new Exception("Failed to insert interest record.");
	}

	if ($insert["success"] && $insertInterest["success"]) {
        $updateResult = json_decode(update_table("sales", ["due" => $due], ["sales_id" => $sale["sales_id"]]), true);
		if (!$updateResult["success"]) throw new Exception("Failed to update sales record.");

        $action = "updated";
    }

    log_activity(
        $userId,
        "create_payment",
        "Payment created for order $ordNo",
        "payments",
        $insert["id"] ?? null
    );

    $response = [
        "success" => true,
        "message" => "Payment created successfully",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => "payments.php"
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;