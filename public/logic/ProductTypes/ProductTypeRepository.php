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

    public function findExisting(
		int $userId,
		string $name,
		?int $companyId = null
	): ?array {
		$where = [
			"create_by" => $userId,
			"company_id" => $companyId,
			"LOWER(product_type_name)" => [
				"condition" => "=",
				"value" => mb_strtolower($name)
			]
		];

		$result = \select_from(
			"product_type",
			[
				"product_type_id",
				"product_type_name"
			],
			$where,
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ProductTypeRepository expected an array response."
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
		$result = \insert_into(
			"product_type",
			$data,
			[
				"id" => "product_type_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				$result["message"]
				?? "Error inserting product type."
			);
		}

		return (int)$result["id"];
	}
}