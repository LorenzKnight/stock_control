<?php
use App\Categories\CategoryRepository;
use App\Categories\CategoryService;

require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No categories found",
	"data" => []
];

try {
	$userId = $_SESSION["sc_UserId"] ?? null;

	if (!$userId) throw new Exception("User session not found.");

	$userData = select_from(
		"users",
		["parent_user", "company_id"],
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

	$altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];
	$companyId = $userInfo["company_id"];

	$company = $_GET["company"] ?? '';
	
	$effectiveCompanyId = !empty($company)
		? (int)$company
		: (int)$companyId;

	$repository = new CategoryRepository();
	$service = new CategoryService($repository);

	$categories = $service->getRootCategories(
		(int)$altUser,
		$effectiveCompanyId
	);

	$response = [
		"success"	=> true,
		"message"	=> "Categories loaded successfully.",
		"data"		=> $categories
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;