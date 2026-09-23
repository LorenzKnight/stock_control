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


	public function findSalesByCompanyId(
		int $companyId
	): array {
		$result = \select_from(
			"sales",
			[
				"sales_id",
				"ord_no",
				"customer_id",
				"price_sum",
				"initial",
				"delivery_date",
				"currency",
				"remaining",
				"interest",
				"installments_month",
				"no_installments",
				"payment_date",
				"due",
				"created_at"
			],
			[
				"company_id" => $companyId
			],
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
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
			isset($result["data"]) &&
			is_array($result["data"])
		) {
			return array_values(
				$result["data"]
			);
		}

		if (
			($result["message"] ?? "") ===
				"No records found"
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not load sales."
		);
	}


	public function findCustomerDetailsById(
		int $customerId,
		int $companyId
	): ?array {
		$result = \select_from(
			"customers",
			[
				"customer_name",
				"customer_surname",
				"customer_phone",
				"customer_document_type",
				"customer_document_no",
				"customer_image"
			],
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
			($result["message"] ?? "") ===
				"No records found"
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not load sale customer."
		);
	}


	public function findPurchasedProductsBySaleId(
		int $saleId
	): array {
		$result = \select_from(
			"purchased_products",
			[
				"product_id",
				"quantity",
				"price",
				"discount",
				"total"
			],
			[
				"sales_id" => $saleId
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
			isset($result["data"]) &&
			is_array($result["data"])
		) {
			return array_values(
				$result["data"]
			);
		}

		if (
			($result["message"] ?? "") ===
				"No records found"
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not load purchased products."
		);
	}


	public function findProductDetailsById(
		int $productId,
		int $companyId
	): ?array {
		$result = \select_from(
			"products",
			[
				"sale_unit_type",
				"units_per_pack",
				"weight_per_unit",
				"total_weight",
				"product_image",
				"product_name",
				"product_year",
				"product_mark",
				"product_model",
				"product_sub_model",
				"price"
			],
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
			return $result["data"];
		}

		if (
			($result["message"] ?? "") ===
				"No records found"
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not load sale product."
		);
	}


	public function findCategoryNameById(
		int $categoryId
	): ?string {
		$result = \select_from(
			"category",
			["category_name"],
			[
				"category_id" => $categoryId
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
			return
				(string)(
					$result["data"]["category_name"]
					?? ''
				);
		}

		if (
			($result["message"] ?? "") ===
				"No records found"
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not load sale category."
		);
	}


	public function countPaymentsForSale(
		int $saleId
	): int {
		$result = \select_from(
			"payments",
			["ord_no"],
			[
				"sales_id" => $saleId
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

		if (!empty($result["success"])) {
			if (isset($result["count"])) {
				return (int)$result["count"];
			}

			return is_array(
				$result["data"] ?? null
			)
				? count($result["data"])
				: 0;
		}

		if (
			($result["message"] ?? "") ===
				"No records found"
		) {
			return 0;
		}

		throw new \RuntimeException(
			"Could not count sale payments."
		);
	}


	public function findSaleForUpdate(
		int $saleId,
		int $companyId
	): ?array {
		$result = \select_from(
			"sales",
			[
				"sales_id"
			],
			[
				"sales_id" => $saleId,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
				"for_update" => true,
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
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not verify sale."
		);
	}


	public function hasInterestEarnings(
		int $saleId
	): bool {
		$result = \select_from(
			"interest_earnings",
			[
				"earnings_id"
			],
			[
				"sales_id" => $saleId
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
			"Unable to verify sale financial records."
		);
	}


	public function updateSale(
		int $saleId,
		int $companyId,
		array $data
	): void {
		$result = \update_table(
			"sales",
			$data,
			[
				"sales_id" => $saleId,
				"company_id" => $companyId
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
				"Failed to update sale. " .
				(
					is_array($result)
						? (
							$result["message"]
							?? "Unknown error."
						)
						: "Invalid database response."
				)
			);
		}
	}


	public function deletePurchasedProducts(
		int $saleId
	): void {
		$result = \delete_from(
			"purchased_products",
			[
				"sales_id" => $saleId
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

		if (!empty($result["success"])) {
			return;
		}

		/*
		* Preservamos el comportamiento actual:
		* una venta sin purchased_products anteriores
		* no debe hacer fallar el UPDATE.
		*/
		if (
			stripos(
				(string)(
					$result["message"]
					?? ''
				),
				'No records deleted'
			) !== false
		) {
			return;
		}

		throw new \RuntimeException(
			"Failed to delete old products. " .
			(
				$result["message"]
				?? "Unknown error."
			)
		);
	}


	public function findSaleForDelete(
		int $saleId,
		int $companyId
	): ?array {
		$result = \select_from(
			"sales",
			[
				"sales_id"
			],
			[
				"sales_id" => $saleId,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
				"for_update" => true,
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
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not verify sale."
		);
	}


	public function deleteSale(
		int $saleId,
		int $companyId
	): void {
		$result = \delete_from(
			"sales",
			[
				"sales_id" => $saleId,
				"company_id" => $companyId
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
				"Failed to delete sale."
			);
		}

		if (
			(int)(
				$result["count"]
				?? 0
			) !== 1
		) {
			throw new \RuntimeException(
				"Sale was not deleted."
			);
		}
	}
}