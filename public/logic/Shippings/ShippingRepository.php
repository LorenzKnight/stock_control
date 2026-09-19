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
				"company_id",
				"shipping_img"
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


	public function findShippings(
		int $companyId,
		string $status = ''
	): array {
		$where = [
			"company_id" => $companyId
		];

		// Conservamos exactamente el comportamiento actual.
		if (!empty($status)) {
			$where["status"] = $status;
		}

		$result = \select_from(
			"shippings",
			[
				"shippings_id",
				"shipping_no",
				"company_id",
				"shipping_img",
				"shipping_method",
				"destination",
				"delivery_date",
				"description",
				"status",
				"created_at"
			],
			$where,
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
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
			"Could not read shippings."
		);
	}


	public function findTrackingByShippingId(
		int $shippingId
	): array {
		$result = \select_from(
			"shipping_tracking",
			[
				"tracking_id",
				"checkpoint_name",
				"status",
				"scanned_by",
				"latitude",
				"longitude",
				"created_at"
			],
			[
				"shipping_id" => $shippingId
			],
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
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
			"Could not read shipping tracking."
		);
	}


	public function deleteTrackingByShippingId(
		int $shippingId
	): void {
		$result = \delete_from(
			"shipping_tracking",
			[
				"shipping_id" => $shippingId
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
				"Failed to delete shipping tracking."
			);
		}
	}


	public function delete(
		int $shippingId,
		int $companyId
	): void {
		$result = \delete_from(
			"shippings",
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
			empty($result["success"]) ||
			empty($result["count"])
		) {
			throw new \RuntimeException(
				"Failed to delete shipping."
			);
		}
	}
}