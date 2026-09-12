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

    public function createProduct(
		int $userId,
		int $companyId,
		array $data,
		?string $imageName = null,
		bool $confirmUpdate = false
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access: invalid or missing token."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"No company selected or linked to this user."
			);
		}

		$productName = trim(
			(string)($data["product_name"] ?? '')
		);

		$quantity = (int)(
			$data["quantity"] ?? 0
		);

		$price = trim(
			(string)($data["price"] ?? '')
		);

		if ($productName === '') {
			throw new \InvalidArgumentException(
				"Product name is required."
			);
		}

		if ($quantity < 0) {
			throw new \InvalidArgumentException(
				"Quantity must be 0 or more."
			);
		}

		if (
			is_numeric($price) &&
			(float)$price < 0
		) {
			throw new \InvalidArgumentException(
				"Price must be 0 or more."
			);
		}

		$unitType =
			(int)($data["unit_type"] ?? 1);

		$units =
			(int)($data["units"] ?? 1);

		$weightUnit =
			(float)($data["weight_unit"] ?? 0);

		$totalWeight =
			$weightUnit * $units;

		$productType =
			isset($data["product_type"]) &&
			$data["product_type"] !== ''
				? (int)$data["product_type"]
				: null;

		$productMark =
			(int)($data["product_mark"] ?? 0);

		$productModel =
			(int)($data["product_model"] ?? 0);

		$productSubModel =
			(int)($data["product_sub_model"] ?? 0);

		$productYear =
			(int)($data["product_year"] ?? 0);

		$existingProduct =
			$this->repository
				->findExistingProduct(
					$companyId,
					$productName,
					$productMark,
					$productModel,
					$productSubModel,
					$productYear
				);

		if (
			$existingProduct !== null &&
			!$confirmUpdate
		) {
			return [
				"needs_confirmation" => true,
				"existing_product_id" =>
					(int)$existingProduct["product_id"],
				"existing_quantity" =>
					(int)$existingProduct["quantity"]
			];
		}

		if ($existingProduct !== null) {
			$productId =
				(int)$existingProduct["product_id"];

			$currentQuantity =
				(int)$existingProduct["quantity"];

			$this->repository->updateQuantity(
				$companyId,
				$productId,
				$currentQuantity + $quantity
			);

			return [
				"needs_confirmation" => false,
				"product_id" => $productId,
				"product_name" => $productName,
				"show_reward_modal" => false,
				"reward_type" => null
			];
		}

		$productData = [
			"created_by" => $userId,
			"company_id" => $companyId,
			"sale_unit_type" => $unitType,
			"units_per_pack" => $units,
			"weight_per_unit" => $weightUnit,
			"total_weight" => $totalWeight,
			"product_name" => $productName,
			"hs_code" => trim(
				(string)($data["hs_code"] ?? '')
			),
			"product_type" => $productType,
			"product_mark" => $productMark,
			"product_model" => $productModel,
			"product_sub_model" =>
				$productSubModel,
			"product_year" => $productYear,
			"purpose" =>
				(int)($data["purpose"] ?? 1),
			"quantity" => $quantity,
			"min_quantity" =>
				(int)($data["min_quantity"] ?? 10),
			"currency" => trim(
				(string)($data["currency"] ?? '')
			),
			"price" => $price,
			"description" => trim(
				(string)($data["description"] ?? '')
			),
			"status" => 1,
			"created_at" =>
				date("Y-m-d H:i:s")
		];

		if (
			$imageName !== null &&
			$imageName !== ''
		) {
			$productData["product_image"] =
				$imageName;
		}

		$productId =
			$this->repository->create(
				$productData
			);

		$showReward =
			$this->processFirstProductOnboarding(
				$userId,
				$companyId
			);

		return [
			"needs_confirmation" => false,
			"product_id" => $productId,
			"product_name" => $productName,
			"show_reward_modal" => $showReward,
			"reward_type" =>
				$showReward
					? "first_product"
					: null
		];
	}

	private function processFirstProductOnboarding(
		int $userId,
		int $companyId
	): bool {
		try {
			$totalProducts =
				$this->repository
					->countCreatedByUser(
						$companyId,
						$userId
					);

			if ($totalProducts !== 1) {
				return false;
			}

			$onboarding =
				$this->repository
					->findProductOnboardingByUserId(
						$userId
					);

			if ($onboarding === null) {
				return $this->repository
					->createProductOnboarding(
						$userId
					);
			}

			$rewardAlreadySeen =
				$this->isDatabaseTrue(
					$onboarding[
						"product_reward_seen"
					] ?? false
				);

			$showReward =
				!$rewardAlreadySeen;

			$updated =
				$this->repository
					->markProductOnboardingComplete(
						$userId
					);

			if (!$updated) {
				error_log(
					"Could not update onboarding product step for user_id: " .
					$userId
				);
			}

			return $showReward;

		} catch (\Throwable $e) {
			error_log(
				"Could not process onboarding product state for user_id: " .
				$userId .
				" - " .
				$e->getMessage()
			);

			return false;
		}
	}


	private function isDatabaseTrue(
		mixed $value
	): bool {
		return
			$value === true ||
			$value === "t" ||
			$value === 1 ||
			$value === "1";
	}
}