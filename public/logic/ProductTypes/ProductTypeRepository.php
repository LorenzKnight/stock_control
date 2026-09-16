<?php

namespace App\ProductTypes;

class ProductTypeRepository
{
	public function findOwnerUserId(
		int $userId
	): int {
		$result = \select_from(
			"users",
			["parent_user"],
			[
				"user_id" => $userId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["data"])
		) {
			throw new \RuntimeException(
				"No user data found."
			);
		}

		$parentUser =
			(int)(
				$result["data"]["parent_user"]
				?? 0
			);

		return $parentUser > 0
			? $parentUser
			: $userId;
	}


	public function findProductTypes(
		int $ownerUserId,
		?int $companyId = null
	): array {
		$where = [
			"user_id" => $ownerUserId
		];

		if ($companyId !== null) {
			$where["company_id"] =
				$companyId;
		}

		$result = \select_from(
			"product_type",
			[
				"product_type_id",
				"product_type_name",
				"company_id",
				"create_by",
				"created_at"
			],
			$where,
			[
				"order_by" =>
					"product_type_id",
				"order_direction" =>
					"ASC",
				"return_type" =>
					"array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ProductTypeRepository expected an array response."
			);
		}

		return $result;
	}
}