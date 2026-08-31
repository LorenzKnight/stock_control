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
}