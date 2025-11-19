<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "images/sys-img/error.gif",
	"redirect_url" => null
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'data_handler')) {
		throw new Exception("Access denied. You do not have permission to edit data.");
	}

	if (empty($_POST["edit_shipping_id"]) || !is_numeric($_POST["edit_shipping_id"])) {
		throw new Exception("Missing shipping ID.");
	}
	$shippingId = (int) $_POST["edit_shipping_id"];

    $shippingMethod	= intval($_POST["edit_shipping_method"] ?? 1);
    $destination	= trim($_POST["edit_destination"] ?? '');
	$deliveryDate	= trim($_POST["edit_delivery_date"] ?? '');
    $description	= trim($_POST["edit_description"] ?? '');
	$status			= isset($_POST["edit_status"]) && $_POST["edit_status"] == "1" ? 1 : 0;

	$shippingData = [
		"shipping_method"   => $shippingMethod,
        "destination"    	=> $destination,
        "delivery_date"   	=> $deliveryDate,
        "description"		=> $description,
		"status"			=> $status
	];

	$updateResult = json_decode(update_table("shippings", $shippingData, ["shippings_id" => $shippingId]), true);
	if (!$updateResult["success"]) {
		throw new Exception("Database update failed.");
	}

	// AQUI
	// triggerRealtimeNotification($userId);

	log_activity(
		$userId,
		"update shipping",
		"User updated shipping info (ID: $shippingId).",
		"products",
		$shippingId
	);

	$response = [
		"success" => true,
		"message" => "shipping updated successfully.",
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