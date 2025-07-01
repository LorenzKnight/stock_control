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

	if (!check_user_permission($userId, 'manage_users')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$userData = json_decode(select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]), true);
	if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }
	$userInfo = $userData["data"];

	$altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];

	$requiredFields = ["name", "surname", "birthday", "phone", "email", "password", "company", "rank"];
	$data = [];

	foreach ($requiredFields as $field) {
		if (empty($_POST[$field])) {
			throw new Exception("The $field field is required.");
		}
		$data[$field] = htmlspecialchars(trim($_POST[$field]));
	}

	$emailCheckResponse = select_from("users", ["user_id"], ["email" => $data["email"]], ["fetch_first" => true]);
	$emailCheck = json_decode($emailCheckResponse, true);

	if ($emailCheck && $emailCheck["success"] && !empty($emailCheck["data"])) {
		throw new Exception("The email is already registered.");
	}

	$insertData = [
		"name"			=> $data["name"],
		"surname"		=> $data["surname"],
		"birthday"		=> $data["birthday"],
		"phone"			=> $data["phone"],
		"email"			=> $data["email"],
		"password"		=> password_hash($data["password"], PASSWORD_DEFAULT),
		"rank"			=> $data["rank"],
		"parent_user"	=> $altUser,
		"company_id"	=> $data["company"],
		"status"		=> 1,
		"username"		=> strtolower($data["name"] . "_" . $data["surname"]),
		"verified"		=> 0,
		"signup_date"	=> date("Y-m-d H:i:s")
	];

	$insertResponse = insert_into("users", $insertData, ["id" => "user_id"]);
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