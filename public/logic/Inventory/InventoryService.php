<?php

namespace App\Inventory;

class InventoryService
{
	private InventoryRepository $repository;

	public function __construct(
		InventoryRepository $repository
	) {
		$this->repository = $repository;
	}


	public function addStock(
		int $userId,
		int $productId,
		int $amount
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access."
			);
		}

		if ($productId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid product ID."
			);
		}

		if ($amount <= 0) {
			throw new \InvalidArgumentException(
				"Amount must be greater than 0."
			);
		}

		$companyId =
			$this->repository
				->findCompanyIdByUserId(
					$userId
				);

		if ($companyId === null) {
			throw new \Exception(
				"Unable to verify user company."
			);
		}

		$product =
			$this->repository
				->findProductStock(
					$productId
				);

		if ($product === null) {
			throw new \Exception(
				"Product not found."
			);
		}

		if (
			(int)($product["company_id"] ?? 0)
			!== $companyId
		) {
			throw new \Exception(
				"Access denied."
			);
		}

		$previousQty =
			(int)($product["quantity"] ?? 0);

		$this->repository->increaseStock(
			$productId,
			$amount
		);

		return [
			"product_id" =>
				$productId,

			"added_amount" =>
				$amount,

			"previous_stock" =>
				$previousQty,

			"new_stock" =>
				$previousQty + $amount
		];
	}
}