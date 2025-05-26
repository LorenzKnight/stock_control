<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "No orders found",
    "data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found");

	$search = $_GET["search"] ?? '';
	if (empty($search)) throw new Exception("No search term provided");

	// Buscar órdenes con ord_no similar
	$result = select_from("sales", ["ord_no", "customer_id"], [
		"CAST(ord_no AS TEXT) ILIKE" => "%$search%"
	], [
		"limit" => 10,
		"order_by" => "ord_no",
		"order_direction" => "desc"
	]);

	$parsed = json_decode($result, true);
	if (!$parsed["success"] || empty($parsed["data"])) {
		throw new Exception("No matching orders");
	}

	$orders = $parsed["data"];
	$finalOrders = [];

	foreach ($orders as $order) {
		$customerId = $order["customer_id"];

		// Buscar datos del cliente
		$customerRes = select_from("customers", [
			"customer_name",
			"customer_surname"
		], ["customer_id" => $customerId], ["fetch_first" => true]);

		$customer = json_decode($customerRes, true)["data"] ?? [];

		$fullName = trim(($customer["customer_name"] ?? '') . ' ' . ($customer["customer_surname"] ?? ''));

		$finalOrders[] = [
			"ord_no" => $order["ord_no"],
			"customer_id" => $customerId,
			"full_name" => $fullName
		];
	}

	$response = [
        "success" => true,
        "data" => $finalOrders,
        "message" => "Orders found"
    ];

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;