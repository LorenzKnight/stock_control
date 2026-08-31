<?php

namespace App\ExtraServices;

class ExtraServiceService
{
	private ExtraServiceRepository $repository;

	public function __construct(
		ExtraServiceRepository $repository
	) {
		$this->repository = $repository;
	}

	public function getExtraServices(
		int $userId,
		?int $serviceId = null,
		?int $status = null
	): array {
		$result = $this->repository->findExtraServices(
			$userId,
			$serviceId,
			$status
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			throw new \Exception(
				"No active extra services found."
			);
		}

		return array_values($result["data"]);
	}
}