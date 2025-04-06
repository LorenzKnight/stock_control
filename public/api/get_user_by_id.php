<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"data" => [],
	"ranks" => []
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		throw new Exception("Method not allowed");
	}

	if (empty($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
		throw new Exception("Invalid or missing user ID.");
	}

	$userId = intval($_GET['user_id']);

	$userInfo = select_from("users", [
		"user_id",
        "name",
        "surname",
        "birthday",
        "phone",
        "email",
        "rank",
		"status"
	], ["user_id" => $userId], ["fetch_first" => true]);

	$userData = json_decode($userInfo, true);

	if ($userData["success"] && !empty($userData["data"])) {
		$ranks = GlobalArrays::$ranks;
		
		$response = [
			"success" => true,
			"message" => "User data retrieved successfully",
			"data" => $userData["data"],
			"ranks" => $ranks
		];
	} else {
		throw new Exception("User not found.");
	}
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>