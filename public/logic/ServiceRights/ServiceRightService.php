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
}