<?php

namespace App\AiSales;

class SalesLeadRepository
{
	public function findByCompanyId(
		int $salesCompanyId
	): array {
		$result = \select_from(
			"sales_leads",
			[
				"sales_lead_id",
				"sales_company_id",
				"primary_contact_id",
				"market",
				"country",
				"language",
				"stage",
				"score",
				"score_reason",
				"source",
				"next_action",
				"next_action_at",
				"last_contact_at",
				"notes",
				"ai_summary",
				"created_by",
				"created_at",
				"updated_at"
			],
			[
				"sales_company_id" => $salesCompanyId
			],
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesLeadRepository expected an array response."
			);
		}

		return $result;
	}
}