<?php

namespace App\AiSales;

class SalesContactService
{
	private SalesContactRepository $repository;

	public function __construct(
		SalesContactRepository $repository
	) {
		$this->repository = $repository;
	}

	public function getContactsByCompany(
		int $salesCompanyId,
		string $search = ''
	): array {
		if ($salesCompanyId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales company ID."
			);
		}

		$search = trim($search);

		$result = $this->repository->findByCompanyId(
			$salesCompanyId,
			$search
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return [];
		}

		$contacts = array_values(
			$result["data"]
		);

		foreach ($contacts as &$contact) {
			$contact["full_name"] = trim(
				(string)($contact["first_name"] ?? '') .
				' ' .
				(string)($contact["last_name"] ?? '')
			);

			$contact["is_primary"] =
				$this->isDatabaseTrue(
					$contact["is_primary"] ?? false
				);

			$contact["do_not_contact"] =
				$this->isDatabaseTrue(
					$contact["do_not_contact"] ?? false
				);
		}

		unset($contact);

		return $contacts;
	}


	private function isDatabaseTrue(
		mixed $value
	): bool {
		return
			$value === true ||
			$value === "t" ||
			$value === 1 ||
			$value === "1";
	}
}