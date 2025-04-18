<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No customers found",
	"data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	$search = $_GET["search"] ?? '';

    $where = [];

	if (!empty($search)) {
		$where["OR"] = [
			"customer_name ILIKE" => "%{$search}%",
			"customer_surname ILIKE" => "%{$search}%",
			"customer_document_no ILIKE" => "%{$search}%"
		];
	}

	$customers = select_from("customers", [
		"customer_id",
		"customer_name",
		"customer_surname",
        "customer_image",
        "customer_document_type",
		"customer_document_no",
		"customer_address",
		"customer_status"
	], $where, [
		"order_by" => "created_at",
		"order_direction" => "DESC"
	]);

	$parsed = json_decode($customers, true);
	if (!$parsed["success"] || empty($parsed["data"])) {
		throw new Exception("No customers available.");
	}

	foreach ($parsed["data"] as &$customer) {
		$customer["full_name"] = trim($customer["customer_name"] . ' ' . $customer["customer_surname"]);
		$customer["document_no"] = $customer["customer_document_no"];
		$customer["address"] = $customer["customer_address"];
		$customer["status"] = ($customer["customer_status"] == 1) ? "Active" : "Inactive";
		$customer["image"] = $customer["customer_image"] ?? "";

        $docType = $customer["customer_document_type"] ?? null;
	    $customer["document_type"] = GlobalArrays::$documentTypes[$docType] ?? "Unknown";
	}

	$response["success"] = true;
	$response["data"] = $parsed["data"];
	$response["message"] = "Customers loaded.";

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>