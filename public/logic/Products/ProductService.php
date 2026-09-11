<?php

namespace App\Products;

class ProductService
{
	private ProductRepository $repository;

	public function __construct(
		ProductRepository $repository
	) {
		$this->repository = $repository;
	}


	public function getProducts(
		int $companyId,
		array $filters = []
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"No company selected or linked to this user."
			);
		}

		$products =
			$this->repository->findProducts(
				$companyId,
				$filters
			);

		$result = [];

		foreach ($products as $product) {
			$enriched =
				$this->repository->enrichProduct(
					$product,
					$companyId
				);

			if ($enriched !== null) {
				$result[] = $enriched;
				continue;
			}

			error_log(
				"Product discarded. Product company_id: " .
				($product["company_id"] ?? 'NULL') .
				" / companyFilter: " .
				$companyId
			);
		}

		return $result;
	}


	public function getProductByBarcode(
		int $companyId,
		string $barcode
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"No company selected or linked to this user."
			);
		}

		$product =
			$this->repository->findByBarcode(
				$companyId,
				$barcode
			);

		if ($product === null) {
			return [
				"found" => false,
				"product" => null
			];
		}

		$enriched =
			$this->repository->enrichProduct(
				$product,
				$companyId
			);

		return [
			"found" => true,
			"product" => $enriched
		];
	}
}