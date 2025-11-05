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

    $shippingResult = json_decode($shippingQuery, true);
    if (empty($shippingResult["success"]) || empty($shippingResult["data"])) {
        throw new Exception("Shipping not found.");
    }

    $shippingData = $shippingResult["data"];
    $companyId = $shippingData["company_id"] ?? null;
    if (!$companyId) {
        throw new Exception("Shipping has no company assigned.");
    }

    $companyQuery = select_from("companies", [
        "company_id",
        "company_name",
        "organization_no",
        "company_address",
        "country_code",
        "company_phone",
        "company_logo"
    ], ["company_id" => $companyId], ["fetch_first" => true]);

    $companyResult = json_decode($companyQuery, true);
    $companyData = $companyResult["success"] ? ($companyResult["data"] ?? null) : null;

	$response = [
		"success" => true,
		"message" => "Shipping and loads loaded successfully.",
		"data"    => [
			"shipping"  => $shippingData,
            "company"   => $companyData
		]
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

ob_end_clean();

echo json_encode($response);
exit;