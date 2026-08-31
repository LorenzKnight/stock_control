<?php

namespace App\ExtraServices;

class ExtraServiceRepository
{
	public function findExtraServices(
		int $userId,
		?int $serviceId = null,
		?int $status = null
	): array {
		$where = [];

		if (!empty($serviceId)) {
			$where["service_id"] = $serviceId;
		} else {
			$where["user_id"] = $userId;

			if ($status !== null) {
				$where["status"] = $status;
			}
		}

		$result = \select_from(
			"extra_services",
			[
				"service_id",
				"user_id",
				"service_name",
				"service_price",
				"status",
				"create_by",
				"created_at"
			],
			$where,
			[
				"order_by" => "service_id",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ExtraServiceRepository expected an array response."
			);
		}

		return $result;
	}

    public function create(array $data): int
    {
        $result = \insert_into(
            "extra_services",
            $data,
            [
                "id" => "service_id",
                "return_type" => "array"
            ]
        );

        if (
            !is_array($result) ||
            empty($result["success"]) ||
            empty($result["id"])
        ) {
            throw new \RuntimeException(
                "Failed to create service. Please try again."
            );
        }

        return (int)$result["id"];
    }
}