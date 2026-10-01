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
		string $sentAt
	): void {
		$result = \update_table(
			"sales_messages",
			[
				"status" => "SENT",
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
}