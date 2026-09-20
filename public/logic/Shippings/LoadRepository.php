<?php

namespace App\Shippings;

class LoadRepository
{
	public function findByShippingId(
		int $companyId,
		int $shippingId
	): array {
		$result = \select_from(
			"loads",
			[
				"load_id",
				"load_no",
				"customer_id",
				"from_currency",
				"to_currency",
				"price_per_kg",
				"total_kg",
				"price_sum",
				"taxes",
				"discount",
				"price_total",
				"price_total_exchanged",
				"destination",
				"status",
				"created_at"
			],
			[
				"company_id" => $companyId,
				"shippings_id" => $shippingId
			],
			[
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"LoadRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"]) &&
			is_array($result["data"])
		) {
			return array_values(
				$result["data"]
			);
		}

		if (
			($result["message"] ?? "") === "No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not read shipping loads."
		);
	}


	public function findCustomerById(
		int $customerId,
		int $companyId
	): ?array {
		$result = \select_from(
			"customers",
			[
				"customer_name",
				"customer_surname",
				"customer_phone",
				"customer_image",
				"customer_document_no"
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
				"LoadRepository expected an array response."
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
			"Could not read load customer."
		);
	}


    public function findLoadedProductsByLoadId(
		int $loadId
	): array {
		$result = \select_from(
			"loaded_products",
			[
				"product_id",
				"quantity",
				"total_kg",
				"from_currency",
				"total_kg_price",
				"to_currency",
				"total_price_exchanged"
			],
			[
				"load_id" => $loadId
			],
			[
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"LoadRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"]) &&
			is_array($result["data"])
		) {
			return array_values(
				$result["data"]
			);
		}

		if (
			($result["message"] ?? "") === "No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not read loaded products."
		);
	}


	public function findProductById(
		int $productId,
		?int $companyId = null
	): ?array {
		$where = [
			"product_id" => $productId
		];

		if (
			$companyId !== null &&
			$companyId > 0
		) {
			$where["company_id"] =
				$companyId;
		}

		$result = \select_from(
			"products",
			[
				"product_image",
				"product_name",
				"product_year",
				"product_mark",
				"product_model",
				"product_sub_model",
				"price",
				"weight_per_unit",
				"total_weight"
			],
			$where,
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"LoadRepository expected an array response."
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
			"Could not read loaded product information."
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
				"LoadRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			$name = trim(
				(string)(
					$result["data"]["category_name"]
					?? ''
				)
			);

			return $name !== ''
				? $name
				: null;
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
			"Could not read product category."
		);
	}


	public function findIdsByShippingId(
		int $companyId,
		int $shippingId
	): array {
		$result = \select_from(
			"loads",
			["load_id"],
			[
				"company_id" => $companyId,
				"shippings_id" => $shippingId
			],
			[
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"LoadRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"]) &&
			is_array($result["data"])
		) {
			return array_map(
				static fn(array $load): int =>
					(int)$load["load_id"],
				array_values($result["data"])
			);
		}

		if (
			($result["message"] ?? "") === "No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not read shipping load IDs."
		);
	}


	public function deleteLoadedProductsByLoadId(
		int $loadId
	): void {
		$result = \delete_from(
			"loaded_products",
			[
				"load_id" => $loadId
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
				"Failed to delete loaded products for load ID: {$loadId}"
			);
		}
	}


	public function deleteById(
		int $loadId,
		int $companyId
	): void {
		$result = \delete_from(
			"loads",
			[
				"load_id" => $loadId,
				"company_id" => $companyId
			],
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["count"])
		) {
			throw new \RuntimeException(
				"Failed to delete load ID: {$loadId}"
			);
		}
	}


	public function getNextLoadNumber(
		int $companyId
	): int {
		$startFrom =
			(int)($companyId . "40000");

		return \get_next_increment_value(
			"loads",
			"load_no",
			$companyId,
			$startFrom
		);
	}


	public function findShippingById(
		int $shippingId,
		int $companyId
	): ?array {
		$result = \select_from(
			"shippings",
			["shippings_id"],
			[
				"shippings_id" => $shippingId,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"LoadRepository expected an array response."
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
			"Could not read shipping data."
		);
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
				"LoadRepository expected an array response."
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
			"Could not validate loaded product."
		);
	}


	public function create(
		array $data
	): int {
		$result = \insert_into(
			"loads",
			$data,
			[
				"id" => "load_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				"Failed to create load record."
			);
		}

		return (int)$result["id"];
	}


	public function createLoadedProduct(
		array $data
	): void {
		$result = \insert_into(
			"loaded_products",
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
				"Error adding product to load."
			);
		}
	}

	public function findById(
		int $loadId,
		int $companyId
	): ?array {
		$result = \select_from(
			"loads",
			[
				"load_id",
				"load_no",
				"company_id",
				"shippings_id",
				"customer_id",
				"from_currency",
				"to_currency",
				"price_per_kg",
				"total_kg",
				"price_sum",
				"taxes",
				"discount",
				"price_total",
				"price_total_exchanged",
				"destination",
				"comment",
				"status",
				"created_at"
			],
			[
				"load_id" => $loadId,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"LoadRepository expected an array response."
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
			"Could not read load data."
		);
	}


	public function update(
		int $loadId,
		int $companyId,
		array $data
	): void {
		$result = \update_table(
			"loads",
			$data,
			[
				"load_id" => $loadId,
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
				"Failed to update load details."
			);
		}
	}
}