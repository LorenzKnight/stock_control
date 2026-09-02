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
}