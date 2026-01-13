<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Unable to update general settings",
    "data" => []
];

try {
    // 🔐 Validar método
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed.");
    }

    // 🔐 Autenticación
    $authUser = requireAuth();
    $userId = $authUser["user_id"] ?? null;

    if (!$userId) {
        throw new Exception("Unauthorized access.");
    }

    // 📥 Datos POST
    $companyId = $_POST["company_id"] ?? null;
    $companyCurrency = $_POST["company_currency"] ?? null;
    $shippingKgPrice = $_POST["shipping_kg_price"] ?? null;

    if (!$companyId || !is_numeric($companyId)) {
        throw new Exception("Invalid company ID.");
    }

    // 🔎 Verificar si ya existen settings para la empresa
    $existingRaw = select_from(
        "settings",
        ["settings_id"],
        ["company_id" => $companyId],
        ["fetch_first" => true]
    );

    $existing = json_decode($existingRaw, true);

    if ($existing["success"] && !empty($existing["data"])) {
        // 🔄 UPDATE
        update_table(
            "settings",
            [
                "company_currency" => $companyCurrency,
                "shipping_kg_price" => $shippingKgPrice
            ],
            [
                "company_id" => $companyId
            ]
        );
    } else {
        // ➕ INSERT
        insert_into(
            "settings",
            [
                "company_id" => $companyId,
                "company_currency" => $companyCurrency,
                "shipping_kg_price" => $shippingKgPrice,
                "created_by" => $userId
            ]
        );
    }

    // 🔄 VOLVER A LEER LA CONFIGURACIÓN COMPLETA (future-proof)
    $settingsRaw = select_from(
        "settings",
        ["*"],
        ["company_id" => $companyId],
        ["fetch_first" => true]
    );

    $settingsData = json_decode($settingsRaw, true);

    if (!$settingsData["success"] || empty($settingsData["data"])) {
        throw new Exception("Settings could not be loaded after update.");
    }

    // ✅ RESPUESTA FINAL
    $response = [
        "success" => true,
        "message" => "General settings updated successfully.",
        "data" => $settingsData["data"]
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;