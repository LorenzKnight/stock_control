<?php
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

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	$customerId = intval($_POST["edit_customer_id"] ?? 0);
	if (!$customerId) throw new Exception("Missing customer ID.");

	$name       = trim($_POST["edit_customer_name"] ?? '');
	$surname    = trim($_POST["edit_customer_surname"] ?? '');
	$email      = trim($_POST["edit_customer_email"] ?? '');
	$address    = trim($_POST["edit_customer_address"] ?? '');
	$phone      = trim($_POST["edit_customer_phone"] ?? '');
	$birthday   = trim($_POST["edit_customer_birthday"] ?? '');
	$docType    = intval($_POST["edit_customer_document_type"] ?? 0);
	$docNo      = trim($_POST["edit_customer_document_no"] ?? '');
	$type       = intval($_POST["edit_customer_type"] ?? 0);
	$status     = intval($_POST["edit_customer_status"] ?? 1);
	$ref1       = trim($_POST["edit_references_1"] ?? '');
	$ref1Phone  = trim($_POST["edit_references_1_phone"] ?? '');
	$ref2       = trim($_POST["edit_references_2"] ?? '');
	$ref2Phone  = trim($_POST["edit_references_2_phone"] ?? '');

	if ($name === '') throw new Exception("Customer name is required.");

	$updateData = [
		"customer_name"            => $name,
		"customer_surname"         => $surname,
		"customer_email"           => $email,
		"customer_address"         => $address,
		"customer_phone"           => $phone,
		"customer_birthday"        => $birthday,
		"customer_document_type"   => $docType,
		"customer_document_no"     => $docNo,
		"customer_type"            => $type,
		"customer_status"          => $status,
		"references_1"             => $ref1,
		"references_1_phone"       => $ref1Phone,
		"references_2"             => $ref2,
		"references_2_phone"       => $ref2Phone
	];

	try {
		$imageName = handle_uploaded_image(
			"edit_customer_image",
			__DIR__ . "/../images/customers",
			["jpg", "jpeg", "png", "webp"],
			"customer",
			$userId
		);

		if ($imageName) {
			$updateData["customer_image"] = $imageName;
		}
	} catch (Exception $imgEx) {
		throw new Exception("Image upload failed: " . $imgEx->getMessage());
	}

	$where = ["customer_id" => $customerId];
	$updateResponse = update_table("customers", $updateData, $where);
	$updateResult = json_decode($updateResponse, true);

	if (!$updateResult["success"]) {
		throw new Exception("Update failed.");
	}

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