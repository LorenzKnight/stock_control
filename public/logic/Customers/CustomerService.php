<?php

namespace App\Customers;

class CustomerService
{
	private CustomerRepository $repository;

	public function __construct(CustomerRepository $repository)
	{
		$this->repository = $repository;
	}


	public function getCustomers(
		int $userId,
		string $search = ''
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"User session not found."
			);
		}

		$search = trim($search);

		$companyId =
			$this->repository->findCompanyIdByUserId($userId);

		if ($companyId === null) {
			throw new \Exception(
				"No customers available."
			);
		}

		$result = $this->repository->findCustomers(
			$companyId,
			$search
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			throw new \Exception(
				"No customers available."
			);
		}

		$documentTypes = \GlobalArrays::documentTypes();
		$generalStatus = \GlobalArrays::generalStatus();

		$customers = array_values($result["data"]);

		foreach ($customers as &$customer) {
			$customer["full_name"] = trim(
				($customer["customer_name"] ?? '') .
				' ' .
				($customer["customer_surname"] ?? '')
			);

			$customer["document_no"] =
				$customer["customer_document_no"] ?? '';

			$customer["address"] =
				$customer["customer_address"] ?? '';

			$status =
				$customer["customer_status"] ?? null;

			$customer["status"] =
				$generalStatus[$status]
				?? \tr("unknown", "Unknown");

			$customer["image"] =
				$customer["customer_image"] ?? "";

			$documentType =
				$customer["customer_document_type"] ?? null;

			$customer["document_type"] =
				$documentTypes[$documentType]
				?? \tr("unknown", "Unknown");
		}

		unset($customer);

		return $customers;
	}
}