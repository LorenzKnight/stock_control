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

	$companyId		= $userData["company_id"] ?? null;

	$method			= intval($_POST["shipping_method"] ?? 1);
	$destination	= trim($_POST["destination"] ?? '');
	$description	= trim($_POST["description"] ?? '');
	$status			= intval($_POST["status"] ?? 0);

	$newOrdNo = get_next_increment_value("shippings", "shipping_no", $companyId, 30000000);

	if ($destination === '') {
		throw new Exception("Destination is required.");
	}

	$insertShippingData = [
		"shipping_no"				=> $newOrdNo,
		"company_id"				=> $companyId,
		"shipping_img"				=> null,
		"shipping_method"			=> $method,
		"destination"				=> $destination,
		"description"				=> $description,
		"status"					=> $status,
		"create_by"					=> $userId,
		"created_at"				=> date("Y-m-d H:i:s")
	];
	
	$insertResponse = insert_into("shippings", $insertShippingData, ["id" => "shippings_id"]);
	$insertResult = json_decode($insertResponse, true);

	if (!$insertResult["success"]) {
		throw new Exception("Error saving customer data.");
	}

	log_activity(
		$userId,
		"create_customer",
		"New shipment is added",
		"shippings",
		$insertResult["id"] ?? null
	);

	$response = [
		"success"		=> true,
		"message"		=> "Shipment created successfully!",
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