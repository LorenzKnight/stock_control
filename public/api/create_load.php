<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Load could not be created",
    "img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'manage_sales')) {
        throw new Exception("Access denied. You do not have permission to create loads.");
    }

    // Obtener company_id del usuario
    $userInfo = json_decode(select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]), true);
    $userData = $userInfo["data"];
    
    $companyId = $userData["company_id"] ?? null;

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception("No data received.");

    // Validaciones básicas
    $required = ["customer_id", "shippings_id", "from_currency", "to_currency", "price_per_kg", "total_kg"];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === "") {
            throw new Exception("Missing required field: $field");
        }
    }

    // Normalizar datos
    $shippingsId     = (int)$input["shippings_id"];
    $customerId      = (int)$input["customer_id"];
    $fromCurrency    = strtoupper(trim($input["from_currency"]));
    $toCurrency      = strtoupper(trim($input["to_currency"]));
    $pricePerKg      = number_format((float)$input["price_per_kg"], 2, '.', '');
    $totalKg         = number_format((float)$input["total_kg"], 3, '.', '');
    $discount        = number_format((float)($input["discount"] ?? 0), 2, '.', '');
    $taxes           = number_format((float)($input["taxes"] ?? 0), 2, '.', '');
    $destination     = trim($input["destination"] ?? '');
    $comment         = trim($input["comment"] ?? '');
    $status          = 1;

    // Calcular sumas
    $priceSum = $pricePerKg * $totalKg;
    $subtotal = $priceSum - $discount;
    $taxAmount = ($subtotal * $taxes) / 100;
    $priceTotal = $subtotal + $taxAmount;

    // 🔄 Calcular conversión de moneda si hay diferencia entre from y to
    $priceTotalExchanged = isset($input["price_total_exchanged"]) 
        ? floatval($input["price_total_exchanged"]) 
        : $priceTotal;

    // Crear número de carga
    $newLoadNo = get_next_increment_value("loads", "load_no", $companyId, 40000000);

    // Construir datos para insertar
    $loadData = [
        "shippings_id"           => $shippingsId,
        "customer_id"            => $customerId,
        "company_id"             => $companyId,
        "load_no"                => $companyId.$newLoadNo,
        "from_currency"          => $fromCurrency,
        "to_currency"            => $toCurrency,
        "price_per_kg"           => $pricePerKg,
        "total_kg"               => $totalKg,
        "price_sum"              => number_format($priceSum, 2, '.', ''),
        "taxes"                  => $taxes,
        "discount"               => $discount,
        "price_total"            => number_format($priceTotal, 2, '.', ''),
        "price_total_exchanged"  => number_format($priceTotalExchanged, 2, '.', ''),
        "destination"            => $destination,
        "comment"                => $comment,
        "status"                 => $status,
        "create_by"              => $userId
    ];

    // Insertar en loads
    $insertResult = json_decode(insert_into("loads", $loadData, ["id" => "load_id"]), true);
    if (!$insertResult["success"]) {
        throw new Exception("Failed to create load record.");
    }

    $loadId = $insertResult["id"];

    // Asociar productos seleccionados
    if (!empty($input["products"]) && is_array($input["products"])) {
        foreach ($input["products"] as $p) {
            $productId = (int)($p["product_id"] ?? 0);
            if ($productId <= 0) continue;

            $quantity = max(1, (int)($p["quantity"] ?? 1));
            $totalKg       = number_format((float)($p["total_kg"] ?? 0), 3, '.', '');
            $totalKgPrice  = number_format((float)($p["total_kg_price"] ?? 0), 2, '.', '');
            $convertedPrice = floatval($p["total_price_exchanged"] ?? $totalKgPrice);

            $loadedProduct = [
                "load_id"               => $loadId,
                "product_id"            => $productId,
                "quantity"              => $quantity,
                "total_kg"              => $totalKg,
                "from_currency"         => $fromCurrency,
                "total_kg_price"        => $totalKgPrice,
                "to_currency"           => $toCurrency,
                "total_price_exchanged" => $convertedPrice,
                "create_by"             => $userId
            ];

            $prodInsert = json_decode(insert_into("loaded_products", $loadedProduct), true);
            if (!$prodInsert["success"]) {
                throw new Exception("Error adding product (ID: $productId) to load ID: $loadId");
            }
        }
    }

    // Log de actividad
    log_activity(
        $userId,
        "create_load",
        "Created new load #$loadId for shipping_id $shippingsId",
        "loads",
        $loadId
    );

    $response = [
        "success" => true,
        "message" => "Load created successfully",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;