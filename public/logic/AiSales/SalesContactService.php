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


	public function saveFoundContacts(
		int $salesCompanyId,
		array $contacts,
		int $userId
	): array {
		if ($salesCompanyId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales company ID."
			);
		}


		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid user ID."
			);
		}


		$existingResult =
			$this->repository->findByCompanyId(
				$salesCompanyId
			);


		$existingContacts =
			[];

		if (
			!empty($existingResult["success"]) &&
			!empty($existingResult["data"])
		) {
			$existingContacts =
				array_values(
					$existingResult["data"]
				);
		}


		$createdContacts =
			[];


		foreach ($contacts as $contact) {
			$firstName =
				trim(
					(string)(
						$contact["first_name"] ?? ''
					)
				);

			if ($firstName === '') {
				continue;
			}


			$lastName =
				$this->nullableString(
					$contact["last_name"] ?? null
				);

			$email =
				$this->nullableString(
					$contact["email"] ?? null
				);

			$linkedinUrl =
				$this->nullableString(
					$contact["linkedin_url"] ?? null
				);


			if (
				$this->contactAlreadyExists(
					$existingContacts,
					$firstName,
					$lastName,
					$email,
					$linkedinUrl
				)
			) {
				continue;
			}


			$salesContactId =
				$this->repository->create(
					[
						"sales_company_id" =>
							$salesCompanyId,

						"first_name" =>
							$firstName,

						"last_name" =>
							$lastName,

						"job_title" =>
							$this->nullableString(
								$contact["job_title"]
									?? null
							),

						"email" =>
							$email,

						"phone" =>
							$this->nullableString(
								$contact["phone"]
									?? null
							),

						"linkedin_url" =>
							$linkedinUrl,

						"source_url" =>
							$this->nullableString(
								$contact["source_url"]
									?? null
							),

						"source_type" =>
							$this->nullableString(
								$contact["source_type"]
									?? null
							),

						"language" =>
							$this->nullableString(
								$contact["language"]
									?? null
							),

						"is_primary" =>
							(bool)(
								$contact["is_primary"]
									?? false
							),

						"do_not_contact" =>
							false,

						"created_by" =>
							$userId
					]
				);


			$createdContacts[] = [
				"sales_contact_id" =>
					$salesContactId,

				"first_name" =>
					$firstName,

				"last_name" =>
					$lastName
			];


			$existingContacts[] =
				$contact;
		}


		return $createdContacts;
	}


	private function contactAlreadyExists(
		array $contacts,
		string $firstName,
		?string $lastName,
		?string $email,
		?string $linkedinUrl
	): bool {
		foreach ($contacts as $contact) {
			$existingEmail =
				strtolower(
					trim(
						(string)(
							$contact["email"] ?? ''
						)
					)
				);

			$existingLinkedin =
				strtolower(
					trim(
						(string)(
							$contact["linkedin_url"] ?? ''
						)
					)
				);


			if (
				$email !== null &&
				$existingEmail !== '' &&
				$existingEmail === strtolower(
					$email
				)
			) {
				return true;
			}


			if (
				$linkedinUrl !== null &&
				$existingLinkedin !== '' &&
				$existingLinkedin === strtolower(
					$linkedinUrl
				)
			) {
				return true;
			}


			$existingFirstName =
				strtolower(
					trim(
						(string)(
							$contact["first_name"] ?? ''
						)
					)
				);

			$existingLastName =
				strtolower(
					trim(
						(string)(
							$contact["last_name"] ?? ''
						)
					)
				);


			if (
				$existingFirstName ===
					strtolower(
						$firstName
					) &&
				$existingLastName ===
					strtolower(
						(string)$lastName
					)
			) {
				return true;
			}
		}


		return false;
	}


	private function nullableString(
		mixed $value
	): ?string {
		if ($value === null) {
			return null;
		}


		$value =
			trim(
				(string)$value
			);


		return
			$value !== ''
				? $value
				: null;
	}


	public function getPrimaryContactByCompany(
		int $salesCompanyId
	): ?array {
		$contacts =
			$this->getContactsByCompany(
				$salesCompanyId
			);


		foreach ($contacts as $contact) {
			if (
				!empty(
					$contact["is_primary"]
				)
			) {
				return $contact;
			}
		}


		return null;
	}
}