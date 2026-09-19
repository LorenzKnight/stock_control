<?php

namespace App\Shippings;

class ShippingService
{
	private ShippingRepository $repository;

	public function __construct(
		ShippingRepository $repository
	) {
		$this->repository = $repository;
	}


	public function createShipping(
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

		$destination = trim(
			(string)($data["destination"] ?? '')
		);

		if ($destination === '') {
			throw new \InvalidArgumentException(
				"Destination is required."
			);
		}

		$shippingMethod =
			(int)($data["shipping_method"] ?? 1);

		$deliveryDate = trim(
			(string)($data["delivery_date"] ?? '')
		);

		$description = trim(
			(string)($data["description"] ?? '')
		);

		$status =
			(int)($data["status"] ?? 0);

		$shippingNo =
			$this->repository
				->getNextShippingNumber(
					$companyId
				);

		$shippingId =
			$this->repository->create([
				"shipping_no" =>
					$shippingNo,

				"company_id" =>
					$companyId,

				"shipping_img" =>
					null,

				"shipping_method" =>
					$shippingMethod,

				"destination" =>
					$destination,

				"delivery_date" =>
					$deliveryDate,

				"description" =>
					$description,

				"status" =>
					$status,

				"create_by" =>
					$userId,

				"created_at" =>
					date("Y-m-d H:i:s")
			]);

		return [
			"shipping_id" => $shippingId,
			"shipping_no" => $shippingNo,
			"qr_image" => $shippingNo . ".png"
		];
	}


	public function attachQrImage(
		int $shippingId,
		int $companyId,
		string $imageName
	): void {
		if ($shippingId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid shipping ID."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		$imageName = trim($imageName);

		if ($imageName === '') {
			throw new \InvalidArgumentException(
				"QR image name is required."
			);
		}

		$this->repository->updateQrImage(
			$shippingId,
			$companyId,
			$imageName
		);
	}

    public function updateShipping(
		int $shippingId,
		int $companyId,
		array $data
	): void {
		if ($shippingId <= 0) {
			throw new \InvalidArgumentException(
				"Missing shipping ID."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		$shipping =
			$this->repository->findById(
				$shippingId,
				$companyId
			);

		if ($shipping === null) {
			throw new \Exception(
				"Shipping not found."
			);
		}

		$shippingData = [
			"shipping_method" =>
				(int)($data["shipping_method"] ?? 1),

			"destination" =>
				trim(
					(string)($data["destination"] ?? '')
				),

			"delivery_date" =>
				trim(
					(string)($data["delivery_date"] ?? '')
				),

			"description" =>
				trim(
					(string)($data["description"] ?? '')
				),

			"status" =>
				(int)($data["status"] ?? 0)
		];

		$this->repository->update(
			$shippingId,
			$companyId,
			$shippingData
		);
	}


	public function getShippings(
		int $companyId,
		string $status = ''
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		$status = trim($status);

		$shippings =
			$this->repository->findShippings(
				$companyId,
				$status
			);

		if (empty($shippings)) {
			throw new \Exception(
				"No shippings available."
			);
		}

		return $shippings;
	}


	public function getShippingTracking(
		int $shippingId
	): array {
		if ($shippingId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid shipping ID."
			);
		}

		$tracking =
			$this->repository
				->findTrackingByShippingId(
					$shippingId
				);

		return [
			"all_tracking" => $tracking,
			"tracking" =>
				$tracking[0] ?? null
		];
	}


	public function buildProductSummary(
		array $loads
	): array {
		$summary = [];

		foreach ($loads as $load) {
			$products =
				$load["products"] ?? [];

			if (!is_array($products)) {
				continue;
			}

			foreach ($products as $product) {
				$productId =
					(int)(
						$product["product_id"]
						?? 0
					);

				if ($productId <= 0) {
					continue;
				}

				if (!isset($summary[$productId])) {
					$summary[$productId] = [
						"product_id" =>
							$productId,

						"name" =>
							$product["name"]
							?? '',

						"mark_name" =>
							$product["mark_name"]
							?? null,

						"model_name" =>
							$product["model_name"]
							?? null,

						"submodel_name" =>
							$product["submodel_name"]
							?? null,

						"image" =>
							$product["image"]
							?? '',

						"quantity" => 0,
						"total_price" => 0.0,
						"total_exchanged" => 0.0,
						"total_weight" => 0.0
					];
				}

				$summary[$productId]["quantity"] +=
					(int)(
						$product["quantity"]
						?? 0
					);

				$summary[$productId]["total_price"] +=
					(float)(
						$product["total_kg_price"]
						?? 0
					);

				$summary[$productId]["total_exchanged"] +=
					(float)(
						$product["total_price_exchanged"]
						?? 0
					);

				$summary[$productId]["total_weight"] +=
					(float)(
						$product["total_kg"]
						?? 0
					);
			}
		}

		return array_values($summary);
	}


	public function deleteShipping(
		int $shippingId,
		int $companyId
	): ?string {
		if ($shippingId <= 0) {
			throw new \InvalidArgumentException(
				"Shipping ID is required."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID is required."
			);
		}

		$shipping =
			$this->repository->findById(
				$shippingId,
				$companyId
			);

		if ($shipping === null) {
			throw new \Exception(
				"Shipping not found."
			);
		}

		$imageName = trim(
			(string)(
				$shipping["shipping_img"]
				?? ''
			)
		);

		$this->repository
			->deleteTrackingByShippingId(
				$shippingId
			);

		$this->repository->delete(
			$shippingId,
			$companyId
		);

		return $imageName !== ''
			? $imageName
			: null;
	}


	public function checkShipping(
		int $userId,
		int $companyId,
		int $shippingId,
		?float $latitude = null,
		?float $longitude = null,
		bool $checkOnly = false
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"Company ID not found for user."
			);
		}

		if ($shippingId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid shipping ID."
			);
		}

		$shipping =
			$this->repository->findById(
				$shippingId,
				$companyId
			);

		if ($shipping === null) {
			throw new \Exception(
				"Shipping not found."
			);
		}

		$currentStatus =
			(int)($shipping["status"] ?? 0);

		if ($currentStatus >= 3) {
			throw new \Exception(
				"Shipping already delivered."
			);
		}

		/*
		* La comprobación se hace también en el
		* scan real, no solamente en check_only.
		*/
		if (
			$this->repository
				->hasBeenScannedByUser(
					$shippingId,
					$userId
				)
		) {
			throw new \Exception(
				"Already checked by this user."
			);
		}

		if ($checkOnly) {
			return [
				"check_only" => true,
				"checkpoint" => null,
				"status_changed" => false,
				"notification_user_ids" => []
			];
		}

		$checkpointName =
			$this->repository
				->findLatestCheckpointLocation(
					$userId
				)
			?? "Scanned at checkpoint";

		$this->repository->createTracking([
			"shipping_id" =>
				$shippingId,

			"checkpoint_name" =>
				$checkpointName,

			/*
			* Preservamos el comportamiento anterior:
			* tracking guarda el estado previo al cambio.
			*/
			"status" =>
				$currentStatus,

			"scanned_by" =>
				$userId,

			"latitude" =>
				$latitude,

			"longitude" =>
				$longitude,

			"created_at" =>
				date("Y-m-d H:i:s")
		]);

		$statusChanged = false;
		$notificationUserIds = [];

		if ($currentStatus < 2) {
			$this->repository->updateStatus(
				$shippingId,
				$companyId,
				2
			);

			$statusChanged = true;

			$notificationUserIds =
				$this->repository
					->findStatusNoticeUserIds(
						$companyId
					);
		}

		return [
			"check_only" => false,
			"checkpoint" => $checkpointName,
			"status_changed" => $statusChanged,
			"notification_user_ids" =>
				$notificationUserIds
		];
	}
}