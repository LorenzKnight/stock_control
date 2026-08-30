<?php
use App\Categories\CategoryRepository;
use App\Categories\CategoryService;

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

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

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

	$companyId		= (int)($_POST["company_id"] ?? 0);
	$categoryName	= trim($_POST["category_name"] ?? '');
	$catParentSub	= trim($_POST["cat_parent_sub"] ?? '');
	$subParent		= trim($_POST["sub_parent"] ?? '');
	
	$catParentSub = $catParentSub !== ''
		? (int)$catParentSub
		: null;

	$subParent = $subParent !== ''
		? (int)$subParent
		: null;

	$repository = new CategoryRepository();
	$service = new CategoryService($repository);

	$categoryId = $service->createCategory(
		(int)$userId,
		(int)$altUser,
		$companyId,
		$categoryName,
		$catParentSub,
		$subParent
	);

	log_activity(
		$userId,
		"create_category",
		"Created new category: $categoryName",
		"category",
		$categoryId
	);

	$response = [
		"success" => true,
		"message" => "Category created successfully.",
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