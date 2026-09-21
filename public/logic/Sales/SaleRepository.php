<?php

namespace App\Sales;

class SaleRepository
{
	public function findCustomerById(
		int $customerId,
		int $companyId
	): ?array {
		$result = \select_from(
			"customers",
			["customer_id"],
			[
				"customer_id" => $customerId,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SaleRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? "") === "No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read sale customer."
		);
	}


	public function getNextOrderNumber(
		int $companyId
	): int {
		return \get_next_increment_value(
			"sales",
			"ord_no",
			$companyId,
			10000000
		);
	}


	public function create(
		array $data
	): int {
		$result = \insert_into(
			"sales",
			$data,
			[
				"id" => "sales_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				"Failed to create sale record."
			);
		}

		return (int)$result["id"];
	}


	public function createPurchasedProduct(
		array $data
	): void {
		$result = \insert_into(
			"purchased_products",
			$data,
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Failed to add purchased product."
			);
		}
	}


	public function findCompanyUserIds(
		int $companyId
	): array {
		$result = \select_from(
			"users",
			["user_id"],
			[
				"company_id" => $companyId
			],
			[
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SaleRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"]) &&
			is_array($result["data"])
		) {
			return array_map(
				static fn(array $row): int =>
					(int)$row["user_id"],
				array_values($result["data"])
			);
		}

		if (
			($result["message"] ?? "") ===
				"No records found"
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not read company users."
		);
	}


	public function findOnboardingByUserId(
		int $userId
	): ?array {
		$result = \select_from(
			"user_onboarding",
			[
				"user_id",
				"company",
				"product",
				"client",
				"sale",
				"sale_reward_seen"
			],
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
				"SaleRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? "") ===
				"No records found"
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read sale onboarding state."
		);
	}


	public function updateOnboarding(
		int $userId,
		array $data
	): void {
		$result = \update_table(
			"user_onboarding",
			$data,
			[
				"user_id" => $userId
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
				"Could not update sale onboarding."
			);
		}
	}


	public function createOnboarding(
		array $data
	): void {
		$result = \insert_into(
			"user_onboarding",
			$data,
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Could not create sale onboarding."
			);
		}
	}


    public function productBelongsToCompany(
		int $productId,
		int $companyId
	): bool {
		$result = \select_from(
			"products",
			["product_id"],
			[
				"product_id" => $productId,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SaleRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return true;
		}

		if (
			($result["message"] ?? "") ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return false;
		}

		throw new \RuntimeException(
			"Could not validate sale product."
		);
	}
}