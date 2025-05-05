<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Sale could not be created",
	"img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	$input = json_decode(file_get_contents('php://input'), true);
	if (!$input) throw new Exception("No data received.");

	$required = ["customer_id", "price_sum", "initial", "delivery_date", "remaining", "interest", "installments_month", "no_installments", "payment_date", "due"];
	foreach ($required as $field) {
		if (!isset($input[$field])) {
			throw new Exception("Missing required field: $field");
		}
	}

	$priceSum = number_format((float)$input["price_sum"], 2, '.', '');
	$initial = number_format((float)$input["initial"], 2, '.', '');
	$remaining = number_format((float)$input["remaining"], 2, '.', '');
	$interest = (int)$input["interest"];
	$installmentsMonth = (int)$input["installments_month"];
	$noInstallments = (int)$input["no_installments"];
	$due = number_format((float)$input["due"], 2, '.', '');
	$deliveryDate = date('Y-m-d H:i:s', strtotime($input["delivery_date"]));
	$paymentDate = date('Y-m-d H:i:s', strtotime($input["payment_date"]));

	$newOrdNo = get_next_increment_value("sales", "ord_no", 10000);

	$saleData = [
		"ord_no"				=> $newOrdNo,
		"customer_id"			=> (int)$input["customer_id"],
		"price_sum"				=> $priceSum,
		"initial"				=> $initial,
		"delivery_date"			=> $deliveryDate,
		"remaining"				=> $remaining,
		"interest"				=> $interest,
		"installments_month" 	=> $installmentsMonth,
		"no_installments" 		=> $noInstallments,
		"payment_date"			=> $paymentDate,
		"due"					=> $due,
		"status"				=> 1,
		"create_by"				=> $userId
	];

	$saleInsert = json_decode(insert_into("sales", $saleData, ["id" => "sales_id"]), true);
	if (!$saleInsert["success"]) {
		throw new Exception("Failed to create sale record.");
	}
	$saleId = $saleInsert["id"];

	foreach ($input["products"] as $product) {
		$purchased = [
			"sales_id"		=> $saleId,
			"customer_id"	=> (int)$input["customer_id"],
			"product_id"	=> (int)$product["product_id"],
			"quantity"		=> (int)($product["quantity"] ?? 1),
			"price"			=> number_format((float)($product["price"] ?? 0), 2, '.', ''),
			"discount"		=> number_format((float)($product["discount"] ?? 0), 2, '.', ''),
			"total"			=> number_format((float)($product["total"] ?? $price), 2, '.', ''),
			"create_by"		=> $userId
		];

		$productInsert = json_decode(insert_into("purchased_products", $purchased), true);
		if (!$productInsert["success"]) {
			throw new Exception("Error inserting product with ID {$purchased["product_id"]}");
		}

		// update_table("products", ["status" => 0], ["product_id" => $purchased["product_id"]]);
	}

	log_activity(
		$userId,
		"create_sale",
		"Created new sale #$saleId with customer_id {$input["customer_id"]}",
		"sales",
		$saleInsert["id"] ?? null
	);

	$response = [
        "success" => true,
        "message" => "Sale created successfully",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;