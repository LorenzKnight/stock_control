<?php

namespace App\AiSales;

class SalesContactRepository
{
	public function findByCompanyId(
		int $salesCompanyId,
		string $search = ''
	): array {
		$where = [
			"sales_company_id" => $salesCompanyId
		];

		if ($search !== '') {
			$where["OR"] = [
				"first_name ILIKE" => $search,
				"last_name ILIKE" => $search,
				"job_title ILIKE" => $search,
				"email ILIKE" => $search
			];
		}

		$result = \select_from(
			"sales_contacts",
			[
				"sales_contact_id",
				"sales_company_id",
				"first_name",
				"last_name",
				"job_title",
				"email",
				"phone",
				"linkedin_url",
				"language",
				"is_primary",
				"do_not_contact",
				"created_by",
				"created_at",
				"updated_at"
			],
			$where,
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesContactRepository expected an array response."
			);
		}

		return $result;
	}
}