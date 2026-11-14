<?php

namespace App\AiSales;

class SalesMessageRepository
{
	public function findByConversationId(
		int $conversationId
	): array {
		$result = \select_from(
			"sales_messages",
			[
				"sales_message_id",
				"conversation_id",
				"direction",
				"sender_type",
				"subject",
				"message",
				"provider_message_id",
				"status",
				"ai_generated",
				"ai_model",
				"approved",
				"approved_by_user_id",
				"approved_at",
				"sent_at",
				"received_at",
				"created_at",
				"updated_at"
			],
			[
				"conversation_id" => $conversationId
			],
			[
				"order_by" => "created_at",
				"order_direction" => "ASC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesMessageRepository expected an array response."
			);
		}

		return $result;
	}


	public function findByProviderMessageId(
		string $providerMessageId
	): ?array {
		$normalizedId = trim(
			$providerMessageId,
			" <> \t\n\r"
		);

		if ($normalizedId === '') {
			return null;
		}

		$candidates = [
			$normalizedId,
			'<' . $normalizedId . '>'
		];

		foreach ($candidates as $candidate) {
			$result = \select_from(
				"sales_messages",
				[
					"sales_message_id",
					"conversation_id",
					"direction",
					"status",
					"provider_message_id"
				],
				[
					"provider_message_id" => $candidate
				],
				[
					"fetch_first" => true,
					"return_type" => "array"
				]
			);

			if (!is_array($result)) {
				throw new \RuntimeException(
					"Invalid sales message lookup response."
				);
			}

			if (
				!empty($result["success"]) &&
				!empty($result["data"])
			) {
				return $result["data"];
			}
		}

		return null;
	}


    public function findById(
		int $salesMessageId
	): ?array {
		$result = \select_from(
			"sales_messages",
			[
				"sales_message_id",
				"conversation_id",
				"direction",
				"sender_type",
				"subject",
				"message",
				"status",
				"ai_generated",
				"approved",
				"approved_by_user_id",
				"approved_at",
				"sent_at",
				"received_at",
				"created_at",
				"updated_at"
			],
			[
				"sales_message_id" => $salesMessageId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesMessageRepository expected an array response."
			);
		}

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return null;
		}

		return $result["data"];
	}


	public function approve(
		int $salesMessageId,
		int $approvedByUserId
	): void {
		$result = \update_table(
			"sales_messages",
			[
				"status" => "APPROVED",
				"approved" => true,
				"approved_by_user_id" => $approvedByUserId,
				"approved_at" => date("Y-m-d H:i:s"),
				"updated_at" => date("Y-m-d H:i:s")
			],
			[
				"sales_message_id" => $salesMessageId
			],
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Could not approve sales message."
			);
		}
	}


	public function markAsSent(
		int $salesMessageId,
		string $sentAt,
		?string $providerMessageId = null
	): void {
		$result = \update_table(
			"sales_messages",
			[
				"status" => "SENT",
				"provider_message_id" => $providerMessageId,
				"sent_at" => $sentAt,
				"updated_at" => $sentAt
			],
			[
				"sales_message_id" => $salesMessageId
			],
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Could not mark sales message as sent."
			);
		}
	}


	public function createInbound(
		array $data
	): int {
		$result = \insert_into(
			"sales_messages",
			$data,
			[
				"id" => "sales_message_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				$result["message"]
					?? "Could not create inbound sales message."
			);
		}

		return (int)$result["id"];
	}


	public function createAiDraft(
		array $data
	): int {
		$result = \insert_into(
			"sales_messages",
			$data,
			[
				"id" => "sales_message_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				$result["message"]
					?? "Could not create AI sales draft."
			);
		}

		return (int)$result["id"];
	}

	public function findUnreadByUser(int $userId): array
	{
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid user ID."
			);
		}

		$result = \select_from(
			"sales_messages sm
			INNER JOIN sales_conversations sc
				ON sc.conversation_id = sm.conversation_id
			INNER JOIN sales_leads sl
				ON sl.sales_lead_id = sc.sales_lead_id
			LEFT JOIN sales_message_reads smr
				ON smr.sales_message_id = sm.sales_message_id
				AND smr.user_id = {$userId}",
			[
				"sm.sales_message_id AS sales_message_id",
				"sc.conversation_id AS conversation_id",
				"sl.sales_company_id AS sales_company_id"
			],
			[
				"sm.direction" => "INBOUND",
				"sm.status" => "RECEIVED",
				"smr.sales_message_id" => null
			],
			[
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"Invalid unread messages response."
			);
		}

		if (empty($result["success"])) {
			if (
				($result["message"] ?? '') ===
				"No records found"
			) {
				return [];
			}

			throw new \RuntimeException(
				$result["message"] ??
				"Could not retrieve unread messages."
			);
		}

		return array_values($result["data"] ?? []);
	}
}