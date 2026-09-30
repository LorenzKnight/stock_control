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
}