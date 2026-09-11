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

    public function findIdByName(
        int $companyId,
        string $slotName
    ): ?int {
        $result = \select_from(
            "slot",
            ["slot_id"],
            [
                "company_id" => $companyId,
                "slot_name" => $slotName
            ],
            [
                "fetch_first" => true,
                "return_type" => "array"
            ]
        );

        if (!is_array($result)) {
            throw new \RuntimeException(
                "SlotRepository expected an array response."
            );
        }

        $slotId =
            (int)($result["data"]["slot_id"] ?? 0);

        return $slotId > 0
            ? $slotId
            : null;
    }


    public function create(
        array $data
    ): int {
        $result = \insert_into(
            "slot",
            $data,
            [
                "id" => "slot_id",
                "return_type" => "array"
            ]
        );

        if (
            !is_array($result) ||
            empty($result["success"])
        ) {
            throw new \RuntimeException(
                "Error saving slot data."
            );
        }

        $slotId =
            (int)($result["id"] ?? 0);

        if ($slotId <= 0) {
            throw new \RuntimeException(
                "Slot ID was not returned."
            );
        }

        return $slotId;
    }

    public function update(
		int $companyId,
		int $slotId,
		array $data
	): void {
		$result = \update_table(
			"slot",
			$data,
			[
				"slot_id" => $slotId,
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
				"Update failed."
			);
		}
	}

	public function delete(
		int $companyId,
		int $slotId
	): void {
		$result = \delete_from(
			"slot",
			[
				"slot_id" => $slotId,
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
				"Failed to delete slot ID: {$slotId}"
			);
		}
	}
}