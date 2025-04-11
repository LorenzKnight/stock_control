<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "../images/sys-img/error.gif",
	"redirect_url" => ""
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User not logged in.");

	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$requiredFields = ["name", "surname", "birthday", "phone", "email", "password", "rank"];
	$data = [];

	foreach ($requiredFields as $field) {
		if (empty($_POST[$field])) {
			throw new Exception("The $field field is required.");
		}
		$data[$field] = htmlspecialchars(trim($_POST[$field]));
	}

	$data["parent_user"] = $userId;
	$data["status"] = 1;
	$data["username"] = strtolower($data["name"] . "_" . $data["surname"]);
	$data["verified"] = 0;
	$data["signup_date"] = date("Y-m-d H:i:s");

	$emailCheckResponse = select_from("users", ["user_id"], ["email" => $data["email"]], ["fetch_first" => true]);
	$emailCheck = json_decode($emailCheckResponse, true);

	if ($emailCheck && $emailCheck["success"] && !empty($emailCheck["data"])) {
		throw new Exception("The email is already registered.");
	}

	$insertResponse = insert_into("users", $data, ["id" => "user_id"]);
	$insertResult = json_decode($insertResponse, true);

	if (!$insertResult["success"]) {
		throw new Exception("Error inserting into database.");
	}

	$response = [
		"success" => true,
		"message" => "Data received successfully",
		"img_gif" => "../images/sys-img/loading1.gif",
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
?>