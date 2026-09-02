<?php

namespace App\ServiceRights;

class ServiceRightRepository
{
	public function findByUserAndService(
		int $userId,
		string $serviceName
	): ?array {
		$result = \select_from(
			"service_rights",
			[
				"can_access"
			],
			[
				"user_id" => $userId,
				"service_name" => $serviceName
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ServiceRightRepository expected an array response."
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

    public function findRights(
        int $userId,
        ?int $rightId = null,
        ?int $canAccess = null
    ): array {
        $where = [];

        if (!empty($rightId)) {
            $where["right_id"] = $rightId;
        } else {
            $where["user_id"] = $userId;

            if ($canAccess !== null) {
                $where["can_access"] = $canAccess;
            }
        }

        $result = \select_from(
            "service_rights",
            [
                "right_id",
                "user_id",
                "service_name",
                "can_access",
                "create_by",
                "created_at"
            ],
            $where,
            [
                "order_by" => "right_id",
                "order_direction" => "DESC",
                "return_type" => "array"
            ]
        );

        if (!is_array($result)) {
            throw new \RuntimeException(
                "ServiceRightRepository expected an array response."
            );
        }

        return $result;
    }
}