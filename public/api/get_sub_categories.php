<?php
use App\Categories\CategoryRepository;
use App\Categories\CategoryService;

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

	$userData = select_from(
		"users",
		["parent_user"],
		["user_id" => $userId],
		[
			"fetch_first" => true,
			"return_type" => "array"
		]
	);

	if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }

	$userInfo = $userData["data"];

	$altUser = empty($userInfo["parent_user"] ?? null) 
		? $userId
		: $userInfo["parent_user"];

	$company = trim($_GET["company"] ?? '');

	$companyId = $company !== ''
		? (int)$company
		: null;

	$parentCategoryId = isset($_GET["mark_id"])
		? intval($_GET["mark_id"])
		: null;

	if (!$parentCategoryId || !is_numeric($parentCategoryId)) {
		throw new Exception("Invalid mark ID.");
	}

	$repository = new CategoryRepository();
	$service = new CategoryService($repository);

	$categories = $service->getSubCategories(
		(int)$altUser,
		(int)$parentCategoryId,
		$companyId
	);

	$response = [
		"success" => true,
		"message" => "Subcategories loaded successfully.",
		"data" => $categories
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;