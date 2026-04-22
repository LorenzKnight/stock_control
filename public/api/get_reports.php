<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Could not fetch reports",
    "data" => []
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

    $search = $_GET['search'] ?? '';
	$productId = isset($_GET['product_id']) && is_numeric($_GET['product_id'])
        ? (int)$_GET['product_id']
        : null;

	$userInfo = select_from(
        "users",
        ["company_id"],
        ["user_id" => $userId],
        ["fetch_first" => true]
    );

    $parsedUserInfo = json_decode($userInfo, true);

    if (
        !is_array($parsedUserInfo) ||
        empty($parsedUserInfo["success"]) ||
        empty($parsedUserInfo["data"]["company_id"])
    ) {
        throw new Exception("Company not found for this user.");
    }

    $companyId = (int)$parsedUserInfo["data"]["company_id"];

    $where = [
		"company_id" => $companyId,
	];

    if (!empty($productId)) {
        $where["product_id"] = $productId;
    }

    if (!empty($search)) {
        $where['OR'] = [
            'CAST(product_name AS TEXT) ILIKE' => "%$search%",
            'CAST(hs_code AS TEXT) ILIKE' => "%$search%"
        ];
    }

    $reportsResult = select_from('products', [
        'product_id',
        'product_name',
        'product_type',
        'product_mark',
        'product_model',
        'hs_code',
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
            "data" => []
        ]);
        exit;
    }

    foreach ($parsedResults["data"] as &$report) {
        $reportId = (int)($report["product_id"] ?? 0);

        $report["sold"] = 0;

        if ($reportId > 0) {
            $soldResult = select_from(
                'purchased_products',
                ['SUM(quantity) AS total_sold'],
                ['product_id' => $reportId],
                ['fetch_first' => true]
            );

            $parsedSoldResult = json_decode($soldResult, true);

            if (
                is_array($parsedSoldResult) &&
                !empty($parsedSoldResult["success"]) &&
                isset($parsedSoldResult["data"]["total_sold"])
            ) {
                $report["sold"] = (int)$parsedSoldResult["data"]["total_sold"];
            }
        }

        // $transferedResult = select_from('storage_transfers', [
        //     'SUM(quantity) AS total_transfered'
        // ], [
        //     'product_id' => $reportId
        // ], [
        //     'fetch_first' => true
        // ]);

        // $parsedTransferedResult = json_decode($transferedResult, true);
        // $report['transfered'] = $parsedTransferedResult["data"]["total_transfered"] ?? 0;
    }
    unset($report);

    $response = [
        "success" => true,
        "message" => "Products fetched successfully",
        "data" => $parsedResults["data"]
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;