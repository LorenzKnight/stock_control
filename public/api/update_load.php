<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "images/sys-img/error.gif",
	"redirect_url" => null
];

try {
    $authUser = requireAuth();
    $companyId = $authUser["company_id"] ?? null;
    $userId = $authUser["user_id"] ?? null;

    $input = json_decode(file_get_contents("php://input"), true);
    if (empty($input)) throw new Exception("Invalid JSON input.");

    $loadId = $input["load_id"] ?? null;
    if (empty($loadId)) throw new Exception("Load ID is required.");

    // Datos principales
    $fields = [
        "customer_id" => $input["customer_id"],
        "from_currency" => $input["from_currency"],
        "to_currency" => $input["to_currency"],
        "price_per_kg" => $input["price_per_kg"],
        "total_kg" => $input["total_kg"],
        "discount" => $input["discount"],
        "taxes" => $input["taxes"],
        "price_total_exchanged" => $input["price_total_exchanged"],
        "destination" => $input["destination"],
        "comment" => $input["comment"],
        "company_id" => $companyId
    ];

    // ✅ Actualizar tabla principal
    $updateResult = update_table("loads", $fields, ["load_id" => $loadId]);
    $updateParsed = json_decode($updateResult, true);

    if (empty($updateParsed["success"])) {
        throw new Exception("Failed to update load details.");
    }

    // ✅ Actualizar productos
    $products = $input["products"] ?? [];
    if (!empty($products)) {
        // Borrar productos previos
        delete_from("loaded_products", ["load_id" => $loadId]);

        // Insertar los nuevos productos
        foreach ($products as $p) {
            insert_into("loaded_products", [
                "load_id" => $loadId,
                "product_id" => $p["product_id"],
                "quantity" => $p["quantity"],
                "total_kg" => $p["total_kg"],
                "from_currency" => $input["from_currency"],
                "total_kg_price" => $p["total_kg_price"],
                "to_currency" => $input["to_currency"],
                "total_price_exchanged" => $p["total_price_exchanged"]
            ]);
        }
    }

    log_activity(
		$userId,
		"update load",
		"User updated load info (ID: $loadId).",
		"loads",
		$loadId
	);

	// 🔔 Opcional: enviar notificación en tiempo real
	// triggerRealtimeNotification($userId);

    $response = [
        "success" => true,
        "message" => "Load updated successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
		"redirect_url" => ""
    ];

} catch (Exception $e) {
    $response = [
		"success" => false,
		"message" => $e->getMessage(),
		"img_gif" => "../images/sys-img/error.gif",
		"redirect_url" => ""
	];
}

echo json_encode($response);
exit;