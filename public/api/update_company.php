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

	if (!empty($_FILES["company_logo"]["name"])) {
		$uploadDir = "../images/company-logos/";
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		$ext = pathinfo($_FILES["company_logo"]["name"], PATHINFO_EXTENSION);
		$allowed = ["png", "jpg", "jpeg", "webp"];

		if (!in_array(strtolower($ext), $allowed)) {
			throw new Exception("Invalid file type for logo.");
		}

		$newName = "logo_user_" . $userId . "_" . time() . "." . $ext;
		$targetFile = $uploadDir . $newName;

		if (!move_uploaded_file($_FILES["company_logo"]["tmp_name"], $targetFile)) {
			throw new Exception("Failed to upload company logo.");
		}

		$updateData["company_logo"] = $newName;
	}

    $checkCompany = select_from("companies", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
    $checkResult = json_decode($checkCompany, true);

    if ($checkResult["success"] && !empty($checkResult["data"])) {
        $updateResponse = update_table("companies", $updateData, ["user_id" => $userId], ["echo_query" => true]);
        $updateResult = json_decode($updateResponse, true);

	    if (!$updateResult["success"]) throw new Exception("Update failed.");

        $action = "updated";
    } else {
        $updateData["user_id"] = $userId;
        $insertResponse = insert_into("companies", $updateData, ["id" => "company_id"]);
        $insertResult = json_decode($insertResponse, true);
    
        if (!$insertResult["success"]) throw new Exception("Insert failed.");
    
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
		"message" => "Company info updated successfully!",
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