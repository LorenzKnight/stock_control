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


	public function deleteLoadsForShipping(
		int $companyId,
		int $shippingId
	): void {
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

		$loadIds =
			$this->repository
				->findIdsByShippingId(
					$companyId,
					$shippingId
				);

		foreach ($loadIds as $loadId) {
			$this->repository
				->deleteLoadedProductsByLoadId(
					$loadId
				);

			$this->repository->deleteById(
				$loadId,
				$companyId
			);
		}
	}


	public function createLoad(
		int $userId,
		int $companyId,
		array $data
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		$required = [
			"customer_id",
			"shippings_id",
			"from_currency",
			"to_currency",
			"price_per_kg",
			"total_kg"
		];

		foreach ($required as $field) {
			if (
				!isset($data[$field]) ||
				$data[$field] === ""
			) {
				throw new \InvalidArgumentException(
					"Missing required field: {$field}"
				);
			}
		}

		$shippingId =
			(int)$data["shippings_id"];

		$customerId =
			(int)$data["customer_id"];

		if ($shippingId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid shipping ID."
			);
		}

		if ($customerId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid customer ID."
			);
		}

		$shipping =
			$this->repository
				->findShippingById(
					$shippingId,
					$companyId
				);

		if ($shipping === null) {
			throw new \Exception(
				"The selected shipping does not exist or does not belong to this company."
			);
		}

		$customer =
			$this->repository
				->findCustomerById(
					$customerId,
					$companyId
				);

		if ($customer === null) {
			throw new \Exception(
				"The selected customer does not exist or does not belong to this company."
			);
		}

		$fromCurrency =
			strtoupper(
				trim(
					(string)$data["from_currency"]
				)
			);

		$toCurrency =
			strtoupper(
				trim(
					(string)$data["to_currency"]
				)
			);

		if ($fromCurrency === '') {
			throw new \InvalidArgumentException(
				"From currency is required."
			);
		}

		if ($toCurrency === '') {
			throw new \InvalidArgumentException(
				"To currency is required."
			);
		}

		$pricePerKg =
			(float)$data["price_per_kg"];

		$totalKg =
			(float)$data["total_kg"];

		if ($pricePerKg <= 0) {
			throw new \InvalidArgumentException(
				"Price per kg must be greater than 0."
			);
		}

		if ($totalKg <= 0) {
			throw new \InvalidArgumentException(
				"Total kg must be greater than 0."
			);
		}

		$discount =
			(float)($data["discount"] ?? 0);

		$taxes =
			(float)($data["taxes"] ?? 0);

		$destination =
			trim(
				(string)($data["destination"] ?? '')
			);

		$comment =
			trim(
				(string)($data["comment"] ?? '')
			);

		$priceSum =
			$pricePerKg * $totalKg;

		$subtotal =
			$priceSum - $discount;

		$taxAmount =
			($subtotal * $taxes) / 100;

		$priceTotal =
			$subtotal + $taxAmount;

		$priceTotalExchanged =
			isset(
				$data["price_total_exchanged"]
			)
				? (float)
					$data["price_total_exchanged"]
				: $priceTotal;

		/*
		* Validamos productos antes de crear el load.
		*/
		$normalizedProducts = [];

		$products =
			$data["products"] ?? [];

		if (is_array($products)) {
			foreach ($products as $product) {
				$productId =
					(int)(
						$product["product_id"]
						?? 0
					);

				/*
				* Preservamos el comportamiento
				* anterior: IDs inválidos se ignoran.
				*/
				if ($productId <= 0) {
					continue;
				}

				if (
					!$this->repository
						->productBelongsToCompany(
							$productId,
							$companyId
						)
				) {
					throw new \Exception(
						"Product ID {$productId} does not belong to this company."
					);
				}

				$quantity =
					max(
						1,
						(int)(
							$product["quantity"]
							?? 1
						)
					);

				$productTotalKg =
					(float)(
						$product["total_kg"]
						?? 0
					);

				$totalKgPrice =
					(float)(
						$product["total_kg_price"]
						?? 0
					);

				$convertedPrice =
					isset(
						$product[
							"total_price_exchanged"
						]
					)
						? (float)
							$product[
								"total_price_exchanged"
							]
						: $totalKgPrice;

				$normalizedProducts[] = [
					"product_id" =>
						$productId,

					"quantity" =>
						$quantity,

					"total_kg" =>
						number_format(
							$productTotalKg,
							3,
							'.',
							''
						),

					"total_kg_price" =>
						number_format(
							$totalKgPrice,
							2,
							'.',
							''
						),

					"total_price_exchanged" =>
						number_format(
							$convertedPrice,
							2,
							'.',
							''
						)
				];
			}
		}

		$loadNo =
			$this->repository
				->getNextLoadNumber(
					$companyId
				);

		$loadId =
			$this->repository->create([
				"shippings_id" =>
					$shippingId,

				"customer_id" =>
					$customerId,

				"company_id" =>
					$companyId,

				"load_no" =>
					$loadNo,

				"from_currency" =>
					$fromCurrency,

				"to_currency" =>
					$toCurrency,

				"price_per_kg" =>
					number_format(
						$pricePerKg,
						2,
						'.',
						''
					),

				"total_kg" =>
					number_format(
						$totalKg,
						3,
						'.',
						''
					),

				"price_sum" =>
					number_format(
						$priceSum,
						2,
						'.',
						''
					),

				"taxes" =>
					number_format(
						$taxes,
						2,
						'.',
						''
					),

				"discount" =>
					number_format(
						$discount,
						2,
						'.',
						''
					),

				"price_total" =>
					number_format(
						$priceTotal,
						2,
						'.',
						''
					),

				"price_total_exchanged" =>
					number_format(
						$priceTotalExchanged,
						2,
						'.',
						''
					),

				"destination" =>
					$destination,

				"comment" =>
					$comment,

				"status" => 1,

				"create_by" =>
					$userId
			]);

		foreach ($normalizedProducts as $product) {
			$this->repository
				->createLoadedProduct([
					"load_id" =>
						$loadId,

					"product_id" =>
						$product["product_id"],

					"quantity" =>
						$product["quantity"],

					"total_kg" =>
						$product["total_kg"],

					"from_currency" =>
						$fromCurrency,

					"total_kg_price" =>
						$product[
							"total_kg_price"
						],

					"to_currency" =>
						$toCurrency,

					"total_price_exchanged" =>
						$product[
							"total_price_exchanged"
						],

					"create_by" =>
						$userId
				]);
		}

		return [
			"load_id" => $loadId,
			"load_no" => $loadNo
		];
	}


	public function getLoadById(
		int $companyId,
		int $loadId
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		if ($loadId <= 0) {
			throw new \InvalidArgumentException(
				"Load ID is required."
			);
		}

		$load =
			$this->repository->findById(
				$loadId,
				$companyId
			);

		if ($load === null) {
			throw new \Exception(
				"Load not found."
			);
		}

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

		$loadedProducts =
			$this->repository
				->findLoadedProductsByLoadId(
					$loadId
				);

		$productsData = [];

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
							$productId,
							$companyId
						)
					?? [];
			}

			/*
			* Conservamos el contrato original de
			* get_load.php utilizado por shipping.js.
			*/
			$productsData[] = [
				"product_id" =>
					$productId,

				"quantity" =>
					(int)(
						$loadedProduct["quantity"]
						?? 0
					),

				"total_kg" =>
					$loadedProduct["total_kg"]
					?? 0,

				"from_currency" =>
					$loadedProduct["from_currency"]
					?? '',

				"total_kg_price" =>
					$loadedProduct["total_kg_price"]
					?? 0,

				"to_currency" =>
					$loadedProduct["to_currency"]
					?? '',

				"total_price_exchanged" =>
					$loadedProduct[
						"total_price_exchanged"
					] ?? 0,

				"product_name" =>
					$product["product_name"]
					?? '',

				"product_image" =>
					$product["product_image"]
					?? '',

				"product_mark" =>
					$product["product_mark"]
					?? null,

				"product_model" =>
					$product["product_model"]
					?? null,

				"product_sub_model" =>
					$product["product_sub_model"]
					?? null
			];
		}

		return [
			"load_id" =>
				$load["load_id"],

			"shipping_id" =>
				$load["shippings_id"],

			"customer" => [
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
			],

			"products" =>
				$productsData,

			"from_currency" =>
				$load["from_currency"],

			"to_currency" =>
				$load["to_currency"],

			"price_per_kg" =>
				$load["price_per_kg"],

			"total_kg" =>
				$load["total_kg"],

			"price_sum" =>
				$load["price_sum"],

			"taxes" =>
				$load["taxes"],

			"discount" =>
				$load["discount"],

			"price_total" =>
				$load["price_total"],

			"price_total_exchanged" =>
				$load["price_total_exchanged"],

			"destination" =>
				$load["destination"],

			"comment" =>
				$load["comment"],

			"status" =>
				$load["status"],

			"created_at" =>
				$load["created_at"]
		];
	}


	public function updateLoad(
		int $userId,
		int $companyId,
		int $loadId,
		array $data
	): void {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		if ($loadId <= 0) {
			throw new \InvalidArgumentException(
				"Load ID is required."
			);
		}

		$load =
			$this->repository->findById(
				$loadId,
				$companyId
			);

		if ($load === null) {
			throw new \Exception(
				"Load not found."
			);
		}

		$required = [
			"customer_id",
			"from_currency",
			"to_currency",
			"price_per_kg",
			"total_kg"
		];

		foreach ($required as $field) {
			if (
				!isset($data[$field]) ||
				$data[$field] === ""
			) {
				throw new \InvalidArgumentException(
					"Missing required field: {$field}"
				);
			}
		}

		$customerId =
			(int)$data["customer_id"];

		if ($customerId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid customer ID."
			);
		}

		$customer =
			$this->repository
				->findCustomerById(
					$customerId,
					$companyId
				);

		if ($customer === null) {
			throw new \Exception(
				"The selected customer does not exist or does not belong to this company."
			);
		}

		$fromCurrency =
			strtoupper(
				trim(
					(string)$data["from_currency"]
				)
			);

		$toCurrency =
			strtoupper(
				trim(
					(string)$data["to_currency"]
				)
			);

		if ($fromCurrency === '') {
			throw new \InvalidArgumentException(
				"From currency is required."
			);
		}

		if ($toCurrency === '') {
			throw new \InvalidArgumentException(
				"To currency is required."
			);
		}

		$pricePerKg =
			(float)$data["price_per_kg"];

		$totalKg =
			(float)$data["total_kg"];

		if ($pricePerKg <= 0) {
			throw new \InvalidArgumentException(
				"Price per kg must be greater than 0."
			);
		}

		if ($totalKg <= 0) {
			throw new \InvalidArgumentException(
				"Total kg must be greater than 0."
			);
		}

		$discount =
			(float)($data["discount"] ?? 0);

		$taxes =
			(float)($data["taxes"] ?? 0);

		$destination =
			trim(
				(string)(
					$data["destination"]
					?? ''
				)
			);

		$comment =
			trim(
				(string)(
					$data["comment"]
					?? ''
				)
			);

		/*
		* Recalculamos los totales en backend.
		*/
		$priceSum =
			$pricePerKg * $totalKg;

		$subtotal =
			$priceSum - $discount;

		$taxAmount =
			($subtotal * $taxes) / 100;

		$priceTotal =
			$subtotal + $taxAmount;

		$priceTotalExchanged =
			isset(
				$data["price_total_exchanged"]
			)
				? (float)
					$data["price_total_exchanged"]
				: $priceTotal;

		$products =
			$data["products"] ?? [];

		if (
			!is_array($products) ||
			empty($products)
		) {
			throw new \InvalidArgumentException(
				"At least one product is required."
			);
		}

		/*
		* Primero validamos todos los productos.
		* No modificamos la DB hasta saber que
		* todos son válidos.
		*/
		$normalizedProducts = [];

		foreach ($products as $product) {
			$productId =
				(int)(
					$product["product_id"]
					?? 0
				);

			if ($productId <= 0) {
				throw new \InvalidArgumentException(
					"Invalid product ID."
				);
			}

			if (
				!$this->repository
					->productBelongsToCompany(
						$productId,
						$companyId
					)
			) {
				throw new \Exception(
					"Product ID {$productId} does not belong to this company."
				);
			}

			$quantity =
				max(
					1,
					(int)(
						$product["quantity"]
						?? 1
					)
				);

			$productTotalKg =
				(float)(
					$product["total_kg"]
					?? 0
				);

			$totalKgPrice =
				(float)(
					$product[
						"total_kg_price"
					] ?? 0
				);

			$convertedPrice =
				isset(
					$product[
						"total_price_exchanged"
					]
				)
					? (float)
						$product[
							"total_price_exchanged"
						]
					: $totalKgPrice;

			$normalizedProducts[] = [
				"product_id" =>
					$productId,

				"quantity" =>
					$quantity,

				"total_kg" =>
					number_format(
						$productTotalKg,
						3,
						'.',
						''
					),

				"total_kg_price" =>
					number_format(
						$totalKgPrice,
						2,
						'.',
						''
					),

				"total_price_exchanged" =>
					number_format(
						$convertedPrice,
						2,
						'.',
						''
					)
			];
		}

		$this->repository->update(
			$loadId,
			$companyId,
			[
				"customer_id" =>
					$customerId,

				"from_currency" =>
					$fromCurrency,

				"to_currency" =>
					$toCurrency,

				"price_per_kg" =>
					number_format(
						$pricePerKg,
						2,
						'.',
						''
					),

				"total_kg" =>
					number_format(
						$totalKg,
						3,
						'.',
						''
					),

				"price_sum" =>
					number_format(
						$priceSum,
						2,
						'.',
						''
					),

				"taxes" =>
					number_format(
						$taxes,
						2,
						'.',
						''
					),

				"discount" =>
					number_format(
						$discount,
						2,
						'.',
						''
					),

				"price_total" =>
					number_format(
						$priceTotal,
						2,
						'.',
						''
					),

				"price_total_exchanged" =>
					number_format(
						$priceTotalExchanged,
						2,
						'.',
						''
					),

				"destination" =>
					$destination,

				"comment" =>
					$comment
			]
		);

		/*
		* Reemplazamos los productos asociados.
		*/
		$this->repository
			->deleteLoadedProductsByLoadId(
				$loadId
			);

		foreach ($normalizedProducts as $product) {
			$this->repository
				->createLoadedProduct([
					"load_id" =>
						$loadId,

					"product_id" =>
						$product["product_id"],

					"quantity" =>
						$product["quantity"],

					"total_kg" =>
						$product["total_kg"],

					"from_currency" =>
						$fromCurrency,

					"total_kg_price" =>
						$product[
							"total_kg_price"
						],

					"to_currency" =>
						$toCurrency,

					"total_price_exchanged" =>
						$product[
							"total_price_exchanged"
						],

					"create_by" =>
						$userId
				]);
		}
	}
}