<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request.",
	"img_gif" => "../images/sys-img/error.gif",
	"redirect_url" => ""
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed.");
	}

	$input = json_decode(file_get_contents("php://input"), true);
	$userId = $input["user_id"] ?? null;

	if (!$userId || !is_numeric($userId)) {
		throw new Exception("Invalid user ID.");
	}

	$deleteQuery = pg_query("DELETE FROM users WHERE user_id = " . intval($userId));

	if (!$deleteQuery) {
		throw new Exception("Failed to delete user.");
	}

	log_activity(
		$_SESSION["sc_UserId"] ?? null,
		"delete_user",
		"Deleted user with ID $userId",
		"users",
		$userId
	);

	$response = [
		"success" => true,
		"message" => "User deleted successfully.",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => "../my_profile.php"
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