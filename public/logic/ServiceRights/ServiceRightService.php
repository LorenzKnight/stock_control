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

	public function updateUserRight(
		int $rightId,
		int $editorId,
		string $serviceName,
		int $canAccess
	): array {
		$serviceName = trim($serviceName);

		if ($rightId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid or missing right ID."
			);
		}

		if ($serviceName === '') {
			throw new \InvalidArgumentException(
				"Service name is required."
			);
		}

		$canAccess = $canAccess === 1 ? 1 : 0;

		$existingRight = $this->repository->findById(
			$rightId
		);

		if ($existingRight === null) {
			throw new \Exception(
				"Service right not found or already deleted."
			);
		}

		$userId = (int)$existingRight["user_id"];

		$duplicates =
			$this->repository->findAllByUserAndService(
				$userId,
				$serviceName
			);

		foreach ($duplicates as $duplicate) {
			if (
				(int)$duplicate["right_id"] !== $rightId
			) {
				throw new \Exception(
					"This user already has a right with the same service name."
				);
			}
		}

		$this->repository->update(
			$rightId,
			[
				"service_name" => $serviceName,
				"can_access" => $canAccess
			]
		);

		$collaboratorChanges = [];

		$collaboratorIds =
			$this->repository->findCollaboratorIds(
				$userId
			);

		foreach ($collaboratorIds as $collaboratorId) {
			$collaboratorRight =
				$this->repository->findByUserAndService(
					$collaboratorId,
					$serviceName
				);

			if ($collaboratorRight !== null) {
				$collaboratorRightId =
					(int)$collaboratorRight["right_id"];

				$this->repository->update(
					$collaboratorRightId,
					[
						"service_name" => $serviceName,
						"can_access" => $canAccess
					]
				);

				$collaboratorChanges[] = [
					"action" => "updated",
					"user_id" => $collaboratorId,
					"right_id" => $collaboratorRightId
				];

				continue;
			}

			$collaboratorRightId =
				$this->repository->create([
					"user_id" => $collaboratorId,
					"service_name" => $serviceName,
					"can_access" => $canAccess,
					"create_by" => $editorId,
					"created_at" => date("Y-m-d H:i:s")
				]);

			$collaboratorChanges[] = [
				"action" => "created",
				"user_id" => $collaboratorId,
				"right_id" => $collaboratorRightId
			];
		}

		return [
			"user_id" => $userId,
			"collaborator_changes" => $collaboratorChanges
		];
	}

	public function deleteUserRight(
		int $rightId
	): array {
		if ($rightId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid or missing right ID."
			);
		}

		$existingRight = $this->repository->findById(
			$rightId
		);

		if ($existingRight === null) {
			throw new \Exception(
				"Right not found or already deleted."
			);
		}

		$serviceName =
			$existingRight["service_name"] ?? "Unknown";

		$userId =
			(int)$existingRight["user_id"];

		$deleted = $this->repository->delete(
			$rightId
		);

		if (!$deleted) {
			throw new \RuntimeException(
				"Failed to delete right. Please try again."
			);
		}

		$deletedCollaboratorRights = [];

		$collaboratorIds =
			$this->repository->findCollaboratorIds(
				$userId
			);

		foreach ($collaboratorIds as $collaboratorId) {
			$collaboratorRight =
				$this->repository->findByUserAndService(
					$collaboratorId,
					$serviceName
				);

			if ($collaboratorRight === null) {
				continue;
			}

			$collaboratorRightId =
				(int)$collaboratorRight["right_id"];

			if ($collaboratorRightId <= 0) {
				continue;
			}

			$deletedCollaborator =
				$this->repository->delete(
					$collaboratorRightId
				);

			if (!$deletedCollaborator) {
				continue;
			}

			$deletedCollaboratorRights[] = [
				"user_id" => $collaboratorId,
				"right_id" => $collaboratorRightId
			];
		}

		return [
			"user_id" => $userId,
			"service_name" => $serviceName,
			"deleted_collaborator_rights" =>
				$deletedCollaboratorRights
		];
	}
}