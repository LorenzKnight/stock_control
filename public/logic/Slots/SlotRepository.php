<?php

namespace App\Slots;

class SlotRepository
{
	public function findSlots(
		int $companyId,
		string $search = '',
		?int $slotId = null
	): array {
		$where = [
			"company_id" => $companyId
		];

		if (
			$slotId !== null &&
			$slotId > 0
		) {
			$where["slot_id"] = $slotId;
		}

		if ($search !== '') {
			$where["OR"] = [
				"slot_name ILIKE" =>
					"%{$search}%"
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
				"status"
			],
			$where,
			[
				"order_by" => "slot_name",
				"order_direction" => "ASC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"SlotRepository expected an array response."
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
			($result["message"] ?? '') ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return [];
		}

		throw new \RuntimeException(
			"Error loading slot data."
		);
	}
}