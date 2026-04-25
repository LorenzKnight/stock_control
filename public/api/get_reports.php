<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Could not fetch reports",
    "data" => [],
	"summary" => [
		"total_sold_amount" => 0,
		"total_quantity_sold" => 0,
        "products_found" => 0,
        "average_sold_amount_per_product" => 0,
        "from_date" => null,
        "to_date" => null
	]
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		throw new Exception("Method not allowed.");
	}
	
	$authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;
    
	if (empty($userId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

    $search				= $_GET['search'] ?? '';
    $fromDate			= $_GET['reports_from_date'] ?? '';
    $toDate				= $_GET['reports_to_date'] ?? '';
	$company 			= $_GET['reports_select_company'] ?? '';
    $productMark		= $_GET['reports_product_mark'] ?? '';
    $productModel 		= $_GET['reports_product_model'] ?? '';
    $productSubModel	= $_GET['reports_product_sub_model'] ?? '';
    
	$productId = isset($_GET['product_id']) && is_numeric($_GET['product_id'])
        ? (int)$_GET['product_id']
        : null;

	$parsedUserInfo = json_decode(select_from(
        "users",
        ["company_id"],
        ["user_id" => $userId],
        ["fetch_first" => true]
    ), true);

	$userInfo = $parsedUserInfo["data"];

    if (
        !is_array($parsedUserInfo) ||
        empty($parsedUserInfo["success"]) ||
        empty($userInfo["company_id"])
    ) {
        throw new Exception("Company not found for this user.");
    }

    $companyId = (int)$userInfo["company_id"];

	$companyFilter = (!empty($company) && is_numeric($company))
		? (int)$company
		: $companyId;

	$where = [];
	
    $where = [
		"company_id" => $companyFilter,
	];

	if (!empty($productMark)) {
		$where["product_mark"] = $productMark;
	}

	if (!empty($productModel)) {
		$where["product_model"] = $productModel;
	}

	if (!empty($productSubModel)) {
		$where["product_sub_model"] = $productSubModel;
	}

    if (!empty($productId)) {
        $where["product_id"] = $productId;
    }

    if (!empty($search)) {
        $where['OR'] = [
            'CAST(product_name AS TEXT) ILIKE' => "%$search%",
            'CAST(hs_code AS TEXT) ILIKE' => "%$search%"
        ];
    }

	if (!empty($fromDate) || !empty($toDate)) {
        $salesFilter = [];

        if (!empty($fromDate) && !empty($toDate)) {
            $salesFilter["created_at BETWEEN"] = [
                $fromDate . " 00:00:00",
                $toDate . " 23:59:59.999"
            ];
        } elseif (!empty($fromDate)) {
            $salesFilter["created_at"] = [
                "condition" => ">=",
                "value" => $fromDate . " 00:00:00"
            ];
        } elseif (!empty($toDate)) {
            $salesFilter["created_at"] = [
                "condition" => "<=",
                "value" => $toDate . " 23:59:59.999"
            ];
        }

        $soldProductsResult = select_from(
            'purchased_products',
            ['product_id'],
            $salesFilter
        );

        $parsedSoldProducts = json_decode($soldProductsResult, true);

        $soldProductIds = [];
        if (is_array($parsedSoldProducts) && !empty($parsedSoldProducts["success"]) && !empty($parsedSoldProducts["data"])) {
            foreach ($parsedSoldProducts["data"] as $saleRow) {
                if (!empty($saleRow["product_id"])) {
                    $soldProductIds[] = (int)$saleRow["product_id"];
                }
            }
            $soldProductIds = array_values(array_unique($soldProductIds));
        }

        // Si no hubo ventas en el rango, devolver vacío
        if (empty($soldProductIds)) {
            echo json_encode([
                "success" => true,
                "message" => "No products sold in the selected date range.",
                "data" => [],
				"summary" => [
					"total_sold_amount" => 0,
					"total_quantity_sold" => 0,
                    "products_found" => 0,
                    "average_sold_amount_per_product" => 0,
                    "from_date" => $fromDate ?: null,
                    "to_date" => $toDate ?: null
				]
            ]);
            exit;
        }

        $where["product_id IN"] = $soldProductIds;
    }

    $reportsResult = select_from('products', [
        'product_id',
		'company_id',
        'product_name',
		'hs_code',
        'product_type',
        'product_mark',
        'product_model',
		'product_sub_model',
        'price',
        'quantity',
        'created_by',
        'created_at'
    ], $where, [
        'order_by' => 'created_at',
        'order_direction' => 'DESC'
    ]);

    $parsedResults = json_decode($reportsResult, true);

	if (!is_array($parsedResults) || empty($parsedResults["success"])) {
        throw new Exception("Error fetching products.");
    }

    if (empty($parsedResults["data"])) {
        echo json_encode([
            "success" => true,
            "message" => "No products available.",
            "data" => [],
			"summary" => [
				"total_sold_amount" => 0,
				"total_quantity_sold" => 0,
                "products_found" => 0,
                "average_sold_amount_per_product" => 0,
                "from_date" => $fromDate ?: null,
                "to_date" => $toDate ?: null
			]
        ]);
        exit;
    }

	$grandTotalSoldAmount = 0.0;
	$grandTotalSoldQty = 0;

    foreach ($parsedResults["data"] as &$report) {
        $reportId = (int)($report["product_id"] ?? 0);
        $report["sold"] = 0;
        $report["sold_total"] = 0;

        if ($reportId <= 0) {
            continue;
        }

        $salesWhere = [
            "product_id" => $reportId
        ];

        if (!empty($fromDate) && !empty($toDate)) {
			$salesWhere["created_at BETWEEN"] = [
				$fromDate . " 00:00:00",
				$toDate . " 23:59:59.999"
			];
		} elseif (!empty($fromDate)) {
			$salesWhere["created_at"] = [
				"condition" => ">=",
				"value" => $fromDate . " 00:00:00"
			];
		} elseif (!empty($toDate)) {
			$salesWhere["created_at"] = [
				"condition" => "<=",
				"value" => $toDate . " 23:59:59.999"
			];
		}

        $report["sold"] = 0;

        if ($reportId > 0) {
            $soldResult = select_from(
                'purchased_products',
                [
					'SUM(quantity) AS total_sold',
                	'SUM(total) AS total_amount_sold'
				],
                $salesWhere,
                ['fetch_first' => true]
            );

            $parsedSoldResult = json_decode($soldResult, true);

            if (
                is_array($parsedSoldResult) &&
                !empty($parsedSoldResult["success"]) &&
                isset($parsedSoldResult["data"]["total_sold"])
            ) {
                $report["sold"] = isset($parsedSoldResult["data"]["total_sold"])
					? (int)$parsedSoldResult["data"]["total_sold"]
					: 0;

				$report["sold_total"] = isset($parsedSoldResult["data"]["total_amount_sold"])
					? (float)$parsedSoldResult["data"]["total_amount_sold"]
					: 0;

				$grandTotalSoldQty += (int)$report["sold"];
				$grandTotalSoldAmount += (float)$report["sold_total"];
            }
        }
    }
    unset($report);

	$productsData = [];

	foreach ($parsedResults["data"] ?? [] as $product) {
		$enriched = mapProductRelations($product, $companyFilter);

		if ($enriched) {
			$productsData[] = $enriched;
		}
	}

	$productsFound = count($parsedResults["data"]);
    $averageSoldAmountPerProduct = $productsFound > 0
        ? $grandTotalSoldAmount / $productsFound
        : 0;

    $response = [
        "success" => true,
        "message" => "Products fetched successfully",
        "data" => $productsData,
        "summary" => [
            "total_sold_amount" => round($grandTotalSoldAmount, 2),
			"total_quantity_sold" => $grandTotalSoldQty,
            "products_found" => $productsFound,
            "average_sold_amount_per_product" => round($averageSoldAmountPerProduct, 2),
            "from_date" => $fromDate ?: null,
            "to_date" => $toDate ?: null
        ]
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;