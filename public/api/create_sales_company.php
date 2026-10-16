<?php

use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesCompanyService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request."
];


try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception(
			"Method not allowed."
		);
	}


	$authUser =
		requireAuth();

	$userId =
		(int)(
			$authUser["user_id"] ?? 0
		);

	if ($userId <= 0) {
		throw new Exception(
			"Unauthorized access."
		);
	}


	$hasRootAccess =
		check_user_permission(
			$userId,
			"root_access"
		);

	$hasSystemAdminAccess =
		check_user_permission(
			$userId,
			"system_admin"
		);


	if (
		!$hasRootAccess &&
		!$hasSystemAdminAccess
	) {
		throw new Exception(
			"Permission denied."
		);
	}


	$input =
		json_decode(
			file_get_contents(
				'php://input'
			),
			true
		);

	if (!is_array($input)) {
		throw new InvalidArgumentException(
			"Invalid JSON input."
		);
	}


	$company =
		$input["company"] ?? null;

	if (!is_array($company)) {
		throw new InvalidArgumentException(
			"Invalid company data."
		);
	}


	$market =
		isset($input["market"])
			? (string)$input["market"]
			: null;

	$language =
		isset($input["language"])
			? (string)$input["language"]
			: null;


	$repository =
		new SalesCompanyRepository();

	$service =
		new SalesCompanyService(
			$repository
		);


	$result =
		$service->createProspect(
			$company,
			$userId,
			$market,
			$language
		);


	if (!empty($result["created"])) {
		try {
			$salesCompanyId =
				(int)$result[
					"sales_company_id"
				];

			log_activity(
				$userId,
				"create_sales_company",
				"Added AI Sales prospect: " .
					$result["company_name"],
				"sales_companies",
				$salesCompanyId
			);

		} catch (Throwable $e) {
			error_log(
				"Could not log AI Sales company creation: " .
				$e->getMessage()
			);
		}
	}


	$response = [
		"success" =>
			true,

		"message" =>
			!empty($result["created"])
				? "Prospect added successfully."
				: "Prospect already exists.",

		"data" =>
			$result
	];

} catch (Throwable $e) {
	$response["message"] =
		$e->getMessage();
}


echo json_encode(
	$response,
	JSON_UNESCAPED_UNICODE |
	JSON_UNESCAPED_SLASHES
);

exit;