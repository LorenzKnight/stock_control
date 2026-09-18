<?php

namespace App\Shippings;

class ShippingRepository
{
	public function getNextShippingNumber(
		int $companyId
	): int {
		$startFrom =
			(int)($companyId . "30000");

		return \get_next_increment_value(
			"shippings",
			"shipping_no",
			$companyId,
			$startFrom
		);
	}


	public function create(
		array $data
	): int {
		$result = \insert_into(
			"shippings",
			$data,
			[
				"id" => "shippings_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				"Error saving shipping data."
			);
		}

		return (int)$result["id"];
	}


	public function updateQrImage(
		int $shippingId,
		int $companyId,
		string $imageName
	): void {
		$result = \update_table(
			"shippings",
			[
				"shipping_img" => $imageName
			],
			[
				"shippings_id" => $shippingId,
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
				"Failed to save shipping QR image."
			);
		}
	}
}