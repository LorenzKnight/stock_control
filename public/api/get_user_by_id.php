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
		"company_id",
		"status"
	], ["user_id" => $userId], ["fetch_first" => true]);

	$userData = json_decode($userInfo, true);

	if ($userData["success"] && !empty($userData["data"])) {
		$minRoleId = 1;
		$rolesResponse = select_from("roles", ["role_id", "role_name"], [
			"role_id" => ["condition" => ">=", "value" => $minRoleId]
		], [
			"order_by" => "role_id",
			"order_direction" => "ASC"
		]);

		$rolesData = json_decode($rolesResponse, true);
		$ranks = [];

		if ($rolesData["success"] && !empty($rolesData["data"])) {
			foreach ($rolesData["data"] as $role) {
				$ranks[$role["role_id"]] = $role["role_name"];
			}
		}
		
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