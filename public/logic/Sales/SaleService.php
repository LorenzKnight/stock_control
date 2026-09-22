<?php

namespace App\Sales;

use App\Inventory\InventoryService;

class SaleService
{
	private SaleRepository $repository;
	private InventoryService $inventoryService;

	public function __construct(
		SaleRepository $repository,
		InventoryService $inventoryService
	) {
		$this->repository =
			$repository;

		$this->inventoryService =
			$inventoryService;
	}


	public function createSale(
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

		$required = [
			"customer_id",
			"price_sum",
			"initial",
			"delivery_date",
			"remaining",
			"interest",
			"installments_month",
			"no_installments",
			"payment_date",
			"due"
		];

		foreach ($required as $field) {
			if (!isset($data[$field])) {
				throw new \InvalidArgumentException(
					"Missing required field: {$field}"
				);
			}
		}

		$customerId =
			(int)$data["customer_id"];

		if ($customerId <= 0) {
			throw new \InvalidArgumentException(
				"A customer is required to create a sale."
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

		$deliveryTimestamp =
			strtotime(
				(string)$data["delivery_date"]
			);

		if ($deliveryTimestamp === false) {
			throw new \InvalidArgumentException(
				"Invalid delivery_date."
			);
		}

		$paymentTimestamp =
			strtotime(
				(string)$data["payment_date"]
			);

		if ($paymentTimestamp === false) {
			throw new \InvalidArgumentException(
				"Invalid payment_date."
			);
		}

		$currency =
			strtoupper(
				trim(
					(string)(
						$data["currency"]
						?? "USD"
					)
				)
			);

		if ($currency === '') {
			$currency = "USD";
		}

		$priceSum =
			(float)$data["price_sum"];

		$initial =
			(float)$data["initial"];

		$remaining =
			(float)$data["remaining"];

		$interest =
			(int)$data["interest"];

		$installmentsMonth =
			(int)$data["installments_month"];

		$noInstallments =
			(int)$data["no_installments"];

		$due =
			(float)$data["due"];

		/*
		 * Validamos todos los productos ANTES
		 * de modificar ventas o inventario.
		 */
		$normalizedProducts = [];
		$sumFromProducts = 0.0;

		foreach ($products as $product) {
			$productId =
				(int)(
					$product["product_id"]
					?? 0
				);

			if ($productId <= 0) {
				throw new \InvalidArgumentException(
					"Invalid product_id in products array."
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

			$price =
				(float)(
					$product["price"]
					?? 0
				);

			$discount =
				(float)(
					$product["discount"]
					?? 0
				);

			if ($discount < 0) {
				$discount = 0;
			}

			$lineTotal =
				isset($product["total"])
					? (float)$product["total"]
					: $price * $quantity;

			$sumFromProducts +=
				$lineTotal;

			$normalizedProducts[] = [
				"product_id" =>
					$productId,

				"quantity" =>
					$quantity,

				"price" =>
					$price,

				"discount" =>
					$discount,

				"total" =>
					$lineTotal
			];
		}

		$orderNo =
			$this->repository
				->getNextOrderNumber(
					$companyId
				);

		$saleId =
			$this->repository->create([
				"ord_no" =>
					$orderNo,

				"customer_id" =>
					$customerId,

				"company_id" =>
					$companyId,

				"currency" =>
					$currency,

				"price_sum" =>
					number_format(
						$priceSum,
						2,
						'.',
						''
					),

				"initial" =>
					number_format(
						$initial,
						2,
						'.',
						''
					),

				"delivery_date" =>
					date(
						'Y-m-d H:i:s',
						$deliveryTimestamp
					),

				"remaining" =>
					number_format(
						$remaining,
						2,
						'.',
						''
					),

				"interest" =>
					$interest,

				"installments_month" =>
					$installmentsMonth,

				"no_installments" =>
					$noInstallments,

				"payment_date" =>
					date(
						'Y-m-d H:i:s',
						$paymentTimestamp
					),

				"due" =>
					number_format(
						$due,
						2,
						'.',
						''
					),

				"status" => 1,

				"create_by" =>
					$userId
			]);

		$lowStockAlerts = [];

		foreach (
			$normalizedProducts
			as $product
		) {
			$stockResult =
				$this->inventoryService
					->consumeStockForSale(
						$product[
							"product_id"
						],
						$product[
							"quantity"
						]
					);

			$this->repository
				->createPurchasedProduct([
					"sales_id" =>
						$saleId,

					"customer_id" =>
						$customerId,

					"product_id" =>
						$product[
							"product_id"
						],

					"quantity" =>
						$product[
							"quantity"
						],

					"price" =>
						number_format(
							$product["price"],
							2,
							'.',
							''
						),

					"discount" =>
						number_format(
							$product[
								"discount"
							],
							2,
							'.',
							''
						),

					"total" =>
						number_format(
							$product["total"],
							2,
							'.',
							''
						),

					"create_by" =>
						$userId
				]);

			$minQuantity =
				$stockResult[
					"min_quantity"
				] ?? null;

			$newStock =
				(int)(
					$stockResult[
						"new_stock"
					] ?? 0
				);

			if (
				$minQuantity !== null &&
				$newStock ===
					(int)$minQuantity
			) {
				$lowStockAlerts[] = [
					"product_id" =>
						(int)$stockResult[
							"product_id"
						],

					"product_name" =>
						(string)$stockResult[
							"product_name"
						],

					"new_stock" =>
						$newStock
				];
			}
		}

		return [
			"sale_id" =>
				$saleId,

			"order_no" =>
				$orderNo,

			"sum_mismatch" =>
				abs(
					$sumFromProducts -
					$priceSum
				) > 0.01,

			"products_sum" =>
				$sumFromProducts,

			"low_stock_alerts" =>
				$lowStockAlerts
		];
	}


	public function processSaleOnboarding(
		int $userId
	): bool {
		if ($userId <= 0) {
			return false;
		}

		try {
			$onboarding =
				$this->repository
					->findOnboardingByUserId(
						$userId
					);

			$now =
				date("Y-m-d H:i:s");

			if ($onboarding === null) {
				$this->repository
					->createOnboarding([
						"user_id" =>
							$userId,

						"sale" => true,

						"sale_reward_seen" =>
							false,

						"onboarding_completed" =>
							false,

						"created_at" =>
							$now,

						"updated_at" =>
							$now
					]);

				return true;
			}

			$saleCompleted =
				$this->isDatabaseTrue(
					$onboarding["sale"]
					?? false
				);

			$rewardAlreadySeen =
				$this->isDatabaseTrue(
					$onboarding[
						"sale_reward_seen"
					] ?? false
				);

			$companyCompleted =
				$this->isDatabaseTrue(
					$onboarding["company"]
					?? false
				);

			$productCompleted =
				$this->isDatabaseTrue(
					$onboarding["product"]
					?? false
				);

			$clientCompleted =
				$this->isDatabaseTrue(
					$onboarding["client"]
					?? false
				);

			$showReward =
				!$saleCompleted &&
				!$rewardAlreadySeen;

			if (!$saleCompleted) {
				$this->repository
					->updateOnboarding(
						$userId,
						[
							"sale" =>
								true,

							"onboarding_completed" =>
								$companyCompleted &&
								$productCompleted &&
								$clientCompleted,

							"updated_at" =>
								$now
						]
					);
			}

			return $showReward;

		} catch (\Throwable $e) {
			error_log(
				"Could not process sale onboarding for user_id {$userId}: " .
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


    public function getSales(
		int $companyId,
		string $search = ''
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		$sales =
			$this->repository
				->findSalesByCompanyId(
					$companyId
				);

		if (empty($sales)) {
			throw new \Exception(
				"No sales available."
			);
		}

		$search =
			strtolower(
				trim($search)
			);

		$filterBySearch =
			$search !== '';

		$documentTypes =
			\GlobalArrays::documentTypes();

		$salesData = [];

		foreach ($sales as $sale) {
			$customerId =
				(int)(
					$sale["customer_id"]
					?? 0
				);

			$customer =
				$this->repository
					->findCustomerDetailsById(
						$customerId,
						$companyId
					)
				?? [];

			$customerFullName =
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
				);

			$documentNo =
				(string)(
					$customer[
						"customer_document_no"
					] ?? ''
				);

			/*
			* Aplicamos búsqueda antes de cargar
			* productos y pagos innecesariamente.
			*/
			if (
				$filterBySearch &&
				strpos(
					strtolower(
						$customerFullName
					),
					$search
				) === false &&
				strpos(
					strtolower(
						$documentNo
					),
					$search
				) === false &&
				strpos(
					strtolower(
						(string)(
							$sale["ord_no"]
							?? ''
						)
					),
					$search
				) === false
			) {
				continue;
			}

			$purchasedProducts =
				$this->repository
					->findPurchasedProductsBySaleId(
						(int)$sale["sales_id"]
					);

			$productsData = [];

			foreach (
				$purchasedProducts
					as $purchasedProduct
			) {
				$productId =
					(int)(
						$purchasedProduct[
							"product_id"
						] ?? 0
					);

				$product =
					$this->repository
						->findProductDetailsById(
							$productId,
							$companyId
						)
					?? [];

				$markName = null;
				$modelName = null;
				$submodelName = null;

				$productMark =
					(int)(
						$product[
							"product_mark"
						] ?? 0
					);

				$productModel =
					(int)(
						$product[
							"product_model"
						] ?? 0
					);

				$productSubModel =
					(int)(
						$product[
							"product_sub_model"
						] ?? 0
					);

				if ($productMark > 0) {
					$markName =
						$this->repository
							->findCategoryNameById(
								$productMark
							);
				}

				if ($productModel > 0) {
					$modelName =
						$this->repository
							->findCategoryNameById(
								$productModel
							);
				}

				if ($productSubModel > 0) {
					$submodelName =
						$this->repository
							->findCategoryNameById(
								$productSubModel
							);
				}

				$productsData[] = [
					"product_id" =>
						$purchasedProduct[
							"product_id"
						] ?? '',

					"sale_unit_type" =>
						$product[
							"sale_unit_type"
						] ?? "1",

					"units_per_pack" =>
						$product[
							"units_per_pack"
						] ?? 1,

					"name" =>
						$product[
							"product_name"
						] ?? '',

					"year" =>
						$product[
							"product_year"
						] ?? '',

					"image" =>
						$product[
							"product_image"
						] ?? '',

					"mark_name" =>
						$markName,

					"model_name" =>
						$modelName,

					"submodel_name" =>
						$submodelName,

					"quantity" =>
						$purchasedProduct[
							"quantity"
						] ?? 1,

					/*
					* Preservamos comportamiento
					* actual de get_sales.php:
					* usa el precio actual del producto.
					*/
					"price" =>
						$product["price"]
						?? 0,

					"discount" =>
						$purchasedProduct[
							"discount"
						] ?? 0,

					"weight_per_unit" =>
						$product[
							"weight_per_unit"
						] ?? 0,

					"total_weight" =>
						$product[
							"total_weight"
						] ?? 0,

					"total" =>
						$purchasedProduct[
							"total"
						] ?? 0
				];
			}

			$docTypeId =
				$customer[
					"customer_document_type"
				] ?? null;

			$paymentCount =
				$this->repository
					->countPaymentsForSale(
						(int)$sale["sales_id"]
					);

			$salesData[] = [
				"sales_id" =>
					$sale["sales_id"],

				"ord_no" =>
					$sale["ord_no"],

				"price_sum" =>
					$sale["price_sum"],

				"initial" =>
					$sale["initial"],

				"delivery_date" =>
					date(
						"Y-m-d",
						strtotime(
							(string)$sale[
								"delivery_date"
							]
						)
					),

				"remaining" =>
					$sale["remaining"],

				"interest" =>
					$sale["interest"],

				"total_interest" =>
					(
						(float)$sale[
							"price_sum"
						] *
						(float)$sale[
							"interest"
						]
					) / 100,

				"installments_month" =>
					$sale[
						"installments_month"
					],

				"no_installments" =>
					$sale[
						"no_installments"
					],

				"payment_date" =>
					date(
						"Y-m-d",
						strtotime(
							(string)$sale[
								"payment_date"
							]
						)
					),

				"due" =>
					$sale["due"],

				"customer" => [
					"customer_id" =>
						$customerId,

					"full_name" =>
						$customerFullName,

					"document_type" =>
						$docTypeId !== null
							? (
								$documentTypes[
									(int)$docTypeId
								] ?? ''
							)
							: '',

					"document_no" =>
						$documentNo,

					"phone" =>
						$customer[
							"customer_phone"
						] ?? '',

					"image" =>
						$customer[
							"customer_image"
						] ?? ''
				],

				"products" =>
					$productsData,

				"payments" =>
					$paymentCount
			];
		}

		return $salesData;
	}
}