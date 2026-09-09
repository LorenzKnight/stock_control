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

    public function createCustomer(
		int $userId,
		?int $companyId,
		array $data,
		?string $imageName = null
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid user ID."
			);
		}

		$name = trim(
			(string)($data["customer_name"] ?? '')
		);

		$birthday = trim(
			(string)($data["customer_birthday"] ?? '')
		);

		if ($name === '') {
			throw new \InvalidArgumentException(
				"Customer name is required."
			);
		}

		if ($birthday === '') {
			throw new \InvalidArgumentException(
				"Customer birthday is required."
			);
		}

		$data["customer_name"] = $name;
		$data["customer_birthday"] = $birthday;
		$data["company_id"] = $companyId;
		$data["create_by"] = $userId;
		$data["created_at"] = date("Y-m-d H:i:s");

		if (
			$imageName !== null &&
			$imageName !== ''
		) {
			$data["customer_image"] = $imageName;
		}

		$customerId =
			$this->repository->create($data);

		$showClientReward = false;

		try {
			$onboarding =
				$this->repository
					->findOnboardingByUserId($userId);

			if ($onboarding === null) {
				$showClientReward =
					$this->repository
						->createCustomerOnboarding($userId);
			} else {
				$clientCompleted =
					$this->isDatabaseTrue(
						$onboarding["client"] ?? false
					);

				$rewardAlreadySeen =
					$this->isDatabaseTrue(
						$onboarding["client_reward_seen"]
							?? false
					);

				$showClientReward =
					!$clientCompleted &&
					!$rewardAlreadySeen;

				if (!$clientCompleted) {
					$updated =
						$this->repository
							->markCustomerOnboardingComplete(
								$userId
							);

					if (!$updated) {
						$showClientReward = false;

						error_log(
							"Could not update onboarding customer step for user_id: " .
							$userId
						);
					}
				}
			}
		} catch (\Throwable $e) {
			$showClientReward = false;

			error_log(
				"Could not process onboarding customer state for user_id: " .
				$userId .
				" - " .
				$e->getMessage()
			);
		}

		return [
			"customer_id" => $customerId,
			"customer_name" => trim(
				$name . " " .
				(string)($data["customer_surname"] ?? '')
			),
			"show_reward_modal" => $showClientReward,
			"reward_type" =>
				$showClientReward
					? "first_client"
					: null
		];
	}


	private function isDatabaseTrue(mixed $value): bool
	{
		return
			$value === true ||
			$value === "t" ||
			$value === 1 ||
			$value === "1";
	}

	public function getCustomerImage(
		int $customerId
	): ?string {
		if ($customerId <= 0) {
			throw new \InvalidArgumentException(
				"Missing customer ID."
			);
		}

		return $this->repository
			->findImageById($customerId);
	}


	public function updateCustomer(
		int $customerId,
		array $data,
		?string $imageName = null
	): void {
		if ($customerId <= 0) {
			throw new \InvalidArgumentException(
				"Missing customer ID."
			);
		}

		$name = trim(
			(string)($data["customer_name"] ?? '')
		);

		if ($name === '') {
			throw new \InvalidArgumentException(
				"Customer name is required."
			);
		}

		$data["customer_name"] = $name;

		if (
			$imageName !== null &&
			$imageName !== ''
		) {
			$data["customer_image"] = $imageName;
		}

		$this->repository->update(
			$customerId,
			$data
		);
	}
}