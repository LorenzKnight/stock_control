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

    $products = select_from("products", [
        "product_id",
        "product_name",
        "product_mark",
        "product_model",
        "product_sub_model",
        "description",
        "product_year",
        "product_image",
        "product_type",
        "prise"
    ], [
        "company_id" => $companyId
    ], [
        "order_by" => "created_at DESC"
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
            $markParsed = json_decode($markRes, true);
            $product["mark_name"] = $markParsed["data"]["category_name"] ?? null;
        }

        // Nombre del modelo
        if (!empty($product['product_model'])) {
            $modelRes = select_from("category", ["category_name"], [
                "category_id" => $product['product_model']
            ], ["fetch_first" => true]);
            $modelParsed = json_decode($modelRes, true);
            $product["model_name"] = $modelParsed["data"]["category_name"] ?? null;
        }

        // Nombre del submodelo
        if (!empty($product['product_sub_model'])) {
            $subRes = select_from("category", ["category_name"], [
                "category_id" => $product['product_sub_model']
            ], ["fetch_first" => true]);
            $subParsed = json_decode($subRes, true);
            $product["submodel_name"] = $subParsed["data"]["category_name"] ?? null;
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