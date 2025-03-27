<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No subcategories found",
	"data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	$userInfoResponse = select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
	$userInfo = json_decode($userInfoResponse, true);

	if (!$userInfo["success"] || empty($userInfo["data"]["company_id"])) {
		throw new Exception("No company found for user.");
	}

	$companyId = intval($userInfo["data"]["company_id"]);

	$modelId = isset($_GET["model_id"]) ? intval($_GET["model_id"]) : null;
	if (!$modelId || !is_numeric($modelId)) {
		throw new Exception("Invalid model ID.");
	}

	$categoriesResponse = select_from("category", [
		"category_id",
		"category_name"
	], [
		"sub_parent" => $modelId,
		"company_id" => $companyId
	], [
		"order_by" => "category_name"
	]);

	$categories = json_decode($categoriesResponse, true);

	if (!$categories["success"] || empty($categories["data"])) {
		throw new Exception("No subcategories available for this mark.");
	}

	$response = [
		"success" => true,
		"message" => "Subcategories loaded successfully.",
		"data" => $categories["data"]
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>