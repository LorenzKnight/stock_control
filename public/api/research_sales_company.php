<?php

use App\AiSales\AiSalesAgentClient;
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


	$salesCompanyId =
		(int)(
			$input["sales_company_id"] ?? 0
		);

	if ($salesCompanyId <= 0) {
		throw new InvalidArgumentException(
			"Invalid sales company ID."
		);
	}


	$repository =
		new SalesCompanyRepository();

	$service =
		new SalesCompanyService(
			$repository
		);


	$company =
		$repository->findById(
			$salesCompanyId
		);

	if ($company === null) {
		throw new RuntimeException(
			"Sales company not found."
		);
	}


	$service->markResearching(
		$salesCompanyId
	);


	try {
		$client =
			new AiSalesAgentClient();


		$research =
			$client->researchCompany(
				$company
			);


		$service->completeResearch(
			$salesCompanyId,
			$company,
			$research
		);

	} catch (Throwable $e) {
        error_log(
            "AI Sales company research failed: " .
            $e->getMessage()
        );

		try {
			$service->markResearchFailed(
				$salesCompanyId
			);

		} catch (Throwable $statusError) {
			error_log(
				"Could not mark company research as failed: " .
				$statusError->getMessage()
			);
		}

		throw $e;
	}


	try {
		log_activity(
			$userId,
			"research_sales_company",
			"AI researched sales company: " .
				($company["company_name"] ?? ''),
			"sales_companies",
			$salesCompanyId
		);

	} catch (Throwable $e) {
		error_log(
			"Could not log AI Sales company research: " .
				$e->getMessage()
		);
	}


	$response = [
		"success" =>
			true,

		"message" =>
			"Company researched successfully.",

		"data" =>
			[
				"sales_company_id" =>
					$salesCompanyId,

				"research" =>
					$research
			]
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