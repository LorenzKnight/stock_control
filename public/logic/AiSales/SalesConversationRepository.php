<?php

namespace App\AiSales;

class SalesConversationRepository
{
	public function findByLeadId(
		int $salesLeadId
	): array {
		$result = \select_from(
			"sales_conversations",
			[
				"conversation_id",
				"sales_lead_id",
				"sales_contact_id",
				"channel",
				"subject",
				"external_thread_id",
				"status",
				"started_at",
				"last_message_at",
				"created_at",
				"updated_at"
			],
			[
				"sales_lead_id" => $salesLeadId
			],
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesConversationRepository expected an array response."
			);
		}

		return $result;
	}


	public function findById(
		int $conversationId
	): ?array {
		$result = \select_from(
			"sales_conversations",
			[
				"conversation_id",
				"sales_lead_id",
				"sales_contact_id",
				"channel",
				"status",
				"last_message_at"
			],
			[
				"conversation_id" => $conversationId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesConversationRepository expected an array response."
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


	public function markWaitingReply(
		int $conversationId,
		string $lastMessageAt
	): void {
		$result = \update_table(
			"sales_conversations",
			[
				"status" => "WAITING_REPLY",
				"last_message_at" => $lastMessageAt,
				"updated_at" => $lastMessageAt
			],
			[
				"conversation_id" => $conversationId
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
				"Could not update sales conversation after sending message."
			);
		}
	}


	public function markOpenAfterReply(
		int $conversationId,
		string $lastMessageAt
	): void {
		$result = \update_table(
			"sales_conversations",
			[
				"status" => "OPEN",
				"last_message_at" => $lastMessageAt,
				"updated_at" => $lastMessageAt
			],
			[
				"conversation_id" => $conversationId
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
				"Could not update sales conversation after receiving reply."
			);
		}
	}
}