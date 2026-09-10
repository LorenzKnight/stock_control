<?php

namespace App\Storages;

class StorageService
{
	private StorageRepository $repository;

	public function __construct(
		StorageRepository $repository
	) {
		$this->repository = $repository;
	}


	public function getStorages(
		int $companyId,
		string $search = '',
		int $slotIdFilter = 0
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access: company not found."
			);
		}

		$search = trim($search);

		$filterBySearch = $search !== '';
		$filterBySlotId = $slotIdFilter > 0;

		if (
			!$filterBySearch &&
			!$filterBySlotId
		) {
			throw new \Exception(
				"No data available."
			);
		}

		$slotData = $this->repository->findSlots(
			$companyId,
			$filterBySearch ? $search : '',
			$filterBySlotId
				? $slotIdFilter
				: null
		);

		$productData = [];
		$allStorages = [];
		$seenStorageIds = [];
		$productMap = [];

		if ($filterBySearch) {
			$rawProducts =
				$this->repository->findProductsByName(
					$companyId,
					$search
				);

			foreach ($rawProducts as $productRow) {
				$productId =
					(int)($productRow["product_id"] ?? 0);

				if ($productId <= 0) {
					continue;
				}

				$productInfo =
					$this->buildProductInfo(
						$productId,
						$companyId
					);

				if ($productInfo !== null) {
					$productData[] = $productInfo;
				}
			}
		}

		foreach ($productData as $product) {
			$productId =
				(int)($product["product_id"] ?? 0);

			if ($productId > 0) {
				$productMap[(string)$productId] =
					$product;
			}
		}

		foreach ($slotData as $slot) {
			$slotId =
				(int)($slot["slot_id"] ?? 0);

			if ($slotId <= 0) {
				continue;
			}

			$storageRows =
				$this->repository
					->findStoragesBySlotId(
						$companyId,
						$slotId
					);

			$this->appendStorages(
				$storageRows,
				$companyId,
				$allStorages,
				$seenStorageIds,
				$productMap
			);
		}

		if (
			$filterBySlotId &&
			!empty($allStorages)
		) {
			foreach ($allStorages as $storageRow) {
				$product =
					$storageRow["product"] ?? null;

				if (
					is_array($product) &&
					!empty($product["product_id"])
				) {
					$productMap[
						(string)$product["product_id"]
					] = $product;
				}
			}

			$productData =
				array_values($productMap);
		}

		if (
			$filterBySearch &&
			!empty($productData)
		) {
			foreach ($productData as $product) {
				$productId =
					(int)($product["product_id"] ?? 0);

				if ($productId <= 0) {
					continue;
				}

				$storageRows =
					$this->repository
						->findStoragesByProductId(
							$companyId,
							$productId
						);

				$this->appendStorages(
					$storageRows,
					$companyId,
					$allStorages,
					$seenStorageIds,
					$productMap
				);
			}
		}

		if (
			empty($allStorages) &&
			empty($slotData) &&
			empty($productData)
		) {
			throw new \Exception(
				"No data available."
			);
		}

		return [
			"slots" => $slotData,
			"products" => $productData,
			"storages" => $allStorages
		];
	}


	private function buildProductInfo(
		int $productId,
		int $companyId
	): ?array {
		$product =
			$this->repository->findProductById(
				$companyId,
				$productId
			);

		if ($product === null) {
			return null;
		}

		$product =
			\mapProductRelations(
				$product,
				$companyId
			);

		if ($product === null) {
			return null;
		}

		$slots = [];
		$slotIds = [];
		$slotNames = [];
		$seenSlotIds = [];

		$storageRows =
			$this->repository
				->findStoragesByProductId(
					$companyId,
					$productId
				);

		foreach ($storageRows as $storageRow) {
			$slotId =
				(int)($storageRow["slot_id"] ?? 0);

			if (
				$slotId <= 0 ||
				isset($seenSlotIds[(string)$slotId])
			) {
				continue;
			}

			$slot =
				$this->repository->findSlotById(
					$companyId,
					$slotId
				);

			if ($slot === null) {
				continue;
			}

			$slots[] = [
				"slot_id" =>
					$slot["slot_id"] ?? null,
				"slot_name" =>
					$slot["slot_name"] ?? null
			];

			$slotIds[] =
				$slot["slot_id"] ?? null;

			$slotNames[] =
				$slot["slot_name"] ?? null;

			$seenSlotIds[(string)$slotId] =
				true;
		}

		return [
			"product_id" =>
				$product["product_id"] ?? null,

			"product_name" =>
				$product["product_name"] ?? '',

			"product_year" =>
				$product["product_year"] ?? '',

			"product_image" =>
				$product["product_image"] ?? '',

			"product_mark" =>
				$product["product_mark"] ?? null,

			"product_model" =>
				$product["product_model"] ?? null,

			"product_sub_model" =>
				$product["product_sub_model"] ?? null,

			"mark_name" =>
				$product["mark_name"]
				?? \tr(
					"uncategorized",
					"Uncategorized"
				),

			"model_name" =>
				$product["model_name"]
				?? \tr(
					"no_model",
					"No model assigned"
				),

			"submodel_name" =>
				$product["submodel_name"]
				?? \tr(
					"no_submodel",
					"No submodel assigned"
				),

			"purpose" =>
				$product["purpose"] ?? '',

			"purpose_text" =>
				$product["purpose_text"]
				?? \tr(
					"no_purpose",
					"No purpose assigned"
				),

			"price" =>
				$product["price"] ?? 0,

			"sale_unit_type" =>
				$product["sale_unit_type"] ?? '',

			"units_per_pack" =>
				$product["units_per_pack"] ?? 0,

			"weight_per_unit" =>
				(float)(
					$product["weight_per_unit"] ?? 0
				),

			"total_weight" =>
				(float)(
					$product["total_weight"] ?? 0
				),

			"quantity" =>
				$product["quantity"] ?? 0,

			"min_quantity" =>
				$product["min_quantity"] ?? 0,

			"currency" =>
				$product["currency"] ?? '',

			"slot_id" =>
				count($slotIds) === 1
					? $slotIds[0]
					: null,

			"slot_name" =>
				count($slotNames) === 1
					? $slotNames[0]
					: null,

			"slot_ids" => $slotIds,
			"slot_names" => $slotNames,
			"slots" => $slots
		];
	}


	private function appendStorages(
		array $storageRows,
		int $companyId,
		array &$allStorages,
		array &$seenStorageIds,
		array &$productMap
	): void {
		foreach ($storageRows as $storageRow) {
			$storageId =
				(int)($storageRow["storage_id"] ?? 0);

			if (
				$storageId <= 0 ||
				isset(
					$seenStorageIds[
						(string)$storageId
					]
				)
			) {
				continue;
			}

			$productId =
				(int)($storageRow["product_id"] ?? 0);

			$productInfo =
				$productMap[(string)$productId]
				?? null;

			if (
				$productInfo === null &&
				$productId > 0
			) {
				$productInfo =
					$this->buildProductInfo(
						$productId,
						$companyId
					);

				if ($productInfo !== null) {
					$productMap[
						(string)$productId
					] = $productInfo;
				}
			}

			$allStorages[] = [
				"storage_id" =>
					$storageRow["storage_id"] ?? null,

				"company_id" =>
					$storageRow["company_id"] ?? null,

				"slot_id" =>
					$storageRow["slot_id"] ?? null,

				"product_id" =>
					$storageRow["product_id"] ?? null,

				"quantity" =>
					$storageRow["quantity"] ?? null,

				"created_by" =>
					$storageRow["created_by"] ?? null,

				"created_at" =>
					$storageRow["created_at"] ?? null,

				"product" => $productInfo
			];

			$seenStorageIds[
				(string)$storageId
			] = true;
		}
	}

    public function saveStorageSelection(
		int $userId,
		int $slotId,
		array $productIds
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access."
			);
		}

		if ($slotId <= 0) {
			throw new \InvalidArgumentException(
				"Slot is required."
			);
		}

		$companyId =
			$this->repository
				->findCompanyIdByUserId($userId);

		if ($companyId === null) {
			throw new \Exception(
				"User data not found."
			);
		}

		if ($companyId <= 0) {
			throw new \Exception(
				"Invalid company."
			);
		}

		$newSelection = [];

		foreach ($productIds as $productIdRaw) {
			$productId = (int)$productIdRaw;

			if ($productId <= 0) {
				continue;
			}

			$newSelection[$productId] = true;
		}

		if (empty($newSelection)) {
			throw new \InvalidArgumentException(
				"At least one product is required."
			);
		}

		$currentStorage =
			$this->repository
				->findStoragesBySlotId(
					$companyId,
					$slotId
				);

		$currentMap = [];

		foreach ($currentStorage as $row) {
			$currentProductId =
				(int)($row["product_id"] ?? 0);

			$currentStorageId =
				(int)($row["storage_id"] ?? 0);

			if (
				$currentProductId > 0 &&
				$currentStorageId > 0
			) {
				$currentMap[$currentProductId] = [
					"storage_id" =>
						$currentStorageId
				];
			}
		}

		$insertedCount = 0;
		$deletedCount = 0;

		foreach (
			$newSelection as $productId => $selected
		) {
			if (isset($currentMap[$productId])) {
				unset($currentMap[$productId]);
				continue;
			}

			$this->repository->createStorage([
				"company_id" => $companyId,
				"slot_id" => $slotId,
				"product_id" => $productId,
				"created_by" => $userId,
				"created_at" => date(
					"Y-m-d H:i:s"
				)
			]);

			$insertedCount++;
		}

		foreach ($currentMap as $row) {
			$storageId = $row["storage_id"];

			$this->repository
				->deleteStorage($storageId);

			$deletedCount++;
		}

		return [
			"inserted_count" => $insertedCount,
			"deleted_count" => $deletedCount
		];
	}
}