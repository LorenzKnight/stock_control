<?php

use App\AiSales\AiSalesAgentClient;
use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesCompanyService;
use App\AiSales\SalesContactRepository;
use App\AiSales\SalesContactService;
use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesLeadService;

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


	$salesCompanyId =
		(int)(
			$input["sales_company_id"]
				?? 0
		);


	if ($salesCompanyId <= 0) {
		throw new InvalidArgumentException(
			"Invalid sales company ID."
		);
	}


	$companyRepository = new SalesCompanyRepository();

	$companyService = new SalesCompanyService(
        $companyRepository
    );

	$contactRepository = new SalesContactRepository();

	$contactService = new SalesContactService(
        $contactRepository
    );


	$company = $companyRepository->findById(
        $salesCompanyId
    );


	if ($company === null) {
		throw new RuntimeException(
			"Sales company not found."
		);
	}


	if (
		($company["qualification_status"] ?? null)
		!== "COMPLETED"
	) {
		throw new RuntimeException(
			"Company qualification must be completed before contact search."
		);
	}


	if (
		($company["qualification_level"] ?? null)
		=== "LOW"
	) {
		throw new RuntimeException(
			"Low-qualified companies are not eligible for automatic contact search."
		);
	}


	$companyService->markContactSearching(
		$salesCompanyId
	);

    $lead = null;

	try {
		$client = new AiSalesAgentClient();


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


		$contactsFound =
			!empty(
				$contacts
			);


		$companyService->completeContactSearch(
			$salesCompanyId,
			$contactsFound
		);

        if ($contactsFound) {
            try {
                $primaryContact =
                    $contactService->getPrimaryContactByCompany(
                        $salesCompanyId
                    );


                if ($primaryContact === null) {
                    throw new RuntimeException(
                        "No primary contact was found for sales lead creation."
                    );
                }


                $leadCompany =
                    $companyRepository->findById(
                        $salesCompanyId
                    );


                if ($leadCompany === null) {
                    throw new RuntimeException(
                        "Could not reload company for sales lead creation."
                    );
                }


                $leadRepository =
                    new SalesLeadRepository();

                $leadService =
                    new SalesLeadService(
                        $leadRepository
                    );


                $lead =
                    $leadService->createFromQualifiedCompany(
                        $leadCompany,
                        $primaryContact,
                        $userId
                    );

            } catch (Throwable $leadError) {
                error_log(
                    "AI Sales automatic lead creation failed: " .
                        $leadError->getMessage()
                );
            }
        }

	} catch (Throwable $e) {
		error_log(
			"AI Sales company contact search failed: " .
				$e->getMessage()
		);


		try {
			$companyService->markContactSearchFailed(
				$salesCompanyId
			);

		} catch (Throwable $statusError) {
			error_log(
				"Could not mark company contact search as failed: " .
					$statusError->getMessage()
			);
		}


		throw $e;
	}


	try {
		log_activity(
			$userId,
			"find_sales_contacts",
			"AI searched contacts for sales company: " .
				(
					$company["company_name"]
						?? ''
				),
			"sales_companies",
			$salesCompanyId
		);

	} catch (Throwable $e) {
		error_log(
			"Could not log AI Sales contact search: " .
				$e->getMessage()
		);
	}


	$response = [
		"success" => true,
		"message" =>
			$contactsFound
				? "Company contacts found successfully."
				: "No verified company contacts were found.",
		"data" =>
			[
				"sales_company_id" => $salesCompanyId,
				"contacts_found" => count($contacts),
				"contacts_created" => count($createdContacts),
				"contacts" => $contacts,
				"model" => $contactSearch["model"] ?? null,
                "lead" => $lead
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