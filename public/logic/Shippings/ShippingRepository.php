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

    public function findById(
		int $shippingId,
		int $companyId
	): ?array {
		$result = \select_from(
			"shippings",
			[
				"shippings_id",
				"company_id"
			],
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
				"ShippingRepository expected an array response."
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
			"Could not read shipping data."
		);
	}


	public function update(
		int $shippingId,
		int $companyId,
		array $data
	): void {
		$result = \update_table(
			"shippings",
			$data,
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
				"Database update failed."
			);
		}
	}
}