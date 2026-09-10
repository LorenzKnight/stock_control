<?php

namespace App\Storages;

class StorageRepository
{
	public function findSlots(
		int $companyId,
		string $search = '',
		?int $slotId = null
	): array {
		$where = [
			"company_id" => $companyId
		];

		if ($slotId !== null && $slotId > 0) {
			$where["slot_id"] = $slotId;
		} elseif ($search !== '') {
			$where["OR"] = [
				"slot_name ILIKE" => "%{$search}%"
			];
		}

		$result = \select_from(
			"slot",
			[
				"slot_id",
				"company_id",
				"slot_name",
				"slot_description",
				"max_capacity",
				"current_capacity",
				"status",
				"created_by",
				"created_at"
			],
			$where,
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		return $this->extractRows($result);
	}


	public function findProductsByName(
		int $companyId,
		string $search
	): array {
		$result = \select_from(
			"products",
			[
				"product_id",
				"product_image",
				"product_name",
				"product_year",
				"product_mark",
				"product_model",
				"product_sub_model",
				"price",
				"sale_unit_type",
				"weight_per_unit",
				"total_weight",
				"quantity",
				"min_quantity",
				"currency",
				"purpose"
			],
			[
				"company_id" => $companyId,
				"OR" => [
					"product_name ILIKE" => "%{$search}%"
				]
			],
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		return $this->extractRows($result);
	}


	public function findProductById(
		int $companyId,
		int $productId
	): ?array {
		$result = \select_from(
			"products",
			[
				"product_id",
				"company_id",
				"product_image",
				"product_name",
				"product_year",
				"product_mark",
				"product_model",
				"product_sub_model",
				"price",
				"sale_unit_type",
				"units_per_pack",
				"weight_per_unit",
				"total_weight",
				"quantity",
				"min_quantity",
				"currency",
				"purpose"
			],
			[
				"company_id" => $companyId,
				"product_id" => $productId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"StorageRepository expected an array response."
			);
		}

		$data = $result["data"] ?? null;

		return is_array($data)
			? $data
			: null;
	}


	public function findStoragesBySlotId(
		int $companyId,
		int $slotId
	): array {
		return $this->findStorages([
			"company_id" => $companyId,
			"slot_id" => $slotId
		]);
	}


	public function findStoragesByProductId(
		int $companyId,
		int $productId
	): array {
		return $this->findStorages([
			"company_id" => $companyId,
			"product_id" => $productId
		]);
	}


	public function findSlotById(
		int $companyId,
		int $slotId
	): ?array {
		$result = \select_from(
			"slot",
			[
				"slot_id",
				"slot_name"
			],
			[
				"company_id" => $companyId,
				"slot_id" => $slotId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"StorageRepository expected an array response."
			);
		}

		$data = $result["data"] ?? null;

		return is_array($data)
			? $data
			: null;
	}


	private function findStorages(
		array $where
	): array {
		$result = \select_from(
			"storage",
			[
				"storage_id",
				"company_id",
				"slot_id",
				"product_id",
				"quantity",
				"created_by",
				"created_at"
			],
			$where,
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		return $this->extractRows($result);
	}


	private function extractRows(
		array|string $result
	): array {
		if (!is_array($result)) {
			throw new \RuntimeException(
				"StorageRepository expected an array response."
			);
		}

		$data = $result["data"] ?? [];

		return is_array($data)
			? array_values($data)
			: [];
	}

    public function findCompanyIdByUserId(
		int $userId
	): ?int {
		$result = \select_from(
			"users",
			["company_id"],
			["user_id" => $userId],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"StorageRepository expected an array response."
			);
		}

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return null;
		}

		return (int)(
			$result["data"]["company_id"] ?? 0
		);
	}


	public function createStorage(
		array $data
	): void {
		$result = \insert_into(
			"storage",
			$data,
			[
				"id" => "storage_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Error saving storage data."
			);
		}
	}


	public function deleteStorage(
		int $storageId
	): void {
		$result = \delete_from(
			"storage",
			["storage_id" => $storageId],
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Error deleting old storage data."
			);
		}
	}
}