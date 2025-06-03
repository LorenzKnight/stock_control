<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No sales found",
	"data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	$companyResult = json_decode(select_from("companies", [
		"company_id"
	], ["user_id" => $userId], ["fetch_first" => true]), true);

	if (!$companyResult["success"]) {
		throw new Exception("No matching company found.");
	}

    $companyInfo = $companyResult["data"];
    $companyId = $companyInfo["company_id"];

	$search = $_GET["search"] ?? '';
	
	$where = [
		"company_id" => $companyId
	];

	$filterBySearch = !empty($search);

	$salesResult = select_from("sales", [
		"sales_id", "ord_no", "customer_id", "price_sum", "initial", "delivery_date",
		"currency", "remaining", "interest", "installments_month", "no_installments",
		"payment_date", "due", "created_at"
	], $where, [
		"order_by" => "created_at",
		"order_direction" => "DESC"
	]);

	$parsedSales = json_decode($salesResult, true);
	if (!$parsedSales["success"] || empty($parsedSales["data"])) {
		throw new Exception("No sales available.");
	}

	$salesData = [];
	foreach ($parsedSales["data"] as $sale) {
		$customerInfo = select_from("customers", [
			"customer_name", "customer_surname", "customer_phone",
			"customer_document_type", "customer_document_no", "customer_image"
		], ["customer_id" => $sale["customer_id"]], ["fetch_first" => true]);

		$customer = json_decode($customerInfo, true)["data"] ?? [];
		$documentTypes = GlobalArrays::$documentTypes;

		$productsQuery = select_from("purchased_products", [
			"product_id", "quantity", "price", "discount", "total"
		], ["sales_id" => $sale["sales_id"]]);

		$productsList = json_decode($productsQuery, true)["data"] ?? [];
		$productsData = [];

		foreach ($productsList as $prod) {
			$productInfo = select_from("products", [
				"product_image", "product_name", "product_year",
				"product_mark", "product_model", "product_sub_model", "prise"
			], ["product_id" => $prod["product_id"]], ["fetch_first" => true]);

			$product = json_decode($productInfo, true)["data"] ?? [];

			$markName = $modelName = $submodelName = null;
			if (!empty($product['product_mark'])) {
				$mark = select_from("category", ["category_name"], ["category_id" => $product['product_mark']], ["fetch_first" => true]);
				$markName = json_decode($mark, true)["data"]["category_name"] ?? null;
			}
			if (!empty($product['product_model'])) {
				$model = select_from("category", ["category_name"], ["category_id" => $product['product_model']], ["fetch_first" => true]);
				$modelName = json_decode($model, true)["data"]["category_name"] ?? null;
			}
			if (!empty($product['product_sub_model'])) {
				$sub = select_from("category", ["category_name"], ["category_id" => $product['product_sub_model']], ["fetch_first" => true]);
				$submodelName = json_decode($sub, true)["data"]["category_name"] ?? null;
			}

			$productsData[] = [
				"product_id"        => $prod["product_id"] ?? '',
				"name"				=> $product["product_name"] ?? '',
				"year"				=> $product["product_year"] ?? '',
				"image"				=> $product["product_image"] ?? '',
				"mark_name"			=> $markName,
				"model_name"		=> $modelName,
				"submodel_name" 	=> $submodelName,
				"quantity"			=> $prod["quantity"] ?? 1,
				"prise"				=> $product["prise"] ?? 0,
				"discount"			=> $prod["discount"] ?? 0,
				"total"				=> $prod["total"] ?? 0
			];
		}

		$customerFullName = strtolower(trim(($customer["customer_name"] ?? '') . ' ' . ($customer["customer_surname"] ?? '')));
		$documentNo = strtolower($customer["customer_document_no"] ?? '');
		$searchLower = strtolower($search);

		$paymentQuery = select_from("payments", ["ord_no"], ["sales_id" => $sale["sales_id"]]);
		$paymentCountParsed = json_decode($paymentQuery, true);
		$paymentCount = 0;
		
		if ($paymentCountParsed && $paymentCountParsed["success"] && isset($paymentCountParsed["count"])) {
			$paymentCount = (int)$paymentCountParsed["count"];
		}

		if (
			!$filterBySearch ||
			strpos($customerFullName, $searchLower) !== false ||
			strpos($documentNo, $searchLower) !== false ||
			strpos(strtolower((string)$sale["ord_no"]), $searchLower) !== false
		) {
			$salesData[] = [
				"sales_id"				=> $sale["sales_id"],
				"ord_no"				=> $sale["ord_no"],
				"price_sum"				=> $sale["price_sum"],
				"initial"				=> $sale["initial"],
				"delivery_date"			=> date("Y-m-d", strtotime($sale["delivery_date"])),
				"remaining"				=> $sale["remaining"],
				"interest"				=> $sale["interest"],
				"total_interest"		=> ($sale["price_sum"] * $sale["interest"]) / 100,
				"installments_month"	=> $sale["installments_month"],
				"no_installments"		=> $sale["no_installments"],
				"payment_date"			=> date("Y-m-d", strtotime($sale["payment_date"])),
				"due"					=> $sale["due"],

				"customer" => [
					"customer_id"		=> $sale["customer_id"],
					"full_name"			=> trim(($customer["customer_name"] ?? '') . ' ' . ($customer["customer_surname"] ?? '')),
					"document_type"		=> $documentTypes[$customer["customer_document_type"]] ?? '',
					"document_no"		=> $customer["customer_document_no"] ?? '',
					"phone"				=> $customer["customer_phone"] ?? '',
					"image"				=> $customer["customer_image"] ?? ''
				],

				"products"				=> $productsData,

				"payments"				=> $paymentCount
			];
		}
	}

	$response["success"] = true;
	$response["data"] = $salesData;
	$response["message"] = "Sales loaded successfully.";
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;