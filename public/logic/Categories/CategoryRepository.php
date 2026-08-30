<?php

namespace App\Categories;

class CategoryRepository
{
	public function findRootCategories(
		int $userId,
		int $companyId
	): array {
		$result = \select_from(
			"category",
			[
				"category_id",
				"category_name"
			],
			[
				"cat_parent_sub" => null,
				"sub_parent" => null,
				"user_id" => $userId,
				"company_id" => $companyId
			],
			[
				"order_by" => "category_name",
				"return_type" => "array"
			]
		);

        if (!is_array($result)) {
			throw new \RuntimeException(
				"CategoryRepository expected an array response."
			);
		}

		return $result;
	}

    public function create(array $data): int
	{
		$result = \insert_into(
			"category",
			$data,
			[
				"id" => "category_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				"Failed to insert category."
			);
		}

		return (int)$result["id"];
	}
}