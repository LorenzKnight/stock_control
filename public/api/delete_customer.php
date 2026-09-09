<?php
use App\Customers\CustomerRepository;
use App\Customers\CustomerService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "../images/sys-img/error.gif",
	"redirect_url" => ""
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$authUser = requireAuth();
    $userId = (int)($authUser["user_id"] ?? 0);

    if ($userId <= 0) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }

	if (!check_user_permission($userId, 'platform_admin')) {
		throw new Exception("Access denied. You do not have permission to delete data.");
	}

	$customerIdRaw = $_POST["customer_id"] ?? null;

	if (empty($customerIdRaw) || !is_numeric($customerIdRaw)) {
		throw new Exception("Missing or invalid customer ID.");
	}

	$customerId = (int)$customerIdRaw;

	$deleteImgResult =
		delete_image_from_record([
			"table" => "customers",
			"id_column" => "customer_id",
			"id_value" => $customerId,
			"image_column" => "customer_image",
			"image_folder" => "images/customers",
			"clear_db" => false
		]);

	if (empty($deleteImgResult["success"])) {
		throw new Exception("Image deletion failed: " . ($deleteImgResult["message"] ?? "Unknown error"));
	}

	$repository = new CustomerRepository();
	$service = new CustomerService($repository);

	$service->deleteCustomer(
		$customerId
	);

	log_activity(
		$userId,
		"delete_customer",
		"User deleted a customer (ID: {$customerId}).",
		"customers",
		$customerId
	);

	$response = [
		"success" => true,
		"message" => "Customer deleted successfully.",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => ""
	];

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;