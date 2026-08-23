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

	$companyName = trim($_POST['company_name'] ?? '');

	$orgNoRaw = trim($_POST['organization_no'] ?? '');
	$orgNo = ($orgNoRaw !== '' && is_numeric($orgNoRaw))
		? intval($orgNoRaw)
		: null;

	$address = trim($_POST['company_address'] ?? '');
	$phone = trim($_POST['company_phone'] ?? '');
	$countryCode = trim($_POST['company_country_code'] ?? '');

	if ($companyName === '') {
		throw new Exception("Company name is required.");
	}

	if ($countryCode === '') {
		throw new Exception("Company country code is required.");
	}

	if ($phone === '') {
		throw new Exception("Company phone is required.");
	}

	$isCompanyProfileCompleted =
		$companyName !== '' &&
		$phone !== '' &&
		$countryCode !== '' &&
		strcasecmp($companyName, 'My Company') !== 0;

	$showCompanyReward = false;

	$updateData = [
		"company_name" => $companyName,
		"organization_no" => $orgNo,
		"company_address" => $address,
		"country_code" => $countryCode,
		"company_phone" => $phone
	];

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
        $updateResult = json_decode(
			update_table(
				"companies",
				$updateData,
				[
					"user_id" => $altUser,
					"company_id" => $companyId
				]
			),
			true
		);

	    if (empty($updateResult["success"])) throw new Exception("Update failed.");

		/*
		* ✅ Onboarding: completar empresa
		*
		* Solo se completa cuando el usuario edita
		* su primera empresa.
		*/
		$firstCompanyData = json_decode(
			select_from(
				"companies",
				["company_id"],
				[
					"user_id" => $altUser
				],
				[
					"order_by" => "company_id",
					"order_direction" => "asc",
					"limit" => 1,
					"fetch_first" => true
				]
			),
			true
		);

		$firstCompanyId = intval(
			$firstCompanyData["data"]["company_id"] ?? 0
		);

		if (
			!empty($firstCompanyData["success"]) &&
			$firstCompanyId > 0 &&
			intval($companyId) === $firstCompanyId &&
			$isCompanyProfileCompleted
		) {
			$onboardingCheck = json_decode(select_from("user_onboarding",
					[
						"user_id",
						"company",
						"company_reward_seen"
					],
					[
						"user_id" => $altUser
					],
					[
						"fetch_first" => true
					]
				),
				true
			);

			if (
				!empty($onboardingCheck["success"]) &&
				!empty($onboardingCheck["data"])
			) {
				$companyAlreadyCompleted =
					$onboardingCheck["data"]["company"] === true ||
					$onboardingCheck["data"]["company"] === "t" ||
					$onboardingCheck["data"]["company"] === 1 ||
					$onboardingCheck["data"]["company"] === "1";

				$rewardAlreadySeen =
					$onboardingCheck["data"]["company_reward_seen"] === true ||
					$onboardingCheck["data"]["company_reward_seen"] === "t" ||
					$onboardingCheck["data"]["company_reward_seen"] === 1 ||
					$onboardingCheck["data"]["company_reward_seen"] === "1";

				$showCompanyReward =
					!$companyAlreadyCompleted &&
					!$rewardAlreadySeen;

				/*
				* Evitamos hacer UPDATE innecesariamente
				* si ya estaba completado.
				*/
				if (!$companyAlreadyCompleted) {
					$onboardingResult = json_decode(update_table("user_onboarding",
							[
								"company" => true,
								"updated_at" => date("Y-m-d H:i:s")
							],
							[
								"user_id" => $altUser
							]
						),
						true
					);

					if (empty($onboardingResult["success"])) {
						error_log("Could not update onboarding company step for user_id: " .$altUser);
					
						$showCompanyReward = false;
					}
				}

			} elseif (
				($onboardingCheck["message"] ?? "") === "No records found"
			) {
				$onboardingResult = json_decode(
					insert_into(
						"user_onboarding",
						[
							"user_id" => $altUser,
							"company" => true,
							"company_reward_seen" => false,
							"created_at" => date("Y-m-d H:i:s"),
							"updated_at" => date("Y-m-d H:i:s")
						]
					),
					true
				);

				$showCompanyReward = !empty($onboardingResult["success"]);

				if (!$showCompanyReward) {
					error_log("Could not create onboarding company step for user_id: " .$altUser);
				}
			} else {
				error_log(
					"Could not read onboarding company state for user_id " .
					$altUser .
					": " .
					($onboardingCheck["message"] ?? "Unknown error")
				);

				$showCompanyReward = false;
			}
		}

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

		$newCompanyId = intval($insertResult["id"] ?? 0);

		if ($newCompanyId <= 0) {
			throw new Exception("Invalid company ID returned after insert.");
		}
		
		$decodedUser = json_decode(
			select_from(
				"users",
				["company_id"],
				["user_id" => $userId],
				["fetch_first" => true]
			),
			true
		);

		$currentUserCompanyId =
			intval($decodedUser["data"]["company_id"] ?? 0);

		if ($currentUserCompanyId <= 0) {
			$updateResult = json_decode(
				update_table(
					"users",
					["company_id" => $newCompanyId],
					["user_id" => $userId]
				),
				true
			);

			if (empty($updateResult["success"])) {
				throw new Exception("Update failed.");
			}
		}

		$companyId = $newCompanyId;
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
		$companyId
	);

	$response = [
		"success" => true,
		"message" => "Company info {$action} successfully!",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => "",
		"action" => $action,
		"show_reward_modal" => $showCompanyReward,
		"reward_type" => $showCompanyReward
			? "first_company"
			: null,
		"company_id" => $companyId,
		"company_name" => $companyName
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