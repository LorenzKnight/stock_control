<?php

namespace App\Inventory;

class InventoryRepository
{
	public function findCompanyIdByUserId(
		int $userId
	): ?int {
		$result = \select_from(
			"users",
			["company_id"],
			[
				"user_id" => $userId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"InventoryRepository expected an array response."
			);
		}

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return null;
		}

		return (int)(
			$result["data"]["company_id"] ?? 0
		);
	}


	public function findProductStock(
		int $productId
	): ?array {
		$result = \select_from(
			"products",
			[
				"company_id",
				"quantity"
			],
			[
				"product_id" => $productId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"InventoryRepository expected an array response."
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


	public function increaseStock(
		int $productId,
		int $amount
	): void {
		$result = \update_table(
			"products",
			[
				"quantity" =>
					"quantity + {$amount}"
			],
			[
				"product_id" => $productId
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
				"Error updating stock."
			);
		}
	}
}