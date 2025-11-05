<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json; charset=UTF-8");

ob_start();

$response = [
	"success" => false,
	"message" => "No shipping data found.",
	"data"    => []
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) {
		throw new Exception("User session not found.");
	}

    $shippingId = $_GET['shipping_id'] ?? null;
    if (!$shippingId) {
        throw new Exception("Missing shipping_id parameter.");
    }

    // Obtener datos del shipping
    $shippingQuery = select_from("shippings", [
        "shippings_id", "shipping_no", "company_id", "shipping_img", "destination",
        "delivery_date", "description", "status", "created_at"
    ], ["shippings_id" => $shippingId], ["fetch_first" => true]);

    $shippingData = json_decode($shippingQuery, true)['data'] ?? null;

    if (!$shippingData) {
        echo json_encode(["error" => "Shipping not found."]);
        exit;
    }

    // Cargar loads asociados
    $loads = json_decode(select_from("loads", [
        "load_id",
        "load_no",
        "customer_id",
        "total_kg",
        "price_total_exchanged",
        "destination"
    ], ["shippings_id" => $shippingId]), true)['data'] ?? [];

    $loadsData = $loadsResponse["success"] ? ($loadsResponse["data"] ?? []) : [];

	$response = [
		"success" => true,
		"message" => "Shipping and loads loaded successfully.",
		"data"    => [
			"shipping" => $shippingData,
			"loads"    => $loadsData
		]
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

ob_end_clean();

echo json_encode($response);
exit;