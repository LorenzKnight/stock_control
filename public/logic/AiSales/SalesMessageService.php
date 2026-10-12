<?php

namespace App\AiSales;

class SalesMessageService
{
	private SalesMessageRepository $repository;

	private SalesConversationRepository $conversationRepository;

	private SalesLeadRepository $leadRepository;

	private ?SalesCompanyRepository $companyRepository;

	private ?SalesContactRepository $contactRepository;

	private ?AiSalesAgentClient $aiSalesAgentClient;

	public function __construct(
		SalesMessageRepository $repository,
		SalesConversationRepository $conversationRepository,
		SalesLeadRepository $leadRepository,
		?SalesCompanyRepository $companyRepository = null,
		?SalesContactRepository $contactRepository = null,
		?AiSalesAgentClient $aiSalesAgentClient = null
	) {
		$this->repository =
			$repository;

		$this->conversationRepository =
			$conversationRepository;

		$this->leadRepository =
			$leadRepository;

		$this->companyRepository =
			$companyRepository;

		$this->contactRepository =
			$contactRepository;

		$this->aiSalesAgentClient =
			$aiSalesAgentClient;
	}

	public function getMessagesByConversation(
		int $conversationId
	): array {
		if ($conversationId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid conversation ID."
			);
		}

		$result = $this->repository->findByConversationId(
			$conversationId
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return [];
		}

		$messages = array_values(
			$result["data"]
		);

		foreach ($messages as &$message) {

			$message["sales_message_id"] =
				(int)($message["sales_message_id"] ?? 0);

			$message["conversation_id"] =
				(int)($message["conversation_id"] ?? 0);


			$approvedByUserId =
				$message["approved_by_user_id"] ?? null;

			$message["approved_by_user_id"] =
				$approvedByUserId !== null
					? (int)$approvedByUserId
					: null;


			$message["direction"] =
				(string)(
					$message["direction"] ?? ''
				);

			$message["sender_type"] =
				(string)(
					$message["sender_type"] ?? ''
				);

			$message["status"] =
				(string)(
					$message["status"] ?? "DRAFT"
				);


			$message["ai_generated"] =
				$this->isDatabaseTrue(
					$message["ai_generated"] ?? false
				);

			$message["approved"] =
				$this->isDatabaseTrue(
					$message["approved"] ?? false
				);
		}

		unset($message);

		return $messages;
	}


	public function sendMessage(
		int $salesMessageId
	): array {
		if ($salesMessageId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales message ID."
			);
		}

		$message =
			$this->repository->findById(
				$salesMessageId
			);

		if ($message === null) {
			throw new \Exception(
				"Sales message not found."
			);
		}

		$status =
			(string)($message["status"] ?? '');

		if ($status !== "APPROVED") {
			throw new \Exception(
				"Only approved messages can be sent."
			);
		}

		$direction =
			(string)($message["direction"] ?? '');

		if ($direction !== "OUTBOUND") {
			throw new \Exception(
				"Only outbound messages can be sent."
			);
		}

		$approved =
			$this->isDatabaseTrue(
				$message["approved"] ?? false
			);

		if (!$approved) {
			throw new \Exception(
				"Message must be approved before sending."
			);
		}

		$conversationId =
			(int)($message["conversation_id"] ?? 0);

		if ($conversationId <= 0) {
			throw new \Exception(
				"Conversation not found for sales message."
			);
		}

		$conversation =
			$this->conversationRepository->findById(
				$conversationId
			);

		if ($conversation === null) {
			throw new \Exception(
				"Sales conversation not found."
			);
		}

		$salesLeadId =
			(int)($conversation["sales_lead_id"] ?? 0);

		if ($salesLeadId <= 0) {
			throw new \Exception(
				"Sales lead not found for conversation."
			);
		}

		$lead =
			$this->leadRepository->findById(
				$salesLeadId
			);

		if ($lead === null) {
			throw new \Exception(
				"Sales lead not found."
			);
		}

		$currentStage =
			(string)($lead["stage"] ?? "NEW");


		$newStage = null;

		if (
			in_array(
				$currentStage,
				[
					"NEW",
					"RESEARCHING"
				],
				true
			)
		) {
			$newStage = "CONTACTED";
		}

		$now =
			date("Y-m-d H:i:s");

		$this->repository->markAsSent(
			$salesMessageId,
			$now
		);

		$this->conversationRepository
			->markWaitingReply(
				$conversationId,
				$now
			);


		$this->leadRepository
			->updateAfterContact(
				$salesLeadId,
				$now,
				$newStage
			);

		return [
			"sales_message_id" =>
				$salesMessageId,

			"conversation_id" =>
				$conversationId,

			"sales_lead_id" =>
				$salesLeadId,

			"previous_stage" =>
				$currentStage,

			"current_stage" =>
				$newStage ?? $currentStage,

			"sent_at" =>
				$now
		];
	}


	public function receiveMessage(
		int $conversationId,
		string $message,
		?string $subject = null,
		?string $providerMessageId = null
	): array {
		if ($conversationId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid conversation ID."
			);
		}

		$message =
			trim($message);

		if ($message === '') {
			throw new \InvalidArgumentException(
				"Message cannot be empty."
			);
		}


		$conversation =
			$this->conversationRepository->findById(
				$conversationId
			);

		if ($conversation === null) {
			throw new \Exception(
				"Sales conversation not found."
			);
		}


		$salesLeadId =
			(int)(
				$conversation["sales_lead_id"]
				?? 0
			);

		if ($salesLeadId <= 0) {
			throw new \Exception(
				"Sales lead not found for conversation."
			);
		}


		$lead =
			$this->leadRepository->findById(
				$salesLeadId
			);

		if ($lead === null) {
			throw new \Exception(
				"Sales lead not found."
			);
		}


		$currentStage =
			(string)(
				$lead["stage"]
				?? "NEW"
			);


		$now =
			date("Y-m-d H:i:s");


		$subject =
			$subject !== null
				? trim($subject)
				: null;

		if ($subject === '') {
			$subject = null;
		}


		$providerMessageId =
			$providerMessageId !== null
				? trim($providerMessageId)
				: null;

		if ($providerMessageId === '') {
			$providerMessageId = null;
		}


		$salesMessageId =
			$this->repository->createInbound([
				"conversation_id" =>
					$conversationId,

				"direction" =>
					"INBOUND",

				"sender_type" =>
					"CONTACT",

				"subject" =>
					$subject,

				"message" =>
					$message,

				"provider_message_id" =>
					$providerMessageId,

				"status" =>
					"RECEIVED",

				"ai_generated" =>
					false,

				"approved" =>
					false,

				"received_at" =>
					$now,

				"updated_at" =>
					$now
			]);


		$this->conversationRepository
			->markOpenAfterReply(
				$conversationId,
				$now
			);


		$newStage =
			$currentStage;

		if (
			in_array(
				$currentStage,
				[
					"NEW",
					"RESEARCHING",
					"CONTACTED"
				],
				true
			)
		) {
			$this->leadRepository
				->markReplied(
					$salesLeadId,
					$now
				);

			$newStage =
				"REPLIED";

		} else {
			$this->leadRepository
				->updateAfterContact(
					$salesLeadId,
					$now
				);
		}


		return [
			"sales_message_id" =>
				$salesMessageId,

			"conversation_id" =>
				$conversationId,

			"sales_lead_id" =>
				$salesLeadId,

			"previous_stage" =>
				$currentStage,

			"current_stage" =>
				$newStage,

			"received_at" =>
				$now
		];
	}


	public function generateAiDraft(
		int $conversationId
	): array {
		if ($conversationId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid conversation ID."
			);
		}


		$conversation =
			$this->conversationRepository
				->findById(
					$conversationId
				);

		if ($conversation === null) {
			throw new \Exception(
				"Sales conversation not found."
			);
		}


		$salesLeadId =
			(int)(
				$conversation["sales_lead_id"]
					?? 0
			);

		if ($salesLeadId <= 0) {
			throw new \Exception(
				"Sales lead not found for conversation."
			);
		}


		$lead =
			$this->leadRepository
				->findById(
					$salesLeadId
				);

		if ($lead === null) {
			throw new \Exception(
				"Sales lead not found."
			);
		}


		$salesCompanyId =
			(int)(
				$lead["sales_company_id"]
					?? 0
			);

		if ($salesCompanyId <= 0) {
			throw new \Exception(
				"Sales company not found for lead."
			);
		}


		$this->companyRepository ??=
			new SalesCompanyRepository();

		$company =
			$this->companyRepository
				->findById(
					$salesCompanyId
				);

		if ($company === null) {
			throw new \Exception(
				"Sales company not found."
			);
		}


		$salesContactId =
			(int)(
				$conversation["sales_contact_id"]
					??
				$lead["primary_contact_id"]
					??
				0
			);

		if ($salesContactId <= 0) {
			throw new \Exception(
				"Sales contact not found for conversation."
			);
		}


		$this->contactRepository ??=
			new SalesContactRepository();

		$contact =
			$this->contactRepository
				->findById(
					$salesContactId
				);

		if ($contact === null) {
			throw new \Exception(
				"Sales contact not found."
			);
		}


		if (
			(int)($contact["sales_company_id"] ?? 0)
			!== $salesCompanyId
		) {
			throw new \Exception(
				"Sales contact does not belong to sales company."
			);
		}


		$messageResult =
			$this->repository
				->findByConversationId(
					$conversationId
				);

		if (
			empty($messageResult["success"]) ||
			empty($messageResult["data"])
		) {
			throw new \Exception(
				"Sales conversation has no messages."
			);
		}


		$messages =
			array_values(
				$messageResult["data"]
			);

		$latestMessage =
			end(
				$messages
			);

		if (
			!is_array($latestMessage) ||
			(string)(
				$latestMessage["direction"]
					?? ''
			) !== "INBOUND"
		) {
			throw new \Exception(
				"AI draft can only be generated after an inbound message."
			);
		}


		$contactName =
			trim(
				(string)(
					$contact["first_name"]
						?? ''
				) .
				" " .
				(string)(
					$contact["last_name"]
						?? ''
				)
			);


		$messageContext = [];

		foreach ($messages as $message) {
			$direction =
				(string)(
					$message["direction"]
						?? ''
				);

			$status =
				(string)(
					$message["status"]
						?? ''
				);


			if (
				$direction === "OUTBOUND" &&
				!in_array(
					$status,
					[
						"SENT",
						"DELIVERED"
					],
					true
				)
			) {
				continue;
			}


			if (
				$direction === "INBOUND" &&
				$status !== "RECEIVED"
			) {
				continue;
			}


			$messageContext[] = [
				"direction" =>
					$direction,

				"sender_type" =>
					(string)(
						$message["sender_type"]
							?? ''
					),

				"subject" =>
					$message["subject"]
						?? null,

				"message" =>
					(string)(
						$message["message"]
							?? ''
					)
			];
		}


		if (empty($messageContext)) {
			throw new \Exception(
				"Sales conversation has no usable message history."
			);
		}


		$context = [
			"product" => [
				"name" =>
					"AllStockControl",

				"verified_facts" => [
					"AllStockControl helps companies manage inventory and stock operations from one system."
				]
			],

			"company" => [
				"name" =>
					(string)(
						$company["company_name"]
							?? ''
					),

				"industry" =>
					$company["industry"]
						?? null,

				"country" =>
					$company["country"]
						?? null,

				"city" =>
					$company["city"]
						?? null,

				"language" =>
					$company["language"]
						?? null
			],

			"contact" => [
				"name" =>
					$contactName,

				"job_title" =>
					$contact["job_title"]
						?? null,

				"language" =>
					$contact["language"]
						?? null
			],

			"lead" => [
				"stage" =>
					(string)(
						$lead["stage"]
							?? ''
					),

				"market" =>
					$lead["market"]
						?? null,

				"country" =>
					$lead["country"]
						?? null,

				"language" =>
					$lead["language"]
						?? null
			],

			"conversation" => [
				"channel" =>
					(string)(
						$conversation["channel"]
							?? ''
					),

				"subject" =>
					$conversation["subject"]
						?? null
			],

			"messages" =>
				$messageContext
		];


		$this->aiSalesAgentClient ??=
			new AiSalesAgentClient();


		$generatedReply =
			$this->aiSalesAgentClient
				->generateReply(
					$context
				);


		$now =
			date(
				"Y-m-d H:i:s"
			);


		$salesMessageId =
			$this->repository
				->createAiDraft([
					"conversation_id" =>
						$conversationId,

					"direction" =>
						"OUTBOUND",

					"sender_type" =>
						"AI",

					"subject" =>
						$generatedReply["subject"]
							?? null,

					"message" =>
						$generatedReply["message"],

					"status" =>
						"PENDING_APPROVAL",

					"ai_generated" =>
						true,

					"ai_model" =>
						$generatedReply["model"]
							?? null,

					"approved" =>
						false,

					"updated_at" =>
						$now
				]);


		return [
			"sales_message_id" =>
				$salesMessageId,

			"conversation_id" =>
				$conversationId,

			"sales_lead_id" =>
				$salesLeadId,

			"status" =>
				"PENDING_APPROVAL",

			"ai_model" =>
				$generatedReply["model"]
					?? null,

			"created_at" =>
				$now
		];
	}


	private function isDatabaseTrue(
		mixed $value
	): bool {
		return
			$value === true ||
			$value === "t" ||
			$value === 1 ||
			$value === "1";
	}


    public function approveMessage(
		int $salesMessageId,
		int $approvedByUserId
	): void {
		if ($salesMessageId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales message ID."
			);
		}

		if ($approvedByUserId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid approving user ID."
			);
		}

		$message =
			$this->repository->findById(
				$salesMessageId
			);

		if ($message === null) {
			throw new \Exception(
				"Sales message not found."
			);
		}

		$status =
			(string)($message["status"] ?? '');

		if ($status !== "PENDING_APPROVAL") {
			throw new \Exception(
				"Only messages pending approval can be approved."
			);
		}

		$direction =
			(string)($message["direction"] ?? '');

		if ($direction !== "OUTBOUND") {
			throw new \Exception(
				"Only outbound messages can be approved."
			);
		}

		$this->repository->approve(
			$salesMessageId,
			$approvedByUserId
		);
	}



}