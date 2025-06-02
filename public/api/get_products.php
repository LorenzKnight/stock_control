<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No products found",
	"data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	$userInfo = select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
	$companyId = json_decode($userInfo, true)["data"]["company_id"] ?? null;

	// Leer filtros desde la URL
	$search = $_GET["search"] ?? '';
	$mark = $_GET["mark"] ?? '';
	$model = $_GET["model"] ?? '';
	$submodel = $_GET["submodel"] ?? '';

	$where = [
		"company_id" => $companyId,
		// "status" => 1
	];

	if (!empty($mark)) {
		$where["product_mark"] = $mark;
	}
	if (!empty($model)) {
		$where["product_model"] = $model;
	}
	if (!empty($submodel)) {
		$where["product_sub_model"] = $submodel;
	}

	if (!empty($search)) {
		$where["OR"] = [
			"product_name ILIKE" => "%" . $search . "%"
		];
	}

	$products = select_from("products", [
		"product_id",
		"product_name",
		"product_image",
		"product_mark",
		"product_model",
		"product_sub_model",
		"description",
		"product_year",
		"product_image",
		"product_type",
		"currency",
		"prise",
		"quantity"
	], $where, [
		"order_by" => "created_at",
		"order_direction" => "DESC"
	]);

	$parsed = json_decode($products, true);
	if (!$parsed["success"] || empty($parsed["data"])) {
		throw new Exception("No products available.");
	}

	$productsData = $parsed["data"];

	foreach ($productsData as &$product) {
		// Nombre de la marca
		if (!empty($product['product_mark'])) {
			$markRes = select_from("category", ["category_name"], [
				"category_id" => $product['product_mark']
			], ["fetch_first" => true]);
			$product["mark_name"] = json_decode($markRes, true)["data"]["category_name"] ?? null;
		}

		// Nombre del modelo
		if (!empty($product['product_model'])) {
			$modelRes = select_from("category", ["category_name"], [
				"category_id" => $product['product_model']
			], ["fetch_first" => true]);
			$product["model_name"] = json_decode($modelRes, true)["data"]["category_name"] ?? null;
		}

		// Nombre del submodelo
		if (!empty($product['product_sub_model'])) {
			$subRes = select_from("category", ["category_name"], [
				"category_id" => $product['product_sub_model']
			], ["fetch_first" => true]);
			$product["submodel_name"] = json_decode($subRes, true)["data"]["category_name"] ?? null;
		}
	}

	$response["success"] = true;
	$response["data"] = $productsData;
	$response["message"] = "Products loaded.";
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>