<?php
use App\Customers\CustomerRepository;
use App\Customers\CustomerService;

require_once ('../inc/cors.php');
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
    $userId = $authUser["user_id"];

	if ($userId <= 0) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }

	if (!check_user_permission($userId, 'data_handler')) {
		throw new Exception("Access denied. You do not have permission to edit data.");
	}

	$customerId = (int)($_POST["edit_customer_id"] ?? 0);
	if ($customerId <= 0) throw new Exception("Missing customer ID.");

	$customerData = [
		"customer_name" =>
			trim($_POST["edit_customer_name"] ?? ''),

		"customer_surname" =>
			trim($_POST["edit_customer_surname"] ?? ''),

		"customer_email" =>
			trim($_POST["edit_customer_email"] ?? ''),

		"customer_address" =>
			trim($_POST["edit_customer_address"] ?? ''),

		"cu_country_code" =>
			trim($_POST["edit_customer_country_code"] ?? ''),

		"customer_phone" =>
			trim($_POST["edit_customer_phone"] ?? ''),

		"customer_birthday" =>
			trim($_POST["edit_customer_birthday"] ?? ''),

		"customer_document_type" =>
			(int)($_POST["edit_customer_document_type"] ?? 0),

		"customer_document_no" =>
			trim($_POST["edit_customer_document_no"] ?? ''),

		"customer_type" =>
			(int)($_POST["edit_customer_type"] ?? 0),

		"customer_status" =>
			isset($_POST["edit_customer_status"])
				? 1
				: 0,

		"references_1" =>
			trim($_POST["edit_references_1"] ?? ''),

		"r1_country_code" =>
			trim($_POST["edit_references_1_country_code"] ?? ''),

		"references_1_phone" =>
			trim($_POST["edit_references_1_phone"] ?? ''),

		"references_2" =>
			trim($_POST["edit_references_2"] ?? ''),

		"r2_country_code" =>
			trim($_POST["edit_references_2_country_code"] ?? ''),

		"references_2_phone" =>
			trim($_POST["edit_references_2_phone"] ?? '')
	];

	$repository = new CustomerRepository();
	$service = new CustomerService($repository);

	$previousImage =
		$service->getCustomerImage(
			$customerId
		);

	try {
		$imageName = handle_uploaded_image(
			"edit_customer_image",
			__DIR__ . "/../images/customers",
			"customer",
			$userId,
			["jpg", "jpeg", "png", "webp"],
			$previousImage
		);
	} catch (Exception $e) {
		throw new Exception("Image upload failed: " . $e->getMessage());
	}

	$service->updateCustomer(
		$customerId,
		$customerData,
		$imageName
	);

	log_activity(
		$userId,
		"update_customer",
		"User updated customer ID: {$customerId}",
		"customers",
		$customerId
	);

	$response = [
		"success" => true,
		"message" => "Customer updated successfully!",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => ""
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;