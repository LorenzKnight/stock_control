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
}