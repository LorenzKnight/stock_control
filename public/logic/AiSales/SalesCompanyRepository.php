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
				"research_status",
				"research_summary",
				"research_inventory_context",
				"research_sources",
				"researched_at",
				"qualification_status",
				"qualification_score",
				"qualification_level",
				"qualification_reason",
				"qualified_at",
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
				"description",
				"source",
				"source_url",
				"ai_researched",
				"research_status",
				"research_summary",
				"research_inventory_context",
				"research_sources",
				"researched_at",
				"qualification_status",
				"qualification_score",
				"qualification_level",
				"qualification_reason",
				"qualified_at"
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


	public function findByDomain(
		string $domain
	): ?array {
		$domain =
			strtolower(
				trim(
					$domain
				)
			);

		if ($domain === '') {
			return null;
		}


		$result =
			\select_from(
				"sales_companies",
				[
					"sales_company_id",
					"company_name",
					"website",
					"domain",
					"market",
					"country",
					"city",
					"industry",
					"language",
					"description",
					"source",
					"source_url",
					"ai_researched"
				],
				[
					"domain" =>
						$domain
				],
				[
					"fetch_first" =>
						true,

					"return_type" =>
						"array"
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


	public function create(
		array $data
	): int {
		$result =
			\insert_into(
				"sales_companies",
				$data,
				[
					"id" =>
						"sales_company_id",

					"return_type" =>
						"array"
				]
			);


		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				$result["message"]
					?? "Could not create sales company."
			);
		}


		return (int)$result["id"];
	}


	public function updateResearch(
		int $salesCompanyId,
		array $data
	): void {
		if ($salesCompanyId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales company ID."
			);
		}


		$result =
			\update_table(
				"sales_companies",
				$data,
				[
					"sales_company_id" =>
						$salesCompanyId
				],
				[
					"return_type" =>
						"array"
				]
			);


		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Could not update company research."
			);
		}
	}


	public function updateQualification(
		int $salesCompanyId,
		array $data
	): void {
		if ($salesCompanyId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales company ID."
			);
		}


		$result =
			\update_table(
				"sales_companies",
				$data,
				[
					"sales_company_id" =>
						$salesCompanyId
				],
				[
					"return_type" =>
						"array"
				]
			);


		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Could not update company qualification."
			);
		}
	}
}