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

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	if (!check_user_permission($userId, 'export_reports')) {
		throw new Exception("Access denied. You do not have permission to update company info.");
	}

	$companyId = $_POST['company_id'] ?? null;
	if ($companyId && !is_numeric($companyId)) {
		throw new Exception("Invalid company ID.");
	}

	$companyName = trim($_POST['company_name'] ?? '');
	$orgNo = trim($_POST['organization_no'] ?? '');
	$address = trim($_POST['company_address'] ?? '');
	$phone = trim($_POST['company_phone'] ?? '');

	if ($companyName === '' || $orgNo === '' || $address === '' || $phone === '') {
		throw new Exception("All fields are required.");
	}

	$updateData = [
		"company_name" => $companyName,
		"organization_no" => $orgNo,
		"company_address" => $address,
		"company_phone" => $phone
	];

	$imageName = null;
	try {
		$imageName = handle_uploaded_image(
			"company_logo",
			__DIR__ . "/../images/company-logos",
			["png", "jpg", "jpeg", "webp"],
			"logo",
			$userId
		);
		if ($imageName) {
			$updateData["company_logo"] = $imageName;
		}
	} catch (Exception $imgEx) {
		throw new Exception("Logo upload failed: " . $imgEx->getMessage());
	}

    if (!empty($companyId) && is_numeric($companyId)) {
        $updateResponse = update_table("companies", $updateData, ["user_id" => $userId, "company_id" => $companyId]);
        $updateResult = json_decode($updateResponse, true);

	    if (!$updateResult["success"]) throw new Exception("Update failed.");

        $action = "updated";
    } else {
		$updateData["user_id"] = $userId;
		$insertResponse = insert_into("companies", $updateData, ["id" => "company_id"]);
		$insertResult = json_decode($insertResponse, true);

		if (!$insertResult["success"]) throw new Exception("Insert failed.");

		$userInfo = select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
		$decodedUser = json_decode($userInfo, true);

		$companyId = $decodedUser["data"]["company_id"] ?? null;

		if (empty($companyId)) {
			$updateResponse = update_table("users", ["company_id" => $insertResult["id"]], ["user_id" => $userId]);
			$updateResult = json_decode($updateResponse, true);

			if (!$updateResult["success"]) throw new Exception("Update failed.");
		}
	
		$action = "created";
    }

	$description = "User {$action} company information.";
	if (!empty($updateData["company_logo"])) {
		$description .= " Logo updated.";
	}

	log_activity(
		$userId,
		"update_company_info",
		$description,
		"companies",
		$userId
	);

	$response = [
		"success" => true,
		"message" => "Company info {$action} successfully!",
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