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

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'create_data')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$companyId		= intval($_POST["company_id"]);
	$categoryName	= trim($_POST["category_name"] ?? '');
	$catParentSub	= trim($_POST["cat_parent_sub"] ?? '');
	$subParent		= trim($_POST["sub_parent"] ?? '');

	if ($categoryName === '') {
		throw new Exception("Category name is required.");
	}

	$data = [
		"category_name" => $categoryName,
		"company_id"    => $companyId,
		"create_by"     => $userId,
		"created_at"    => date("Y-m-d H:i:s")
	];

	if ($catParentSub !== '' && $subParent == '') {
		$data["cat_parent_sub"] = intval($catParentSub);
	}

	if ($subParent !== '') {
		$data["sub_parent"] = intval($subParent);
	}

	$insertResponse = insert_into("category", $data, ["id" => "category_id"]);
	$insertResult = json_decode($insertResponse, true);

	if (!$insertResult["success"]) {
		throw new Exception("Failed to insert category.");
	}

	log_activity($userId, "create_category", "Created new category: $categoryName", "category", $insertResult["id"]);

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
?>