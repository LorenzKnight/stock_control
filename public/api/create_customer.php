<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success"		=> false,
	"message"		=> "Invalid request",
	"img_gif"		=> "../images/sys-img/error.gif",
	"redirect_url"	=> ""
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$authUser = requireAuth();
    $userId = $authUser["user_id"];
	$companyId = $authUser["company_id"] ?? null;

	if (empty($userId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

	if (!check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$name		= trim($_POST["customer_name"] ?? '');
	$surname	= trim($_POST["customer_surname"] ?? '');
	$email		= trim($_POST["customer_email"] ?? '');
	$address	= trim($_POST["customer_address"] ?? '');
	$cuCountry	= trim($_POST["customer_country_code"] ?? '');
	$phone		= trim($_POST["customer_phone"] ?? '');
	$birthday   = trim($_POST["customer_birthday"] ?? '');
	$docType    = intval($_POST["customer_document_type"] ?? 0);
	$docNo      = trim($_POST["customer_document_no"] ?? '');
	$type       = intval($_POST["customer_type"] ?? 0);
	$status     = intval($_POST["customer_status"] ?? 0);
	$ref1       = trim($_POST["references_1"] ?? '');
	$r1Country  = trim($_POST["references_1_country_code"] ?? '');
	$ref1Phone  = trim($_POST["references_1_phone"] ?? '');
	$ref2       = trim($_POST["references_2"] ?? '');
	$r2Country  = trim($_POST["references_2_country_code"] ?? '');
	$ref2Phone  = trim($_POST["references_2_phone"] ?? '');

	$showClientReward = false;

	if ($name === '') {
		throw new Exception("Customer name is required.");
	}

	if ($birthday === '') {
		throw new Exception("Customer birthday is required.");
	}

	$imageName = null;
	try {
		$imageName = handle_uploaded_image(
			"customer_image",
			__DIR__ . "/../images/customers/",
			"customer",
			$userId,
			["jpg", "jpeg", "png", "webp"]
		);
	} catch (Exception $ex) {
		throw new Exception("Image upload failed: " . $ex->getMessage());
	}

	$insertData = [
		"customer_name"				=> $name,
		"customer_surname"			=> $surname,
		"customer_email"			=> $email,
		"customer_address"			=> $address,
		"cu_country_code"			=> $cuCountry,
		"customer_phone"			=> $phone,
		"customer_birthday"			=> $birthday,
		"customer_document_type"	=> $docType,
		"customer_document_no"		=> $docNo,
		"customer_type"				=> $type,
		"customer_status"			=> $status,
		"references_1"				=> $ref1,
		"r1_country_code"			=> $r1Country,
		"references_1_phone"		=> $ref1Phone,
		"references_2"				=> $ref2,
		"r2_country_code"			=> $r2Country,
		"references_2_phone"		=> $ref2Phone,
		"company_id"				=> $companyId,
		"create_by"					=> $userId,
		"created_at"				=> date("Y-m-d H:i:s")
	];

	if ($imageName) {
		$insertData["customer_image"] = $imageName;
	}
	
	$insertResult = json_decode(insert_into("customers", $insertData, ["id" => "customer_id"]), true);

	if (!$insertResult["success"]) {
		throw new Exception("Error saving customer data.");
	}

	// ✅ Onboarding: primer cliente
	$onboardingCheck = json_decode(
		select_from(
			"user_onboarding",
			[
				"user_id",
				"client",
				"client_reward_seen"
			],
			[
				"user_id" => $userId
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
		$clientCompleted =
			$onboardingCheck["data"]["client"] === true ||
			$onboardingCheck["data"]["client"] === "t" ||
			$onboardingCheck["data"]["client"] === 1 ||
			$onboardingCheck["data"]["client"] === "1";

		$rewardAlreadySeen =
			$onboardingCheck["data"]["client_reward_seen"] === true ||
			$onboardingCheck["data"]["client_reward_seen"] === "t" ||
			$onboardingCheck["data"]["client_reward_seen"] === 1 ||
			$onboardingCheck["data"]["client_reward_seen"] === "1";

		/*
		* Solo mostramos el reward cuando este paso todavía
		* no estaba completado y la recompensa tampoco fue vista.
		*/
		$showClientReward =
			!$clientCompleted &&
			!$rewardAlreadySeen;

		if (!$clientCompleted) {
			$onboardingResult = json_decode(
				update_table(
					"user_onboarding",
					[
						"client" => true,
						"updated_at" => date("Y-m-d H:i:s")
					],
					[
						"user_id" => $userId
					]
				),
				true
			);

			if (empty($onboardingResult["success"])) {
				error_log(
					"Could not update onboarding customer step for user_id: " .
					$userId
				);

				$showClientReward = false;
			}
		}

	} elseif (
		($onboardingCheck["message"] ?? "") === "No records found"
	) {
		$onboardingResult = json_decode(
			insert_into(
				"user_onboarding",
				[
					"user_id" => $userId,
					"client" => true,
					"client_reward_seen" => false,
					"created_at" => date("Y-m-d H:i:s"),
					"updated_at" => date("Y-m-d H:i:s")
				]
			),
			true
		);

		$showClientReward =
			!empty($onboardingResult["success"]);

		if (!$showClientReward) {
			error_log(
				"Could not create onboarding customer step for user_id: " .
				$userId
			);
		}

	} else {
		$showClientReward = false;

		error_log(
			"Could not read onboarding customer state for user_id: " .
			$userId
		);
	}

	log_activity(
		$userId,
		"create_customer",
		"User added a new customer: {$name} {$surname}",
		"customers",
		$insertResult["id"] ?? null
	);

	$response = [
		"success"		=> true,
		"message"		=> "Customer created successfully!",
		"img_gif"		=> "../images/sys-img/loading1.gif",
		"redirect_url"	=> "",
		"show_reward_modal" => $showClientReward,
		"reward_type" => $showClientReward ? "first_client" : null,
		"customer_id" => $insertResult["id"] ?? null,
		"customer_name" => trim($name . " " . $surname)
	];

} catch (Exception $e) {
	$response = [
		"success"		=> false,
		"message"		=> $e->getMessage(),
		"img_gif"		=> "../images/sys-img/error.gif",
		"redirect_url"	=> ""
	];
}

echo json_encode($response);
exit;