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

	if (empty($_POST['packs'])) {
		throw new Exception("You must select a member package.");
	}

	if (!isset($_POST['estimated_cost']) || !is_numeric($_POST['estimated_cost'])) {
		throw new Exception("Estimated cost is missing or invalid.");
	}

	$selectedPack = intval($_POST['packs']);
	if ($selectedPack <= 0) {
		throw new Exception("Invalid member package value.");
	}

	$estimatedCost = floatval($_POST['estimated_cost']);
	$userId = $_SESSION["sc_UserId"] ?? null;

	if (!$userId) {
		throw new Exception("User session not found.");
	}

	if (!check_user_permission($userId, 'export_reports')) {
		throw new Exception("Access denied. You do not have permission to upgrade the supscription.");
	}

	$currentPackResponse = json_decode(select_from("users", ["package_id"], ["user_id" => $userId], ["fetch_first" => true]), true);
	$currentPackData = $currentPackResponse["data"];

	if ($currentPackData["success"] && !empty($currentPackData["package_id"])) {
		$currentPack = intval($currentPackData["package_id"]);

		if ($currentPack === $selectedPack) {
			$response = [
				"success" => false,
				"message" => "You already have this subscription package.",
				"img_gif" => "../images/sys-img/error.gif",
				"redirect_url" => ""
			];
			echo json_encode($response);
			exit;
		}
	}

	$subscriptionDate = date("Y-m-d H:i:s");
	$expirationDate = date("Y-m-d H:i:s", strtotime("+1 month"));

	$data = [
		"user_id" => $userId,
		"members_packs" => $selectedPack,
		"estimated_cost" => $estimatedCost,
		"subscription_date" => $subscriptionDate,
		"expiration_date" => $expirationDate
	];

	$insertResponse = insert_into("subscriptions", $data, ["id" => "subsc_id"]);
	$insertResult = json_decode($insertResponse, true);

	if (!$insertResult["success"]) {
		throw new Exception("Database insertion failed.");
	}

	$updateResponse = update_table("users", ["package_id" => $selectedPack], ["user_id" => $userId]);
	$updateResult = json_decode($updateResponse, true);

	if (!$updateResult["success"]) {
		throw new Exception("User update failed.");
	}

	log_activity(
		$userId,
		"subscription_upgrade",
		"User upgraded subscription to {$selectedPack} members. Estimated cost: $ {$estimatedCost}",
		"subscriptions",
		$userId
	);

	$response = [
		"success" => true,
		"message" => "Subscription upgraded successfully!",
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