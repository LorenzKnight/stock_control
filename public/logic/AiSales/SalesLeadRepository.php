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


	public function findById(
		int $salesLeadId
	): ?array {
		$result = \select_from(
			"sales_leads",
			[
				"sales_lead_id",
				"sales_company_id",
				"primary_contact_id",
				"stage",
				"last_contact_at",
				"next_action",
				"next_action_at"
			],
			[
				"sales_lead_id" => $salesLeadId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesLeadRepository expected an array response."
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


	public function updateAfterContact(
		int $salesLeadId,
		string $lastContactAt,
		?string $stage = null
	): void {
		$data = [
			"last_contact_at" => $lastContactAt,
			"updated_at" => $lastContactAt
		];

		if ($stage !== null) {
			$data["stage"] = $stage;
		}

		$result = \update_table(
			"sales_leads",
			$data,
			[
				"sales_lead_id" => $salesLeadId
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
				"Could not update sales lead after contact."
			);
		}
	}


	public function markReplied(
		int $salesLeadId,
		string $repliedAt
	): void {
		$result = \update_table(
			"sales_leads",
			[
				"stage" => "REPLIED",
				"last_contact_at" => $repliedAt,
				"updated_at" => $repliedAt
			],
			[
				"sales_lead_id" => $salesLeadId
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
				"Could not update sales lead after receiving reply."
			);
		}
	}
}