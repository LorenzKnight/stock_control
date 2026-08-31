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


    public function findById(int $serviceId): ?array
    {
        $result = \select_from(
            "extra_services",
            [
                "service_id",
                "user_id"
            ],
            [
                "service_id" => $serviceId
            ],
            [
                "fetch_first" => true,
                "return_type" => "array"
            ]
        );

        if (!is_array($result)) {
            throw new \RuntimeException(
                "ExtraServiceRepository expected an array response."
            );
        }

        if (
            empty($result["success"]) ||
            empty($result["data"])
        ) {
            return null;
        }

        return $result["data"];
    }


    public function findByUserAndName(
        int $userId,
        string $serviceName
    ): array {
        $result = \select_from(
            "extra_services",
            ["service_id"],
            [
                "user_id" => $userId,
                "service_name" => $serviceName
            ],
            [
                "return_type" => "array"
            ]
        );

        if (!is_array($result)) {
            throw new \RuntimeException(
                "ExtraServiceRepository expected an array response."
            );
        }

        if (
            empty($result["success"]) ||
            empty($result["data"])
        ) {
            return [];
        }

        return array_values($result["data"]);
    }


    public function update(
        int $serviceId,
        array $data
    ): void {
        $result = \update_table(
            "extra_services",
            $data,
            [
                "service_id" => $serviceId
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
                "Failed to update extra service. Please try again."
            );
        }
    }
}