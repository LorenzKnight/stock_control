<?php
use App\Shippings\ShippingRepository;
use App\Shippings\ShippingService;

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
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$authUser = requireAuth();
	$userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

	if ($userId <= 0) throw new Exception("User session not found.");
	if ($companyId <= 0) throw new Exception("Company ID is required.");

    if (!check_user_permission($userId, 'data_handler')) {
		throw new Exception("Access denied. You do not have permission to edit data.");
	}

	if (empty($_POST["edit_shipping_id"]) || !is_numeric($_POST["edit_shipping_id"])) {
		throw new Exception("Missing shipping ID.");
	}

	$shippingId = (int) $_POST["edit_shipping_id"];

	$repository = new ShippingRepository();
	$service = new ShippingService($repository);

	$service->updateShipping(
		$shippingId,
		$companyId,
		[
			"shipping_method" => (int)($_POST["edit_shipping_method"] ?? 1),
			"destination" => $_POST["edit_destination"] ?? '',
			"delivery_date" => $_POST["edit_delivery_date"] ?? '',
			"description" => $_POST["edit_description"] ?? '',
			"status" => isset($_POST["edit_status"]) && $_POST["edit_status"] == "1" ? 1 : 0
		]
	);

	// AQUI
	// triggerRealtimeNotification($userId);

	log_activity(
		$userId,
		"update shipping",
		"User updated shipping info (ID: $shippingId).",
		"shippings",
		$shippingId
	);

	$response = [
		"success" => true,
		"message" => "shipping updated successfully.",
		"img_gif" => "images/sys-img/loading1.gif",
		"redirect_url" => ""
	];
} catch (Throwable $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;