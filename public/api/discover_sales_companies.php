<?php

use App\AiSales\AiSalesAgentClient;

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


	$authUser = requireAuth();
	$userId = (int)($authUser["user_id"] ?? 0);

	if ($userId <= 0) {
		throw new Exception(
			"Unauthorized access."
		);
	}


	$hasRootAccess = check_user_permission(
		$userId,
		"root_access"
	);

	$hasSystemAdminAccess = check_user_permission(
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


	$searches = $input["searches"] ?? null;

	if (
		!is_array($searches) ||
		empty($searches)
	) {
		throw new InvalidArgumentException(
			"At least one company search is required."
		);
	}


	$suggestions = $input["suggestions"] ?? [];

	if (!is_array($suggestions)) {
		throw new InvalidArgumentException(
			"Company suggestions must be an array."
		);
	}

	$aiSalesAgentClient = new AiSalesAgentClient();

	$companies =
		$aiSalesAgentClient->discoverCompanies(
			[
				"searches" =>
					$searches,

				"suggestions" =>
					$suggestions
			]
		);


	try {
		$companyCount = count($companies);

		log_activity(
			$userId,
			"discover_sales_companies",
			"Discovered {$companyCount} AI Sales company candidates.",
			"sales_companies",
			0
		);

	} catch (Throwable $e) {
		error_log(
			"Could not log AI Sales company discovery: " .
			$e->getMessage()
		);
	}


	$response = [
		"success" => true,
		"message" => "Companies discovered successfully.",
		"data" => [
			"companies" => $companies
		]
	];

} catch (Throwable $e) {
	$response["message"] = $e->getMessage();
}


echo json_encode(
	$response,
	JSON_UNESCAPED_UNICODE |
	JSON_UNESCAPED_SLASHES
);

exit;