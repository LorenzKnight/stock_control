<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Load not found",
    "data" => null
];

try {
    $authUser = requireAuth();
    $companyId = $authUser["company_id"] ?? null;

    $loadId = $_GET["load_id"] ?? null;
    if (empty($loadId)) {
        throw new Exception("Load ID is required.");
    }

    // 1️⃣ Buscar la carga
    $loadQuery = select_from("loads", [
        "load_id",
        "load_no",
        "company_id",
        "shippings_id",
        "customer_id",
        "from_currency",
        "to_currency",
        "price_per_kg",
        "total_kg",
        "price_sum",
        "taxes",
        "discount",
        "price_total",
        "price_total_exchanged",
        "destination",
        "comment",
        "status",
        "created_at"
    ], ["load_id" => $loadId], ["fetch_first" => true]);

    $parsedLoad = json_decode($loadQuery, true);
    if (empty($parsedLoad["data"])) {
        throw new Exception("Load not found.");
    }

    $load = $parsedLoad["data"];

    // 2️⃣ Cliente
    $customerQuery = select_from("customers", [
        "customer_id", "customer_name", "customer_surname", "customer_phone",
        "customer_image", "customer_document_no"
    ], ["customer_id" => $load["customer_id"]], ["fetch_first" => true]);
    $customer = json_decode($customerQuery, true)["data"] ?? [];

    // 3️⃣ Productos
    $productsQuery = select_from("loaded_products", [
        "product_id", "quantity", "total_kg", "from_currency", "total_kg_price",
        "to_currency", "total_price_exchanged"
    ], ["load_id" => $loadId]);
    $products = json_decode($productsQuery, true)["data"] ?? [];

    $productsData = [];
    foreach ($products as $p) {
        $productInfo = select_from("products", [
            "product_name", "product_image", "product_mark", "product_model", "product_sub_model"
        ], ["product_id" => $p["product_id"]], ["fetch_first" => true]);
        $productDetails = json_decode($productInfo, true)["data"] ?? [];
        $productsData[] = array_merge($p, $productDetails);
    }

    // ✅ Construir respuesta
    $response = [
        "success" => true,
        "message" => "Load data retrieved successfully.",
        "data" => [
            "load_id" => $load["load_id"],
            "shipping_id" => $load["shippings_id"],
            "customer" => [
                "customer_id" => $customer["customer_id"],
                "full_name" => trim(($customer["customer_name"] ?? '') . ' ' . ($customer["customer_surname"] ?? '')),
                "phone" => $customer["customer_phone"] ?? '',
                "image" => $customer["customer_image"] ?? ''
            ],
            "products" => $productsData,
            "from_currency" => $load["from_currency"],
            "to_currency" => $load["to_currency"],
            "price_per_kg" => $load["price_per_kg"],
            "total_kg" => $load["total_kg"],
            "price_sum" => $load["price_sum"],
            "taxes" => $load["taxes"],
            "discount" => $load["discount"],
            "price_total" => $load["price_total"],
            "price_total_exchanged" => $load["price_total_exchanged"],
            "destination" => $load["destination"],
            "comment" => $load["comment"],
            "status" => $load["status"],
            "created_at" => $load["created_at"]
        ]
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;