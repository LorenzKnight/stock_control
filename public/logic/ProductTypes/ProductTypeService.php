<?php

namespace App\ProductTypes;

class ProductTypeService
{
	private ProductTypeRepository $repository;

	public function __construct(
		ProductTypeRepository $repository
	) {
		$this->repository = $repository;
	}


	public function getProductTypes(
		int $userId,
		?int $companyId = null
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access. User not found or invalid token."
			);
		}

		$ownerUserId =
			$this->repository
				->findOwnerUserId($userId);

		$result =
			$this->repository
				->findProductTypes(
					$ownerUserId,
					$companyId
				);

		if (empty($result["success"])) {
			if (
				($result["message"] ?? '') ===
				"No records found"
			) {
				return [];
			}

			throw new \RuntimeException(
				"Error loading product types."
			);
		}

		return array_values(
			$result["data"] ?? []
		);
	}

    public function createProductType(
		int $userId,
		string $name,
		?int $companyId = null
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access: invalid or missing token."
			);
		}

		$name = trim($name);

		if ($name === '') {
			throw new \InvalidArgumentException(
				"Type name is required."
			);
		}

		if (mb_strlen($name) > 100) {
			throw new \InvalidArgumentException(
				"Type name too long (max 100)."
			);
		}

		$existing =
			$this->repository->findExisting(
				$userId,
				$name,
				$companyId
			);

		if ($existing !== null) {
			return [
				"id" =>
					(int)$existing["product_type_id"],
				"name" =>
					(string)$existing["product_type_name"],
				"already_exists" => true
			];
		}

		$data = [
			"user_id" => $userId,
			"product_type_name" => $name,
			"create_by" => $userId,
			"created_at" => date("Y-m-d H:i:s")
		];

		if ($companyId !== null) {
			$data["company_id"] = $companyId;
		}

		$id =
			$this->repository->create($data);

		return [
			"id" => $id,
			"name" => $name,
			"already_exists" => false
		];
	}
}