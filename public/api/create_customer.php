<?php
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

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	if (!check_user_permission($userId, 'create_data')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$userInfo = json_decode(select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]), true);
	$userData = $userInfo["data"];

	$companyId = $userData["company_id"] ?? null;

	$name		= trim($_POST["customer_name"] ?? '');
	$surname	= trim($_POST["customer_surname"] ?? '');
	$email		= trim($_POST["customer_email"] ?? '');
	$address	= trim($_POST["customer_address"] ?? '');
	$phone		= trim($_POST["customer_phone"] ?? '');
	$birthday   = trim($_POST["customer_birthday"] ?? '');
	$docType    = intval($_POST["customer_document_type"] ?? 0);
	$docNo      = trim($_POST["customer_document_no"] ?? '');
	$type       = intval($_POST["customer_type"] ?? 0);
	$status     = intval($_POST["customer_status"] ?? 1);
	$ref1       = trim($_POST["references_1"] ?? '');
	$ref1Phone  = trim($_POST["references_1_phone"] ?? '');
	$ref2       = trim($_POST["references_2"] ?? '');
	$ref2Phone  = trim($_POST["references_2_phone"] ?? '');

	if ($name === '') {
		throw new Exception("Customer name is required.");
	}

	if ($birthday === '') {
		throw new Exception("Customer birthday is required.");
	}

	$imageName = null;
	try {
		$imageName = handle_uploaded_image(
			"customer_image",
			__DIR__ . "/../images/customers/",
			["jpg", "jpeg", "png", "webp"],
			"customer",
			$userId
		);
	} catch (Exception $ex) {
		throw new Exception("Image upload failed: " . $ex->getMessage());
	}

	$insertData = [
		"customer_name"				=> $name,
		"customer_surname"			=> $surname,
		"customer_email"			=> $email,
		"customer_address"			=> $address,
		"customer_phone"			=> $phone,
		"customer_birthday"			=> $birthday,
		"customer_document_type"	=> $docType,
		"customer_document_no"		=> $docNo,
		"customer_type"				=> $type,
		"customer_status"			=> $status,
		"references_1"				=> $ref1,
		"references_1_phone"		=> $ref1Phone,
		"references_2"				=> $ref2,
		"references_2_phone"		=> $ref2Phone,
		"company_id"				=> $companyId,
		"create_by"					=> $userId,
		"created_at"				=> date("Y-m-d H:i:s")
	];

	if ($imageName) {
		$insertData["customer_image"] = $imageName;
	}
	
	$insertResponse = insert_into("customers", $insertData, ["id" => "customer_id"]);
	$insertResult = json_decode($insertResponse, true);

	if (!$insertResult["success"]) {
		throw new Exception("Error saving customer data.");
	}

	log_activity(
		$userId,
		"create_customer",
		"User added a new customer: {$name} {$surname}",
		"customers",
		$insertResult["id"] ?? null
	);

	$response = [
		"success"		=> true,
		"message"		=> "Customer created successfully!",
		"img_gif"		=> "../images/sys-img/loading1.gif",
		"redirect_url"	=> ""
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
?>