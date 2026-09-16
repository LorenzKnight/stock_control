<?php
use App\ProductTypes\ProductTypeRepository;
use App\ProductTypes\ProductTypeService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success"   => false,
    "message"   => "No company info found",
    "count"     => 0,
    "data"      => []
];

try {
    $authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;
    
    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

    $selectCompany = $_GET["select_company"] ?? '';

    $companyId = $selectCompany !== '' && is_numeric($selectCompany)
        ? (int)$selectCompany
        : null;

	$repository = new ProductTypeRepository();
	$service = new ProductTypeService($repository);

	$productTypes = $service->getProductTypes(
        $userId,
        $companyId
    );

    if (!empty($productTypes)) {
        $response = [
            "success"   => true,
            "message"   => "Product type info loaded.",
            "count"     => count($productTypes),
            "data"      => $productTypes
        ];
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;