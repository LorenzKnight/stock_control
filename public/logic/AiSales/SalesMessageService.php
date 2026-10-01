<?php

namespace App\AiSales;

class SalesMessageService
{
	private SalesMessageRepository $repository;

	private SalesConversationRepository $conversationRepository;

	private SalesLeadRepository $leadRepository;

	public function __construct(
		SalesMessageRepository $repository,
		SalesConversationRepository $conversationRepository,
		SalesLeadRepository $leadRepository
	) {
		$this->repository =
			$repository;

		$this->conversationRepository =
			$conversationRepository;

		$this->leadRepository =
			$leadRepository;
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