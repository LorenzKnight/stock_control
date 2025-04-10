<?php
ob_start();
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
		throw new Exception("Method not allowed.");
	}

	$sessionUserId = $_SESSION["sc_UserId"] ?? null;
	if (!$sessionUserId) {
		throw new Exception("User session not found.");
	}

	$targetUserId = $_POST["user_id"] ?? null;
	if (!$targetUserId || !is_numeric($targetUserId)) {
		throw new Exception("Invalid user ID.");
	}

	$checkUser = select_from("users", ["user_id"], [
		"user_id" => $targetUserId,
		"parent_user" => $sessionUserId
	], ["fetch_first" => true]);

	$checkResult = json_decode($checkUser, true);

	if (!$checkResult["success"] || empty($checkResult["data"])) {
		throw new Exception("This user does not belong to you or does not exist.");
	}

	$imgDelete = delete_image_from_record([
		"table"        => "users",
		"id_column"    => "user_id",
		"id_value"     => $targetUserId,
		"image_column" => "image",
		"image_folder" => "images/profile"
	]);

	if (!$imgDelete["success"]) {
		throw new Exception("Image deletion failed: " . $imgDelete["message"]);
	}

	$deleteResponse = delete_from("users", ["user_id" => $targetUserId]);
	$deleteResult = json_decode($deleteResponse, true);

	if (!$deleteResult["success"]) {
		throw new Exception("Deletion failed.");
	}

	log_activity(
		$sessionUserId,
		"delete_user",
		"User deleted a co-worker (ID: {$targetUserId}).",
		"users",
		$targetUserId
	);

	$response = [
		"success" => true,
		"message" => "User deleted successfully.",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => ""
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
exit;
?>