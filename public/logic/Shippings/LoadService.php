<?php

namespace App\Shippings;

class LoadService
{
	private LoadRepository $repository;

	public function __construct(
		LoadRepository $repository
	) {
		$this->repository = $repository;
	}


	public function getLoadsForShipping(
		int $companyId,
		int $shippingId
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		if ($shippingId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid shipping ID."
			);
		}

		$loads =
			$this->repository->findByShippingId(
				$companyId,
				$shippingId
			);

		foreach ($loads as &$load) {
			$customerId =
				(int)($load["customer_id"] ?? 0);

			$customer = null;

			if ($customerId > 0) {
				$customer =
					$this->repository
						->findCustomerById(
							$customerId,
							$companyId
						);
			}

			$load["customer"] = [
				"customer_id" =>
					$customerId,

				"full_name" =>
					trim(
						(string)(
							$customer["customer_name"]
							?? ''
						) .
						' ' .
						(string)(
							$customer["customer_surname"]
							?? ''
						)
					),

				"phone" =>
					$customer["customer_phone"]
					?? '',

				"image" =>
					$customer["customer_image"]
					?? ''
			];
		}

		unset($load);

		return $loads;
	}


    public function getProductsForLoad(
		int $loadId
	): array {
		if ($loadId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid load ID."
			);
		}

		$loadedProducts =
			$this->repository
				->findLoadedProductsByLoadId(
					$loadId
				);

		$productsData = [];
		$loadWeightTotal = 0.0;

		foreach ($loadedProducts as $loadedProduct) {
			$productId =
				(int)(
					$loadedProduct["product_id"]
					?? 0
				);

			$product = [];

			if ($productId > 0) {
				$product =
					$this->repository
						->findProductById(
							$productId
						)
					?? [];
			}

			$quantity =
				(int)(
					$loadedProduct["quantity"]
					?? 0
				);

			$totalKg =
				(float)(
					$loadedProduct["total_kg"]
					?? 0
				);

			$totalKgPrice =
				(float)(
					$loadedProduct["total_kg_price"]
					?? 0
				);

			$totalExchanged =
				(float)(
					$loadedProduct[
						"total_price_exchanged"
					] ?? 0
				);

			$loadWeightTotal += $totalKg;

			$markName = null;
			$modelName = null;
			$submodelName = null;

			$markId =
				(int)(
					$product["product_mark"]
					?? 0
				);

			if ($markId > 0) {
				$markName =
					$this->repository
						->findCategoryNameById(
							$markId
						);
			}

			$modelId =
				(int)(
					$product["product_model"]
					?? 0
				);

			if ($modelId > 0) {
				$modelName =
					$this->repository
						->findCategoryNameById(
							$modelId
						);
			}

			$submodelId =
				(int)(
					$product["product_sub_model"]
					?? 0
				);

			if ($submodelId > 0) {
				$submodelName =
					$this->repository
						->findCategoryNameById(
							$submodelId
						);
			}

			$productsData[] = [
				"product_id" =>
					$productId,

				"name" =>
					$product["product_name"]
					?? '',

				"year" =>
					$product["product_year"]
					?? '',

				"image" =>
					$product["product_image"]
					?? '',

				"mark_name" =>
					$markName,

				"model_name" =>
					$modelName,

				"submodel_name" =>
					$submodelName,

				"quantity" =>
					$quantity,

				"price" =>
					$product["price"]
					?? 0,

				"total_kg" =>
					$totalKg,

				"from_currency" =>
					$loadedProduct["from_currency"]
					?? '',

				"total_kg_price" =>
					$totalKgPrice,

				"to_currency" =>
					$loadedProduct["to_currency"]
					?? '',

				"total_price_exchanged" =>
					$totalExchanged,

				"weight_per_unit" =>
					(float)(
						$product["total_weight"]
						?? 0
					)
			];
		}

		return [
			"products" => $productsData,
			"total_weight" => $loadWeightTotal
		];
	}
}