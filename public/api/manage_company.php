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

	if (!check_user_permission($userId, 'platform_admin')) {
		throw new Exception("Access denied. You do not have permission to update company info.");
	}

	$userData = json_decode(select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]), true);
	if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }
	$userInfo = $userData["data"];

	$altUser = empty($userInfo["parent_user"] ?? null) ? $userId : intval($userInfo["parent_user"]);

	$companyIdRaw = $_POST['company_id'] ?? null;
	$companyId = (is_numeric($companyIdRaw) && intval($companyIdRaw) > 0)
		? intval($companyIdRaw)
		: null;

	if ($companyId && !is_numeric($companyId)) {
		throw new Exception("Invalid company ID.");
	}

	$companyName = trim($_POST['company_name'] ?? '');
	$orgNo = trim($_POST['organization_no'] ?? '');
	$address = trim($_POST['company_address'] ?? '');
	$phone = trim($_POST['company_phone'] ?? '');
	$countryCode = trim($_POST['company_country_code'] ?? '');

	if ($companyName === '' || $orgNo === '' || $address === '' || $phone === '') {
		throw new Exception("All fields are required.");
	}

	$updateData = [
		"company_name" => $companyName,
		"organization_no" => $orgNo,
		"company_address" => $address,
		"country_code" => $countryCode,
		"company_phone" => $phone
	];

	// $previousData = json_decode(select_from(
	// 	"companies",
	// 	["company_logo"],
	// 	["company_id" => $companyId],
    //     ["fetch_first" => true]
	// ),true);

	$previousImage = null;

	if (!empty($companyId)) {
		$previousData = json_decode(select_from(
			"companies",
			["company_logo"],
			["company_id" => $companyId],
			["fetch_first" => true]
		), true);

		if (!empty($previousData["success"]) && !empty($previousData["data"]["company_logo"])) {
			$tmp = trim((string)$previousData["data"]["company_logo"]);
			$previousImage = $tmp !== '' ? $tmp : null;
		}
	}

	try {
		$imageName = handle_uploaded_image(
			"company_logo",
			__DIR__ . "/../images/company-logos",
			"logo",
			$userId,
			["png", "jpg", "jpeg", "webp"],
			$previousImage
		);
		
		if ($imageName) {
			$updateData["company_logo"] = $imageName;
		}
	} catch (Exception $imgEx) {
		throw new Exception("Logo upload failed: " . $imgEx->getMessage());
	}

    if (!empty($companyId) && is_numeric($companyId)) {
        $updateResponse = update_table("companies", $updateData, ["user_id" => $altUser, "company_id" => $companyId]);
        $updateResult = json_decode($updateResponse, true);

	    if (empty($updateResult["success"])) throw new Exception("Update failed.");

        $action = "updated";
    } else {
		$ownerPackageData = json_decode(
			select_from(
				"users",
				["package_id"],
				["user_id" => $altUser],
				["fetch_first" => true]
			),
			true
		);

		$packageId = intval($ownerPackageData["data"]["package_id"] ?? 0);

		if ($packageId <= 0) {
			throw new Exception("No package assigned to this account.");
		}

		$packageInfo = json_decode(
			select_from(
				"packages",
				["branch_affiliate_limit"],
				["package_id" => $packageId],
				["fetch_first" => true]
			),
			true
		);

		$allowedAffiliates = intval($packageInfo["data"]["branch_affiliate_limit"] ?? 0);

		if ($allowedAffiliates <= 0) {
			throw new Exception("Your package does not allow affiliates.");
		}

		$companiesCountRes = json_decode(
			select_from(
				"companies",
				["company_id"],
				["user_id" => $altUser]
			),
			true
		);

		$currentAffiliatesCount = 0;

		if (!empty($companiesCountRes["success"]) && !empty($companiesCountRes["data"]) && is_array($companiesCountRes["data"])) {
			$currentAffiliatesCount = count($companiesCountRes["data"]);
		}

		if ($currentAffiliatesCount >= $allowedAffiliates) {
			throw new Exception("Maximum allowed affiliates reached. Upgrade your pack.");
		}

		$updateData["user_id"] = $altUser;
		$insertResult = json_decode(insert_into("companies", $updateData, ["id" => "company_id"]), true);

		if (empty($insertResult["success"])) throw new Exception("Insert failed.");

		$decodedUser = json_decode(select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]), true);

		$companyId = $decodedUser["data"]["company_id"] ?? null;

		if (empty($companyId)) {
			$updateResponse = update_table("users", ["company_id" => $insertResult["id"]], ["user_id" => $userId]);
			$updateResult = json_decode($updateResponse, true);

			if (empty($updateResult["success"])) throw new Exception("Update failed.");
		}

		// ✅ Validar si este es el primer producto creado
		$companiesCountRes = json_decode(select_from("companies", ["COUNT(*) AS total"], [
			"user_id" => $altUser
		], ["fetch_first" => true]), true);

		$totalCompanies = intval($companiesCountRes["data"]["total"] ?? 0);

		if ($companiesCountRes["success"] && $totalCompanies === 1) {
			$onboardingCheck = json_decode(select_from("user_onboarding", ["user_id"], [
				"user_id" => $userId
			], ["fetch_first" => true]), true);

			if ($onboardingCheck["success"] && !empty($onboardingCheck["data"])) {
				// ✅ Existe: actualizar
				$onboardingResult = json_decode(update_table("user_onboarding", [
					"company" => true,
					"updated_at" => date("Y-m-d H:i:s")
				], [
					"user_id" => $userId
				]), true);
			} else {
				// ✅ No existe: insertar nuevo registro
				$onboardingResult = json_decode(insert_into("user_onboarding", [
					"user_id" => $userId,
					"company" => true,
					"created_at" => date("Y-m-d H:i:s")
				]), true);
			}

			if (!$onboardingResult["success"]) {
				error_log("Could not update onboarding company step for user_id: " . $userId);
			}
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