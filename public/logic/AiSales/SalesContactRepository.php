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
				"source_url",
"source_type",
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


	public function findById(
		int $salesContactId
	): ?array {
		$result = \select_from(
			"sales_contacts",
			[
				"sales_contact_id",
				"sales_company_id",
				"first_name",
				"last_name",
				"job_title",
				"language",
				"is_primary",
				"do_not_contact"
			],
			[
				"sales_contact_id" =>
					$salesContactId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SalesContactRepository expected an array response."
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
				"sales_contacts",
				$data,
				[
					"id" =>
						"sales_contact_id",

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
					?? "Could not create sales contact."
			);
		}


		return (int)$result["id"];
	}
}