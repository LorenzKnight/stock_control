<?php

namespace App\Products;

class ProductRepository
{
	public function findProducts(
		int $companyId,
		array $filters = []
	): array {
		$where = [
			"company_id" => $companyId
		];

		$mark =
			$filters["mark"] ?? '';

		$model =
			$filters["model"] ?? '';

		$submodel =
			$filters["submodel"] ?? '';

		$purpose =
			$filters["purpose"] ?? '';

		$productId =
			$filters["product_id"] ?? '';

		$search =
			$filters["search"] ?? '';

		if (!empty($mark)) {
			$where["product_mark"] = $mark;
		}

		if (!empty($model)) {
			$where["product_model"] = $model;
		}

		if (!empty($submodel)) {
			$where["product_sub_model"] =
				$submodel;
		}

		if (!empty($purpose)) {
			$where["purpose"] = $purpose;
		}

		if (
			!empty($productId) &&
			is_numeric($productId)
		) {
			$where["product_id"] =
				(int)$productId;
		}

		if (!empty($search)) {
			$where["OR"] = [
				"product_name ILIKE" =>
					"%{$search}%",

				"hs_code ILIKE" =>
					"%{$search}%"
			];
		}

		$result = \select_from(
			"products",
			["*"],
			$where,
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ProductRepository expected an array response."
			);
		}

		/*
		 * Preservamos el comportamiento actual:
		 * get_products.php considera success=false
		 * como error al cargar productos.
		 */
		if (empty($result["success"])) {
			throw new \RuntimeException(
				"Error loading products."
			);
		}

		return array_values(
			$result["data"] ?? []
		);
	}


	public function findByBarcode(
		int $companyId,
		string $barcode
	): ?array {
		$result = \select_from(
			"products",
			["*"],
			[
				/*
				 * Preservamos el comportamiento
				 * actual: barcode usa hs_code.
				 */
				"hs_code" => $barcode,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ProductRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"]) &&
			is_array($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? '') ===
				"No records found"
		) {
			return null;
		}

		throw new \RuntimeException(
			"Error loading product."
		);
	}


	public function enrichProduct(
		array $product,
		int $companyId
	): ?array {
		return \mapProductRelations(
			$product,
			$companyId
		);
	}

    public function findExistingProduct(
		int $companyId,
		string $productName,
		int $productMark,
		int $productModel,
		int $productSubModel,
		int $productYear
	): ?array {
		$result = \select_from(
			"products",
			[
				"product_id",
				"quantity"
			],
			[
				"company_id" => $companyId,
				"product_name" => $productName,
				"product_mark" => $productMark,
				"product_model" => $productModel,
				"product_sub_model" => $productSubModel,
				"product_year" => $productYear
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ProductRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"]) &&
			is_array($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? '') === "No records found"
		) {
			return null;
		}

		throw new \RuntimeException(
			"Error checking existing product."
		);
	}


	public function create(
		array $data
	): int {
		$result = \insert_into(
			"products",
			$data,
			[
				"id" => "product_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				"Error saving product data."
			);
		}

		return (int)$result["id"];
	}


	public function updateQuantity(
		int $companyId,
		int $productId,
		int $quantity
	): void {
		$result = \update_table(
			"products",
			[
				"quantity" => $quantity,
				"updated_at" => date("Y-m-d H:i:s")
			],
			[
				"product_id" => $productId,
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
				"Error updating existing product quantity."
			);
		}
	}


	public function countCreatedByUser(
		int $companyId,
		int $userId
	): int {
		$result = \select_from(
			"products",
			[
				"COUNT(*) AS total"
			],
			[
				"company_id" => $companyId,
				"created_by" => $userId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Could not count products for onboarding."
			);
		}

		return (int)(
			$result["data"]["total"] ?? 0
		);
	}


	public function findProductOnboardingByUserId(
		int $userId
	): ?array {
		$result = \select_from(
			"user_onboarding",
			[
				"user_id",
				"product",
				"product_reward_seen"
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
				"ProductRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? '') ===
				"No records found"
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read onboarding product state."
		);
	}


	public function markProductOnboardingComplete(
		int $userId
	): bool {
		$result = \update_table(
			"user_onboarding",
			[
				"product" => true,
				"updated_at" =>
					date("Y-m-d H:i:s")
			],
			[
				"user_id" => $userId
			],
			[
				"return_type" => "array"
			]
		);

		return
			is_array($result) &&
			!empty($result["success"]);
	}


	public function createProductOnboarding(
		int $userId
	): bool {
		$result = \insert_into(
			"user_onboarding",
			[
				"user_id" => $userId,
				"product" => true,
				"product_reward_seen" => false,
				"created_at" =>
					date("Y-m-d H:i:s"),
				"updated_at" =>
					date("Y-m-d H:i:s")
			],
			[
				"return_type" => "array"
			]
		);

		return
			is_array($result) &&
			!empty($result["success"]);
	}
}