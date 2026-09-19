<?php
use App\Shippings\ShippingRepository;
use App\Shippings\ShippingService;
use App\Shippings\LoadRepository;
use App\Shippings\LoadService;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No shippings found",
	"data" => []
];

try {
	$authUser = requireAuth();
	$companyId = (int)($authUser["company_id"] ?? 0);

	// Fallback de company_id cuando no viene en el contexto autenticado
	if ($companyId <= 0) {
		$companyId = (int)($_GET["company"] ?? 0);
	}

	if ($companyId <= 0) {
		throw new Exception("Company ID is required or user not authenticated.");
	}

	$search = $_GET["search"] ?? '';
	$filterBySearch = !empty($search);
	$searchLower = strtolower($search);

	$status = (string)($_GET["status"] ?? '');

	$repository = new ShippingRepository();
	$service = new ShippingService($repository);

	$loadRepository = new LoadRepository();
	$loadService = new LoadService($loadRepository);

	$shippings = $service->getShippings(
		$companyId,
		$status
	);

	$dataList = [];

	foreach ($shippings as $shipping) {
		// Loads asociados al shipping
		$loads = $loadService->getLoadsForShipping(
			$companyId,
			(int)$shipping["shippings_id"]
		);

		$loadsData = [];

		$customerMatchesSearch = false;

		if ($filterBySearch) {
			foreach ($loads as $load) {
				$customerFullName = strtolower(trim((string)($load["customer"]["full_name"] ?? '')));

				if (
					$customerFullName !== '' &&
					strpos($customerFullName, $searchLower) !== false
				) {
					$customerMatchesSearch = true;
					break;
				}
			}
		}

		foreach ($loads as $load) {
			// Productos asociados al load
			$loadProducts = $loadService->getProductsForLoad(
				(int)$load["load_id"]
			);

			$productsData = $loadProducts["products"];
			$loadWeightTotal = (float)$loadProducts["total_weight"];

			$loadsData[] = [
				"load_id"              	=> $load["load_id"],
				"load_no"              	=> $load["load_no"],
				"from_currency"        	=> $load["from_currency"],
				"to_currency"          	=> $load["to_currency"],
				"price_per_kg"         	=> $load["price_per_kg"],
				"total_kg"             	=> $load["total_kg"],
				"price_sum"            	=> $load["price_sum"],
				"taxes"                	=> $load["taxes"],
				"discount"             	=> $load["discount"],
				"price_total"          	=> $load["price_total"],
				"price_total_exchanged"	=> $load["price_total_exchanged"],
				"destination"          	=> $load["destination"],
				"total_weight"         	=> $loadWeightTotal,
				"status"               	=> $load["status"],
				"created_at"           	=> $load["created_at"],
				"customer" 				=> $load["customer"],
				"products"				=> $productsData
			];
		}

		// Resumen de productos del shipping
		$shippingProductSummary = $service->buildProductSummary(
			$loadsData
		);

		// Tracking asociado al shipping
		$trackingData = $service->getShippingTracking(
			(int)$shipping["shippings_id"]
		);

		$allTracking = $trackingData["all_tracking"];
		$latestTracking = $trackingData["tracking"];

		$statusText = GlobalArrays::$shippingStatus[$shipping["status"]] ?? "Unknown";

		// Filtrar por search
		if (
			!$filterBySearch ||
			strpos(strtolower((string)$shipping["shipping_no"]), $searchLower) !== false ||
			$customerMatchesSearch ||
			strtolower((string)$shipping["shippings_id"]) === $searchLower
		) {
			$dataList[] = [
				"shippings_id"   	=> $shipping["shippings_id"],
				"company_id"    	=> $shipping["company_id"],
				"shipping_no"    	=> $shipping["shipping_no"],
				"destination"    	=> $shipping["destination"],
				"delivery_date"  	=> $shipping["delivery_date"] ? date("Y-m-d", strtotime($shipping["delivery_date"])) : null,
				"description"    	=> $shipping["description"],
				"status"         	=> $shipping["status"],
				"status_text"     	=> $statusText,
				"created_at"		=> $shipping["created_at"],
				"shipping_img"		=> $shipping["shipping_img"],
				"shipping_method"	=> $shipping["shipping_method"],
				"loads"				=> $loadsData,
				"loadsQty"			=> count($loadsData),
				"all_tracking"		=> $allTracking,
				"tracking"			=> $latestTracking,
				"product_summary"	=> $shippingProductSummary
			];
		}
	}

	$response = [
		"success"	=> true,
		"message"	=> "Shippings loaded successfully.",
		"data"		=> $dataList
	];
} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;