<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No shippings found",
	"data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	// obtener company_id del usuario
	$userInfo = select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
	$companyId = json_decode($userInfo, true)["data"]["company_id"] ?? null;

	$search = $_GET["search"] ?? '';
	$filterBySearch = !empty($search);
	$searchLower = strtolower($search);

	// 1️⃣ Traer shippings
	$shippingsResult = select_from("shippings", [
		"shippings_id", "shipping_no", "company_id",
		"shipping_img", "shipping_method", "destination", "delivery_date",
		"description", "status", "created_at"
	], ["company_id" => $companyId], [
		"order_by" => "created_at",
		"order_direction" => "DESC"
	]);

	$parsedShippings = json_decode($shippingsResult, true);
	if (!$parsedShippings["success"] || empty($parsedShippings["data"])) {
		throw new Exception("No shippings available.");
	}

	$dataList = [];

	foreach ($parsedShippings["data"] as $shipping) {

		// 2️⃣ Buscar loads asociados a este shipping
		$loadsQuery = select_from("loads", [
			"load_id", "load_no", "customer_id", "currency", "price_sum", "status", "created_at"
		], [
			"company_id"   => $companyId,
			"shippings_id" => $shipping["shippings_id"]
		]);

		$parsedLoads = json_decode($loadsQuery, true)["data"] ?? [];
		$loadsData = [];

		foreach ($parsedLoads as $load) {
			// cliente de cada load
			$loadCustomerInfo = select_from("customers", [
				"customer_name", "customer_surname", "customer_phone", "customer_image"
			], ["customer_id" => $load["customer_id"]], ["fetch_first" => true]);
			$loadCustomer = json_decode($loadCustomerInfo, true)["data"] ?? [];

			if (!$firstCustomer) {
				$firstCustomer = $loadCustomer;
				$customerFullName = strtolower(trim(($loadCustomer["customer_name"] ?? '') . ' ' . ($loadCustomer["customer_surname"] ?? '')));
			}

			// 3️⃣ Productos dentro de cada load
			$loadedProductsQuery = select_from("loaded_products", [
				"product_id","quantity","price","discount","total"
			], ["load_id" => $load["load_id"]]);

			$parsedProducts = json_decode($loadedProductsQuery, true)["data"] ?? [];
			$productsData = [];
			$loadWeightTotal = 0;

			foreach ($parsedProducts as $prod) {
				// info del producto
				$productInfo = select_from("products", [
					"product_image", "product_name", "product_year", "product_mark", "product_model", "product_sub_model", "price", "weight_per_unit"
				], ["product_id" => $prod["product_id"]], ["fetch_first" => true]);
				$product = json_decode($productInfo, true)["data"] ?? [];

				$weightPerUnit = (float)($product["weight_per_unit"] ?? 0);
				$qty = (int)($prod["quantity"] ?? 0);
				$weightTotal = $weightPerUnit * $qty;

				$loadWeightTotal += $weightTotal;

				// nombres de marca, modelo, submodelo
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
					"product_id"   		=> $prod["product_id"] ?? '',
					"name"         		=> $product["product_name"] ?? '',
					"year"         		=> $product["product_year"] ?? '',
					"image"        		=> $product["product_image"] ?? '',
					"mark_name"    		=> $markName,
					"model_name"   		=> $modelName,
					"submodel_name"		=> $submodelName,
					"quantity"			=> $qty,
					"price"        		=> $product["price"] ?? 0,
					"discount"     		=> $prod["discount"] ?? 0,
					"total"        		=> $prod["total"] ?? 0,
					"weight_per_unit"	=> $weightPerUnit,
					"total_weight"		=> $weightTotal
				];
			}

			$loadsData[] = [
				"load_id"      => $load["load_id"],
				"load_no"      => $load["load_no"],
				"currency"     => $load["currency"],
				"price_sum"    => $load["price_sum"],
				"total_weight" => $loadWeightTotal,
				"status"       => $load["status"],
				"created_at"   => $load["created_at"],
				"customer" => [
					"customer_id" => $load["customer_id"],
					"full_name"   => trim(($loadCustomer["customer_name"] ?? '') . ' ' . ($loadCustomer["customer_surname"] ?? '')),
					"phone"       => $loadCustomer["customer_phone"] ?? '',
					"image"       => $loadCustomer["customer_image"] ?? ''
				],
				"products" => $productsData
			];
		}

		// 🧮 2️⃣ RESUMEN DE PRODUCTOS EN ESTE SHIPPING
		$shippingProductSummary = []; // key: product_id

		foreach ($loadsData as $loadEntry) {
			foreach ($loadEntry["products"] as $p) {
				$key = $p["product_id"];
				if (!isset($shippingProductSummary[$key])) {
					$shippingProductSummary[$key] = [
						"product_id"     => $p["product_id"],
						"name"           => $p["name"],
						"mark_name"      => $p["mark_name"],
						"model_name"     => $p["model_name"],
						"submodel_name"  => $p["submodel_name"],
						"image"          => $p["image"],
						"quantity"       => 0,
						"total_price"    => 0,
						"total_weight"   => 0
					];
				}

				$shippingProductSummary[$key]["quantity"]     += $p["quantity"];
				$shippingProductSummary[$key]["total_price"]  += $p["total"];
				$shippingProductSummary[$key]["total_weight"] += $p["total_weight"];
			}
		}

		// Filtrar por search
		if (
			!$filterBySearch ||
			strpos(strtolower((string)$shipping["shipping_no"]), $searchLower) !== false ||
			strpos($customerFullName, $searchLower) !== false
		) {
			$dataList[] = [
				"shippings_id"   	=> $shipping["shippings_id"],
				"company_id"    	=> $shipping["company_id"],
				"shipping_no"    	=> $shipping["shipping_no"],
				"destination"    	=> $shipping["destination"],
				"delivery_date"  	=> $shipping["delivery_date"] ? date("Y-m-d", strtotime($shipping["delivery_date"])) : null,
				"description"    	=> $shipping["description"],
				"status"         	=> $shipping["status"],
				"created_at"		=> $shipping["created_at"],
				"shipping_img"		=> $shipping["shipping_img"],
				"shipping_method"	=> $shipping["shipping_method"],
				"customer" => [
					"full_name"    	=> trim(($customer["customer_name"] ?? '') . ' ' . ($customer["customer_surname"] ?? '')),
					"document_no"  	=> $customer["customer_document_no"] ?? '',
					"phone"        	=> $customer["customer_phone"] ?? '',
					"image"			=> $customer["customer_image"] ?? ''
				],
				"loads"				=> $loadsData,
				"product_summary"	=> array_values($shippingProductSummary)
			];
		}
	}

	$response["success"] = true;
	$response["data"] = $dataList;
	$response["message"] = "Shippings loaded successfully.";

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;