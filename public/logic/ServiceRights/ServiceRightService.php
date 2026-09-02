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
}