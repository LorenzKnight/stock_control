<?php

use App\AiSales\AiSalesAgentClient;
use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesCompanyService;
use App\AiSales\SalesContactRepository;
use App\AiSales\SalesContactService;

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
				"php://input"
			),
			true
		);

	if (!is_array($input)) {
		throw new InvalidArgumentException(
			"Invalid JSON input."
		);
	}


	$salesCompanyId = (int)($input["sales_company_id"] ?? 0);

	if ($salesCompanyId <= 0) {
		throw new InvalidArgumentException(
			"Invalid sales company ID."
		);
	}


	$repository = new SalesCompanyRepository();

	$service = new SalesCompanyService($repository);

	$company = $repository->findById(
        $salesCompanyId
    );

	if ($company === null) {
		throw new RuntimeException(
			"Sales company not found."
		);
	}


	if (
		($company["research_status"] ?? null)
		!== "COMPLETED"
	) {
		throw new RuntimeException(
			"Company research must be completed before qualification."
		);
	}


	$contactSearch = null;

	$createdContacts = [];

	$service->markQualifying(
		$salesCompanyId
	);


	try {
		$client = new AiSalesAgentClient();

		$qualification = $client->qualifyCompany(
			$company
		);

		$service->completeQualification(
			$salesCompanyId,
			$qualification
		);

		$qualificationLevel = strtoupper(trim((string)($qualification["level"] ?? '')));

		if (
			in_array(
				$qualificationLevel,
				[
					"MEDIUM",
					"HIGH"
				],
				true
			)
		) {
			try {
				$contactRepository = new SalesContactRepository();

				$contactService = new SalesContactService(
					$contactRepository
				);

				$service->markContactSearching(
					$salesCompanyId
				);


				$contactSearch = $client->findCompanyContacts(
					$company
				);

				$contacts = $contactSearch["contacts"] ?? [];

				if (!is_array($contacts)) {
					throw new RuntimeException(
						"Invalid contact search response."
					);
				}

				$createdContacts = $contactService->saveFoundContacts(
					$salesCompanyId,
					$contacts,
					$userId
				);

				$service->completeContactSearch(
					$salesCompanyId,
					!empty(
						$contacts
					)
				);

			} catch (Throwable $contactError) {
				error_log(
					"AI Sales automatic contact search failed: " .
						$contactError->getMessage()
				);


				try {
					$service->markContactSearchFailed(
						$salesCompanyId
					);

				} catch (Throwable $statusError) {
					error_log(
						"Could not mark company contact search as failed: " .
							$statusError->getMessage()
					);
				}
			}
		} else {
			$service->markContactSearchSkipped(
				$salesCompanyId
			);
		}

	} catch (Throwable $e) {
		error_log(
			"AI Sales company qualification failed: " .
				$e->getMessage()
		);


		try {
			$service->markQualificationFailed(
				$salesCompanyId
			);

		} catch (Throwable $statusError) {
			error_log(
				"Could not mark company qualification as failed: " .
					$statusError->getMessage()
			);
		}

		throw $e;
	}

	$response = [
		"success" => true,
		"message" => "Company qualified successfully.",
		"data" =>
			[
				"sales_company_id" => $salesCompanyId,
				"qualification" => $qualification,
				"contact_search" => $contactSearch, 
				"contacts_created" => count($createdContacts)
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