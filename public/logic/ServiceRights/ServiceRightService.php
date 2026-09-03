<?php

namespace App\ServiceRights;

class ServiceRightService
{
	private ServiceRightRepository $repository;

	public function __construct(
		ServiceRightRepository $repository
	) {
		$this->repository = $repository;
	}

	public function canAccessService(
		int $userId,
		string $serviceName
	): bool {
		$serviceName = trim($serviceName);

		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid user ID."
			);
		}

		if ($serviceName === '') {
			throw new \InvalidArgumentException(
				"Missing 'service_name' parameter."
			);
		}

		$right = $this->repository->findByUserAndService(
			$userId,
			$serviceName
		);

		if ($right === null) {
			return false;
		}

		return isset($right["can_access"]) &&
			(int)$right["can_access"] === 1;
	}

    public function getUserRights(
        int $userId,
        ?int $rightId = null,
        ?int $canAccess = null
    ): array {
        if ($userId <= 0) {
            throw new \InvalidArgumentException(
                "Invalid user ID."
            );
        }

        $result = $this->repository->findRights(
            $userId,
            $rightId,
            $canAccess
        );

        if (
            empty($result["success"]) ||
            empty($result["data"])
        ) {
            throw new \Exception(
                "No active service rights found."
            );
        }

        return array_values($result["data"]);
    }

    public function createUserRight(
		int $userId,
		int $creatorId,
		string $serviceName,
		int $canAccess
	): array {
		$serviceName = trim($serviceName);

		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid or missing user ID."
			);
		}

		if ($serviceName === '') {
			throw new \InvalidArgumentException(
				"Service name is required."
			);
		}

		$canAccess = $canAccess === 1 ? 1 : 0;

		$existingRight =
			$this->repository->findByUserAndService(
				$userId,
				$serviceName
			);

		if ($existingRight !== null) {
			throw new \Exception(
				"This user already has a right with the same service name."
			);
		}

		$rightId = $this->repository->create([
			"user_id"      => $userId,
			"service_name" => $serviceName,
			"can_access"   => $canAccess,
			"create_by"    => $creatorId,
			"created_at"   => date("Y-m-d H:i:s")
		]);

		$clonedRights = [];

		$collaboratorIds =
			$this->repository->findCollaboratorIds(
				$userId
			);

		foreach ($collaboratorIds as $collaboratorId) {
			$existingCollaboratorRight =
				$this->repository->findByUserAndService(
					$collaboratorId,
					$serviceName
				);

			if ($existingCollaboratorRight !== null) {
				continue;
			}

			$collaboratorRightId =
				$this->repository->create([
					"user_id"      => $collaboratorId,
					"service_name" => $serviceName,
					"can_access"   => $canAccess,
					"create_by"    => $creatorId,
					"created_at"   => date("Y-m-d H:i:s")
				]);

			$clonedRights[] = [
				"user_id" => $collaboratorId,
				"right_id" => $collaboratorRightId
			];
		}

		return [
			"right_id" => $rightId,
			"cloned_rights" => $clonedRights
		];
	}
}