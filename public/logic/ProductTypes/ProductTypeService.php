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
}