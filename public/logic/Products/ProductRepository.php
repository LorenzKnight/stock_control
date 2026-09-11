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
}