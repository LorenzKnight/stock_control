<?php
require_once('../inc/cors.php');
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

	$authUser = requireAuth();
    $userId = intval($authUser["user_id"] ?? 0);

    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }

	$terms = isset($_POST['terms']) ? (int)$_POST['terms'] : 0;
	$gdpr  = isset($_POST['gdpr'])  ? (int)$_POST['gdpr']  : 0;

	if ($terms !== 1 || $gdpr !== 1) {
		throw new Exception("You must accept Terms and Privacy Policy.");
	}

	$updateResponse = update_table("users",
		[
			"terms" => 1,
			"gdpr"  => 1,
		],
		[
			"user_id" => (int)$userId
		]
	);

	$update = json_decode($updateResponse, true);
	if (empty($update["success"])) {
		throw new Exception($update["message"] ?? "Update failed.");
	}

	$response = [
		"success" => true,
		"message" => "Preferences saved.",
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