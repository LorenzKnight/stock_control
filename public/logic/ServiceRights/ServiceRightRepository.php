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
                "right_id",
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

	public function findById(int $rightId): ?array
	{
		$result = \select_from(
			"service_rights",
			[
				"right_id",
				"user_id",
				"service_name",
				"can_access"
			],
			[
				"right_id" => $rightId
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

	public function findAllByUserAndService(
		int $userId,
		string $serviceName
	): array {
		$result = \select_from(
			"service_rights",
			["right_id"],
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
				"ServiceRightRepository expected an array response."
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

    public function findCollaboratorIds( //deuda arquitectónica controlada
        int $parentUserId
    ): array {
        $result = \select_from(
            "users",
            ["user_id"],
            [
                "parent_user" => $parentUserId
            ],
            [
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
            return [];
        }

        $collaboratorIds = [];

        foreach ($result["data"] as $row) {
            $collaboratorId = (int)($row["user_id"] ?? 0);

            if ($collaboratorId > 0) {
                $collaboratorIds[] = $collaboratorId;
            }
        }

        return $collaboratorIds;
    }

	public function create(array $data): int
    {
        $result = \insert_into(
            "service_rights",
            $data,
            [
                "id" => "right_id",
                "return_type" => "array"
            ]
        );

        if (
            !is_array($result) ||
            empty($result["success"]) ||
            empty($result["id"])
        ) {
            throw new \RuntimeException(
                "Failed to create user right. Please try again."
            );
        }

        return (int)$result["id"];
    }

	public function update(
		int $rightId,
		array $data
	): void {
		$result = \update_table(
			"service_rights",
			$data,
			[
				"right_id" => $rightId
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
				"Failed to update user right. Please try again."
			);
		}
	}

	public function delete(int $rightId): bool
	{
		$result = \delete_from(
			"service_rights",
			[
				"right_id" => $rightId
			],
			[
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"ServiceRightRepository expected an array response."
			);
		}

		return !empty($result["success"]);
	}
}