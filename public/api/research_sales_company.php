<?php

use App\AiSales\AiSalesAgentClient;
use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesCompanyService;
use App\AiSales\SalesContactRepository;
use App\AiSales\SalesContactService;
use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesLeadService;
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesConversationService;
use App\AiSales\SalesMessageRepository;
use App\AiSales\SalesMessageService;

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
				'php://input'
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

	$service = new SalesCompanyService(
		$repository
	);


	$company = $repository->findById(
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
		$client = new AiSalesAgentClient();

		$research = $client->researchCompany(
			$company
		);


		$service->completeResearch(
			$salesCompanyId,
			$company,
			$research
		);

        $qualification = null;

		$contactSearch = null;

		$createdContacts = [];

		$lead = null;

		$conversation = null;

		$outreach = null;


        try {
            $researchedCompany = $repository->findById(
				$salesCompanyId
			);

            if ($researchedCompany === null) {
                throw new RuntimeException(
                    "Could not reload researched company."
                );
            }

            $service->markQualifying(
                $salesCompanyId
            );

            $qualification = $client->qualifyCompany(
				$researchedCompany
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
						$researchedCompany
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


					$service->completeContactSearch(
						$salesCompanyId,
						$contactsFound
					);

					if ($contactsFound) {
						try {
							$primaryContact = $contactService->getPrimaryContactByCompany(
								$salesCompanyId
							);

							if ($primaryContact === null) {
								throw new RuntimeException(
									"No primary contact was found for sales lead creation."
								);
							}

							$leadCompany = $repository->findById(
								$salesCompanyId
							);

							if ($leadCompany === null) {
								throw new RuntimeException(
									"Could not reload company for sales lead creation."
								);
							}


							$leadRepository = new SalesLeadRepository();

							$leadService = new SalesLeadService(
								$leadRepository
							);


							$lead = $leadService->createFromQualifiedCompany(
								$leadCompany,
								$primaryContact,
								$userId
							);

							$leadId = (int)($lead["sales_lead_id"] ?? 0);


							if ($leadId <= 0) {
								throw new RuntimeException(
									"Invalid sales lead ID after lead creation."
								);
							}


							$leadRecord = $leadRepository->findById(
								$leadId
							);


							if ($leadRecord === null) {
								throw new RuntimeException(
									"Could not reload sales lead for initial outreach."
								);
							}


							$conversationRepository = new SalesConversationRepository();

							$conversationService = new SalesConversationService(
								$conversationRepository
							);


							$conversation = $conversationService->getOrCreateInitialConversation(
								$leadRecord,
								$primaryContact
							);


							$conversationId = (int)($conversation["conversation_id"] ?? 0);

							if ($conversationId <= 0) {
								throw new RuntimeException(
									"Invalid sales conversation ID."
								);
							}


							$messageRepository = new SalesMessageRepository();

							$messageService = new SalesMessageService(
								$messageRepository,
								$conversationRepository,
								$leadRepository,
								$repository,
								$contactRepository,
								$client
							);


							$outreach = $messageService->generateFirstOutreach(
								$conversationId
							);

						} catch (Throwable $leadError) {
							error_log(
								"AI Sales automatic lead creation failed: " .
									$leadError->getMessage()
							);
						}
					}

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
				try {
					$service->markContactSearchSkipped(
						$salesCompanyId
					);
				} catch (Throwable $contactStatusError) {
					error_log(
						"Could not mark company contact search as skipped: " .
							$contactStatusError->getMessage()
					);
				}
			}

        } catch (Throwable $qualificationError) {
            error_log(
                "AI Sales company qualification failed: " .
                    $qualificationError->getMessage()
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
        }

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
		"success" => true,
		"message" => "Company researched successfully.",
		"data" =>
			[
				"sales_company_id" => $salesCompanyId,
				"research" => $research,
                "qualification" => $qualification,
				"contact_search" => $contactSearch,
				"contacts_created" => count($createdContacts),
				"lead" => $lead,
				"conversation" => $conversation,
				"outreach" => $outreach
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