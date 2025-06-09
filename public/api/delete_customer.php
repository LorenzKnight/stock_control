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

	if (!check_user_permission($userId, 'export_reports')) {
		throw new Exception("Access denied. You do not have permission to delete data.");
	}

	if (empty($_POST["customer_id"]) || !is_numeric($_POST["customer_id"])) {
		throw new Exception("Missing or invalid customer ID.");
	}

	$customerId = (int)($_POST["customer_id"]);

	$deleteImgResult = delete_image_from_record([
		"table"        => "customers",
		"id_column"    => "customer_id",
		"id_value"     => $customerId,
		"image_column" => "customer_image",
		"image_folder" => "images/customers"
	]);

	if (!$deleteImgResult["success"]) {
		throw new Exception("Image deletion failed: " . $deleteImgResult["message"]);
	}

	$deleteResponse = delete_from("customers", ["customer_id" => $customerId]);
	$deleteResult = json_decode($deleteResponse, true);

	if (!$deleteResult["success"]) {
		throw new Exception("Database error while deleting customer.");
	}

	if (empty($deleteResult["count"])) {
		throw new Exception("No customer found with the provided ID.");
	}

	log_activity(
		$userId,
		"delete_customer",
		"User deleted a customer (ID: {$customerId}).",
		"customers",
		$customerId
	);

	$response["success"] = true;
	$response["message"] = "Customer deleted successfully.";
	$response["img_gif"] = "../images/sys-img/loading1.gif";
	$response["redirect_url"] = "";

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>