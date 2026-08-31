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

    public function createExtraService(
        int $userId,
        int $creatorId,
        string $serviceName,
        float $servicePrice,
        int $status
    ): int {
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

        if ($servicePrice <= 0) {
            throw new \InvalidArgumentException(
                "Service price must be greater than zero."
            );
        }

        $status = $status === 1 ? 1 : 0;

        $data = [
            "user_id"       => $userId,
            "service_name"  => $serviceName,
            "service_price" => $servicePrice,
            "status"        => $status,
            "create_by"     => $creatorId,
            "created_at"    => date("Y-m-d H:i:s")
        ];

        return $this->repository->create($data);
    }

    public function updateExtraService(
        int $serviceId,
        string $serviceName,
        string $servicePrice,
        int $status
    ): void {
        $serviceName = trim($serviceName);
        $servicePrice = trim($servicePrice);

        if ($serviceId <= 0) {
            throw new \InvalidArgumentException(
                "Invalid or missing service ID."
            );
        }

        if ($serviceName === '') {
            throw new \InvalidArgumentException(
                "Service name is required."
            );
        }

        if (
            $servicePrice === '' ||
            !is_numeric($servicePrice)
        ) {
            throw new \InvalidArgumentException(
                "Valid service price is required."
            );
        }

        $existingService = $this->repository->findById(
            $serviceId
        );

        if ($existingService === null) {
            throw new \Exception(
                "Service not found or already deleted."
            );
        }

        $userId = (int)$existingService["user_id"];

        $duplicates = $this->repository->findByUserAndName(
            $userId,
            $serviceName
        );

        foreach ($duplicates as $duplicate) {
            if (
                (int)$duplicate["service_id"] !== $serviceId
            ) {
                throw new \Exception(
                    "This user already has an extra service with the same name."
                );
            }
        }

        $status = $status === 1 ? 1 : 0;

        $this->repository->update(
            $serviceId,
            [
                "service_name" => $serviceName,
                "service_price" => $servicePrice,
                "status" => $status
            ]
        );
    }
}