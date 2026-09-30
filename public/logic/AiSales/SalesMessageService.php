<?php

namespace App\AiSales;

class SalesMessageService
{
	private SalesMessageRepository $repository;

	public function __construct(
		SalesMessageRepository $repository
	) {
		$this->repository = $repository;
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
	): void {
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

		$this->repository->markAsSent(
			$salesMessageId
		);
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