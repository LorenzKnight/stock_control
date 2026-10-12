<?php

namespace App\AiSales;

class SalesCompanyRepository
{
	public function findAll(
		string $search = ''
	): array {
		$where = [];

		if ($search !== '') {
			$where["OR"] = [
				"company_name ILIKE" => "%{$search}%",
				"country ILIKE" => "%{$search}%",
				"industry ILIKE" => "%{$search}%"
			];
		}

		$result = \select_from(
			"sales_companies",
			[
				"sales_company_id",
				"company_name",
				"website",
				"domain",
				"market",
				"country",
				"country_code",
				"city",
				"industry",
				"language",
				"description",
				"source",
				"source_url",
				"ai_researched",
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
				"SalesCompanyRepository expected an array response."
			);
		}

		return $result;
	}


	public function findById(
		int $salesCompanyId
	): ?array {
		$result = \select_from(
			"sales_companies",
			[
				"sales_company_id",
				"company_name",
				"website",
				"domain",
				"market",
				"country",
				"country_code",
				"city",
				"industry",
				"language",
				"description"
			],
			[
				"sales_company_id" =>
					$salesCompanyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesCompanyRepository expected an array response."
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
}