<?php
require_once('../logic/stock_be.php');

header('Content-Type: application/json');

$response = [
	"success" => false,
	"message" => "Order not found",
	"data" => null
];

try {
	if (!isset($_GET["ord_no"]) || !is_numeric($_GET["ord_no"])) {
		throw new Exception("Invalid order number.");
	}

	$ordNo = (int)$_GET["ord_no"];

	// Supón que tienes una tabla `sales` o similar
	$saleResult = json_decode(select_from("sales", [
        "sales_id", "ord_no", "customer_id", "interest", "currency"
	], ["ord_no" => $ordNo], ["fetch_first" => true]), true);

	if (!$saleResult["success"]) {
		throw new Exception("No matching order found.");
	}

    $sale = $saleResult["data"];
    $customerId = $sale["customer_id"];

    $customerResult = json_decode(select_from("customers", [
        "customer_name", "customer_surname", "customer_phone",
        "customer_document_type", "customer_document_no", "customer_image",
        "customer_email"
    ], ["customer_id" =>  $customerId], ["fetch_first" => true]), true);

	$customer = $customerResult["data"] ?? [];

    $order = [
        "ord_no"            => $sale["ord_no"],
		"currency"          => $sale["currency"],
        "interest"          => $sale["interest"],
        "customer_id"       => $customerId,
        "customer_name"     => trim(($customer["customer_name"] ?? '') . ' ' . ($customer["customer_surname"] ?? '')),
        "document_type"     => $customer["customer_document_type"] ?? '',
		"document_no"       => $customer["customer_document_no"] ?? '',
		"phone"             => $customer["customer_phone"] ?? '',
		"email"             => $customer["customer_email"] ?? ''
    ];

	$response = [
		"success" => true,
		"message" => "Order found",
		"data" => $order
	];

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;