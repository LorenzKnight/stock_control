<?php

namespace App\Customers;

class CustomerRepository
{
	public function findCompanyIdByUserId(int $userId): ?int
	{
		$result = \select_from(
			"users",
			["company_id"],
			["user_id" => $userId],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"CustomerRepository expected an array response."
			);
		}

		if (
			empty($result["success"]) ||
			empty($result["data"]) ||
			empty($result["data"]["company_id"])
		) {
			return null;
		}

		return (int)$result["data"]["company_id"];
	}


	public function findCustomers(
		int $companyId,
		string $search = ''
	): array {
		$where = [
			"company_id" => $companyId
		];

		if ($search !== '') {
			$where["OR"] = [
				"customer_name ILIKE" => "%{$search}%",
				"customer_surname ILIKE" => "%{$search}%",
				"customer_document_no ILIKE" => "%{$search}%"
			];
		}

		$result = \select_from(
			"customers",
			[
				"customer_id",
				"customer_name",
				"customer_surname",
				"customer_email",
				"cu_country_code",
				"customer_phone",
				"customer_birthday",
				"customer_type",
				"customer_image",
				"customer_document_type",
				"customer_document_no",
				"customer_address",
				"customer_status",
				"references_1",
				"r1_country_code",
				"references_1_phone",
				"references_2",
				"r2_country_code",
				"references_2_phone"
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
				"CustomerRepository expected an array response."
			);
		}

		return $result;
	}
}