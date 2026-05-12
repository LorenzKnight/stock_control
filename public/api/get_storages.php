<?php
require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

function buildProductInfo(int $productId, int $companyId): ?array
{
	$productLookup = select_from("products", [
		"product_id", 'company_id', "product_image", "product_name", "product_year",
		"product_mark", "product_model", "product_sub_model",
		"price", "sale_unit_type", "units_per_pack", "weight_per_unit", "total_weight",
		"quantity", "min_quantity", "currency", "purpose"
	], [
		"company_id" => $companyId,
		"product_id" => $productId
	], [
		"fetch_first" => true
	]);

	$productLookupData = json_decode($productLookup, true)["data"] ?? null;

	if (empty($productLookupData) || !is_array($productLookupData)) {
		return null;
	}

	$productLookupData = mapProductRelations($productLookupData, $companyId);

	if (!$productLookupData) {
		return null;
	}

	$slots = [];
	$slotIds = [];
	$slotNames = [];

	if (!empty($productLookupData['product_id'])) {
		$storageRows = select_from(
			"storage",
			["slot_id"],
			[
				"company_id" => $companyId,
				"product_id" => $productLookupData['product_id']
			],
			[
				"order_by" => "created_at",
				"order_direction" => "DESC"
			]
		);

		$storageDecoded = json_decode($storageRows, true);
		$storageData = $storageDecoded["data"] ?? [];

		if (!empty($storageData) && is_array($storageData)) {
			$seenSlotIds = [];

			foreach ($storageData as $storageRow) {
				$currentSlotId = $storageRow["slot_id"] ?? null;
				if (!$currentSlotId || isset($seenSlotIds[(string)$currentSlotId])) {
					continue;
				}

				$slot = select_from(
					"slot",
					["slot_id", "slot_name"],
					[
						"company_id" => $companyId,
						"slot_id" => $currentSlotId
					],
					["fetch_first" => true]
				);

				$slotDecoded = json_decode($slot, true);
				$slotRow = $slotDecoded["data"] ?? null;

				if (!empty($slotRow) && is_array($slotRow)) {
					$slots[] = [
						"slot_id" => $slotRow["slot_id"] ?? null,
						"slot_name" => $slotRow["slot_name"] ?? null
					];

					$slotIds[] = $slotRow["slot_id"] ?? null;
					$slotNames[] = $slotRow["slot_name"] ?? null;
					$seenSlotIds[(string)$currentSlotId] = true;
				}
			}
		}
	}

	return [
		"product_id"        => $productLookupData["product_id"] ?? null,
		"product_name"      => $productLookupData["product_name"] ?? '',
		"product_year"      => $productLookupData["product_year"] ?? '',
		"product_image"     => $productLookupData["product_image"] ?? '',
		"product_mark"      => $productLookupData["product_mark"] ?? null,
		"product_model"     => $productLookupData["product_model"] ?? null,
		"product_sub_model" => $productLookupData["product_sub_model"] ?? null,
		"mark_name"         => $productLookupData["mark_name"] ?? tr("uncategorized", "Uncategorized"),
		"model_name"        => $productLookupData["model_name"] ?? tr("no_model", "No model assigned"),
		"submodel_name"     => $productLookupData["submodel_name"] ?? tr("no_submodel", "No submodel assigned"),
		"purpose"           => $productLookupData["purpose"] ?? '',
		"purpose_text"      => $productLookupData["purpose_text"] ?? tr("no_purpose", "No purpose assigned"),
		"price"             => $productLookupData["price"] ?? 0,
		"sale_unit_type"    => $productLookupData["sale_unit_type"] ?? '',
		"units_per_pack"    => $productLookupData["units_per_pack"] ?? 0,
		"weight_per_unit"   => (float)($productLookupData["weight_per_unit"] ?? 0),
		"total_weight"      => (float)($productLookupData["total_weight"] ?? 0),
		"quantity"          => $productLookupData["quantity"] ?? 0,
		"min_quantity"      => $productLookupData["min_quantity"] ?? 0,
		"currency"          => $productLookupData["currency"] ?? '',
		"slot_id"			=> count($slotIds) === 1 ? $slotIds[0] : null,
		"slot_name"			=> count($slotNames) === 1 ? $slotNames[0] : null,
		"slot_ids"			=> $slotIds,
		"slot_names"		=> $slotNames,
		"slots"             => $slots
	];
}

function appendStoragesByWhere(array $where, int $companyId, array &$allStorages, array &$seenStorageIds, array &$productMap): void
{
	$storageResult = select_from("storage", [
		"storage_id", "company_id",
		"slot_id", "product_id", "quantity",
		"created_by", "created_at"
	], $where, [
		"order_by" => "created_at",
		"order_direction" => "DESC"
	]);

	$parsedStorages = json_decode($storageResult, true);

	if (empty($parsedStorages["success"]) || empty($parsedStorages["data"]) || !is_array($parsedStorages["data"])) {
		return;
	}

	foreach ($parsedStorages["data"] as $storageRow) {
		$storageId = $storageRow["storage_id"] ?? null;
		if (!$storageId || isset($seenStorageIds[$storageId])) {
			continue;
		}

		$productIdKey = (string)($storageRow["product_id"] ?? '');
		$productInfo = $productMap[$productIdKey] ?? null;

		if (!$productInfo && !empty($storageRow["product_id"])) {
			$productInfo = buildProductInfo($storageRow["product_id"], $companyId);

			if ($productInfo) {
				$productMap[$productIdKey] = $productInfo;
			}
		}

		$allStorages[] = [
			"storage_id"  => $storageRow["storage_id"] ?? null,
			"company_id"  => $storageRow["company_id"] ?? null,
			"slot_id"     => $storageRow["slot_id"] ?? null,
			"product_id"  => $storageRow["product_id"] ?? null,
			"quantity"    => $storageRow["quantity"] ?? null,
			"created_by"   => $storageRow["created_by"] ?? null,
			"created_at"  => $storageRow["created_at"] ?? null,
			"product"     => $productInfo
		];

		$seenStorageIds[$storageId] = true;
	}
}

$response = [
	"success" => false,
	"message" => "No storages found",
	"data" => []
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		throw new Exception("Method not allowed.");
	}

	$authUser = requireAuth();
	$companyId = $authUser["company_id"] ?? null;

	if (empty($companyId)) {
		throw new Exception("Unauthorized access: company not found.");
	}

    $search = $_GET["search"] ?? '';
	$search = trim($search);
	$slotIdFilter = isset($_GET["slot_id"]) ? (int)$_GET["slot_id"] : 0;

	$filterBySearch = ($search !== '');
	$filterBySlotId = ($slotIdFilter > 0);
	
	$slotData = [];
	$productData = [];
	$allStorages = [];
	$dataList = [];
	$seenStorageIds = [];
	$productMap = [];

	if ($filterBySearch || $filterBySlotId) {
		$slotWhere = [
			"company_id" => $companyId
		];

		if ($filterBySlotId) {
			$slotWhere["slot_id"] = $slotIdFilter;
		} else {
			$slotWhere["OR"] = [
				"slot_name ILIKE" => "%{$search}%"
			];
		}

        $slotResult = select_from("slot", [
            "slot_id", "company_id", "slot_name", 
            "slot_description", "max_capacity", "current_capacity", 
            "status", "created_by", "created_at"
        ], $slotWhere, [
            "order_by" => "created_at",
            "order_direction" => "DESC"
        ]);

        $parsedSlots = json_decode($slotResult, true);
		$slotData = $parsedSlots["data"] ?? [];

		if ($filterBySearch) {
			$productWhere = [
				"company_id" => $companyId,
				"OR" => [
					"product_name ILIKE" => "%{$search}%"
				]
			];

			$productResult = select_from("products", [
				"product_id", "product_image", "product_name", "product_year",
				"product_mark", "product_model", "product_sub_model",
				"price", "sale_unit_type", "weight_per_unit", "total_weight",
				"quantity", "min_quantity", "currency", "purpose"
			], $productWhere, [
				"order_by" => "created_at",
				"order_direction" => "DESC"
			]);

			$parsedProducts = json_decode($productResult, true);
			$rawProducts = $parsedProducts["data"] ?? [];

			if (!empty($rawProducts) && is_array($rawProducts)) {
				foreach ($rawProducts as $productRow) {
					$productId = $productRow["product_id"] ?? null;
					if (!$productId) continue;

					$productInfo = buildProductInfo($productId, $companyId);
					if ($productInfo) {
						$productData[] = $productInfo;
					}
				}
			}
		}

		foreach ($productData as $prod) {
			if (!empty($prod["product_id"])) {
				$productMap[(string)$prod["product_id"]] = $prod;
			}
		}

		if (!empty($slotData) && is_array($slotData)) {
			$slotIds = array_column($slotData, "slot_id");

			foreach ($slotIds as $slotId) {
				appendStoragesByWhere(
					[
						"company_id" => $companyId,
						"slot_id" => $slotId
					],
					$companyId,
					$allStorages,
					$seenStorageIds,
					$productMap
				);
			}
		}

		if ($filterBySlotId && !empty($allStorages)) {
			foreach ($allStorages as $storageRow) {
				$product = $storageRow["product"] ?? null;

				if (!empty($product["product_id"])) {
					$productMap[(string)$product["product_id"]] = $product;
				}
			}

			$productData = array_values($productMap);
		}

		if ($filterBySearch && !empty($productData) && is_array($productData)) {
			$productIds = array_column($productData, "product_id");

			foreach ($productIds as $productId) {
				appendStoragesByWhere(
					[
						"company_id" => $companyId,
						"product_id" => $productId
					],
					$companyId,
					$allStorages,
					$seenStorageIds,
					$productMap
				);
			}
		}
	}

	if (empty($allStorages) && empty($slotData) && empty($productData)) {
		throw new Exception("No data available.");
	}

	$dataList = [
		"slots"		=> $slotData,
		"products"	=> $productData,
		"storages"	=> $allStorages
	];

    $response = [
		"success"	=> true,
		"message"	=> "Storages loaded successfully.",
		"data"		=> $dataList
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;