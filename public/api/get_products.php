<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No products found",
	"count"   => 0,
	"data"    => []
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		throw new Exception("Method not allowed.");
	}

	$authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;
	$companyId = $authUser["company_id"] ?? null;
	
	if (empty($userId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

	// Leer filtros desde la URL
	$search     = $_GET["search"]     ?? '';
	$mark       = $_GET["mark"]       ?? '';
	$model      = $_GET["model"]      ?? '';
	$submodel   = $_GET["submodel"]   ?? '';
	$company    = $_GET["company"]    ?? '';
	$productId  = $_GET["product_id"] ?? '';
	$purpose    = $_GET["purpose"]    ?? '';
	$barcode    = $_GET["barcode"]    ?? '';

	$companyFilter = null;

	if (is_numeric($company) && intval($company) > 0) {
		$companyFilter = intval($company);
	} elseif (is_numeric($companyId) && intval($companyId) > 0) {
		$companyFilter = intval($companyId);
	}

	if (empty($companyFilter)) {
		throw new Exception("No company selected or linked to this user.");
	}
	/*
	-------------------------------------------------------------------
	🔎 BÚSQUEDA POR CÓDIGO DE BARRAS (modo individual)
	-------------------------------------------------------------------
	*/
	if (!empty($barcode)) {

		$productQuery = select_from("products", ["*"], [
			"hs_code" => $barcode,   // CAMBIA A "barcode" si tu DB lo maneja así
			"company_id"=> $companyFilter
		], ["fetch_first" => true]);

		$parsed = json_decode($productQuery, true);

		if (!$parsed["success"] || empty($parsed["data"])) {
			echo json_encode([
				"success" => true,
				"message" => "Product not found.",
				"product" => null
			]);
			exit;
		}

		$product = mapProductRelations($parsed["data"], $companyFilter);

		if (!$product) {
			echo json_encode([
				"success" => true,
				"message" => "Product not found in this company.",
				"product" => null
			]);
			exit;
		}

		echo json_encode([
			"success" => true,
			"message" => $product ? "Product found." : "Product not found in this company.",
			"product" => $product
		]);
		exit;
	}

	/*
	-------------------------------------------------------------------
	📦 LISTADO NORMAL DE PRODUCTOS
	-------------------------------------------------------------------
	*/

	$where = [];

	if (!empty($mark))       $where["product_mark"]		 = $mark;
	if (!empty($model))      $where["product_model"]	 = $model;
	if (!empty($submodel))   $where["product_sub_model"] = $submodel;
	if (!empty($purpose))    $where["purpose"]			 = $purpose;

	if (!empty($productId) && is_numeric($productId)) {
		$where["product_id"] = (int)$productId;
	}

	$where["company_id"] = $companyFilter;
	

	if (!empty($search)) {
		$where["OR"] = [
			"product_name ILIKE" => "%{$search}%",
			"hs_code ILIKE"      => "%{$search}%"
		];
	}

	// Listado completo
	$productsQuery = select_from("products", ["*"], $where, [
		"order_by" => "created_at",
		"order_direction" => "DESC"
	]);
	
	$parsed = json_decode($productsQuery, true);
	
	if (!$parsed["success"]) {
		throw new Exception("Error loading products.");
	}

	$productsData = [];

	foreach ($parsed["data"] ?? [] as $product) {
		$enriched = mapProductRelations($product, $companyFilter);

		if ($enriched) {
			$productsData[] = $enriched;
		}

		if (!$enriched) {
			error_log("Product discarded. Product company_id: " . ($product["company_id"] ?? 'NULL') . " / companyFilter: " . $companyFilter);
		}
	}

	$response = [
		"success" => true,
		"message" => "Products loaded.",
		"count"   => count($productsData),
		"data"    => array_values($productsData)
	];
} catch (Exception $e) {
	$response = [
		"success" => false,
		"message" => $e->getMessage()
	];
}

echo json_encode($response);
exit;