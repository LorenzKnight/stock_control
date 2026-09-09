<?php
use App\Customers\CustomerRepository;
use App\Customers\CustomerService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success"		=> false,
	"message"		=> "Invalid request",
	"img_gif"		=> "../images/sys-img/error.gif",
	"redirect_url"	=> ""
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$authUser = requireAuth();
    $userId = $authUser["user_id"];

	$companyId = 
		isset($authUser["company_id"]) 
			? (int)$authUser["company_id"] 
			: null;

	if ($userId <= 0) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

	if (!check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$customerData = [
		"customer_name" =>
			trim($_POST["customer_name"] ?? ''),

		"customer_surname" =>
			trim($_POST["customer_surname"] ?? ''),

		"customer_email" =>
			trim($_POST["customer_email"] ?? ''),

		"customer_address" =>
			trim($_POST["customer_address"] ?? ''),

		"cu_country_code" =>
			trim($_POST["customer_country_code"] ?? ''),

		"customer_phone" =>
			trim($_POST["customer_phone"] ?? ''),

		"customer_birthday" =>
			trim($_POST["customer_birthday"] ?? ''),

		"customer_document_type" =>
			(int)($_POST["customer_document_type"] ?? 0),

		"customer_document_no" =>
			trim($_POST["customer_document_no"] ?? ''),

		"customer_type" =>
			(int)($_POST["customer_type"] ?? 0),

		"customer_status" =>
			(int)($_POST["customer_status"] ?? 0),

		"references_1" =>
			trim($_POST["references_1"] ?? ''),

		"r1_country_code" =>
			trim($_POST["references_1_country_code"] ?? ''),

		"references_1_phone" =>
			trim($_POST["references_1_phone"] ?? ''),

		"references_2" =>
			trim($_POST["references_2"] ?? ''),

		"r2_country_code" =>
			trim($_POST["references_2_country_code"] ?? ''),

		"references_2_phone" =>
			trim($_POST["references_2_phone"] ?? '')
	];

	$imageName = null;

	try {
		$imageName = handle_uploaded_image(
			"customer_image",
			__DIR__ . "/../images/customers/",
			"customer",
			$userId,
			["jpg", "jpeg", "png", "webp"]
		);
	} catch (Exception $ex) {
		throw new Exception("Image upload failed: " . $ex->getMessage());
	}

	$repository = new CustomerRepository();
	$service = new CustomerService($repository);

	$result = $service->createCustomer(
		$userId,
		$companyId,
		$customerData,
		$imageName
	);

	log_activity(
		$userId,
		"create_customer",
		"User added a new customer: " . $result["customer_name"],
		"customers",
		$result["customer_id"]
	);

	$response = [
		"success"			=> true,
		"message"			=> "Customer created successfully!",
		"img_gif"			=> "../images/sys-img/loading1.gif",
		"redirect_url"		=> "",
		"show_reward_modal" => $result["show_reward_modal"],
		"reward_type"		=> $result["reward_type"],
		"customer_id"		=> $result["customer_id"],
		"customer_name" 	=> $result["customer_name"]
	];

} catch (Exception $e) {
	$response = [
		"success"		=> false,
		"message"		=> $e->getMessage(),
		"img_gif"		=> "../images/sys-img/error.gif",
		"redirect_url"	=> ""
	];
}

echo json_encode($response);
exit;