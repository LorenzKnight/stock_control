<?php
use App\Products\ProductRepository;
use App\Products\ProductService;

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
		$companyFilter = (int)$company;
	} elseif (is_numeric($companyId) && intval($companyId) > 0) {
		$companyFilter = (int)$companyId;
	}

	if (empty($companyFilter)) {
		throw new Exception("No company selected or linked to this user.");
	}

	$repository = new ProductRepository();
	$service = new ProductService($repository);

	/*
	-------------------------------------------------------------------
	🔎 BÚSQUEDA POR CÓDIGO DE BARRAS (modo individual)
	-------------------------------------------------------------------
	*/
	if (!empty($barcode)) {
		$result =
			$service->getProductByBarcode(
				$companyFilter,
				(string)$barcode
			);

		if (!$result["found"]) {
			echo json_encode([
				"success" => true,
				"message" =>
					"Product not found.",
				"product" => null
			]);

			exit;
		}

		if ($result["product"] === null) {
			echo json_encode([
				"success" => true,
				"message" => "Product not found in this company.",
				"product" => null
			]);
			exit;
		}

		echo json_encode([
			"success" => true,
			"message" => "Product found.",
			"product" => $result["product"]
		]);

		exit;
	}

	$products =
		$service->getProducts(
			$companyFilter,
			[
				"search" => $search,
				"mark" => $mark,
				"model" => $model,
				"submodel" => $submodel,
				"product_id" => $productId,
				"purpose" => $purpose
			]
		);

	$response = [
		"success" => true,
		"message" => "Products loaded.",
		"count"   => count($products),
		"data"    => $products
	];
} catch (Exception $e) {
	$response = [
		"success" => false,
		"message" => $e->getMessage()
	];
}

echo json_encode($response);
exit;