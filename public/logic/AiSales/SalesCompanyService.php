<?php

namespace App\AiSales;

class SalesCompanyService
{
	private SalesCompanyRepository $repository;


	public function __construct(
		SalesCompanyRepository $repository
	) {
		$this->repository =
			$repository;
	}


	public function getCompanies(
		string $search = ''
	): array {
		$search =
			trim(
				$search
			);

		$result =
			$this->repository->findAll(
				$search
			);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return [];
		}

		return array_values(
			$result["data"]
		);
	}


	public function createProspect(
		array $company,
		int $userId,
		?string $market = null,
		?string $language = null
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid user ID."
			);
		}


		$companyName =
			trim(
				(string)(
					$company["name"] ?? ''
				)
			);

		if ($companyName === '') {
			throw new \InvalidArgumentException(
				"Company name is required."
			);
		}


		$website =
			$this->nullableString(
				$company["website"] ?? null
			);


		$domain =
			strtolower(
				trim(
					(string)(
						$company["domain"] ?? ''
					)
				)
			);

		$domain =
			preg_replace(
				'/^www\./i',
				'',
				$domain
			) ?? $domain;


		if (
			$domain === '' &&
			$website !== null
		) {
			$host =
				parse_url(
					$website,
					PHP_URL_HOST
				);

			if (is_string($host)) {
				$domain =
					strtolower(
						trim(
							$host
						)
					);

				$domain =
					preg_replace(
						'/^www\./i',
						'',
						$domain
					) ?? $domain;
			}
		}


		if ($domain === '') {
			throw new \InvalidArgumentException(
				"Company domain is required."
			);
		}


		$existingCompany =
			$this->repository->findByDomain(
				$domain
			);


		if ($existingCompany !== null) {
			return [
				"sales_company_id" =>
					(int)$existingCompany[
						"sales_company_id"
					],

				"created" =>
					false,

				"company_name" =>
					$existingCompany[
						"company_name"
					] ?? $companyName,

				"domain" =>
					$domain
			];
		}


		$sourceType =
			strtoupper(
				trim(
					(string)(
						$company["source_type"]
						?? "DISCOVERY"
					)
				)
			);


		$source =
			$sourceType === "SUGGESTION"
				? "MANUAL"
				: "WEB";


		$salesCompanyId =
			$this->repository->create(
				[
					"company_name" =>
						$companyName,

					"website" =>
						$website,

					"domain" =>
						$domain,

					"market" =>
						$this->nullableString(
							$market
						),

					"country" =>
						$this->nullableString(
							$company["country"]
								?? null
						),

					"city" =>
						$this->nullableString(
							$company["city"]
								?? null
						),

					"industry" =>
						$this->nullableString(
							$company["industry"]
								?? null
						),

					"language" =>
						$this->nullableString(
							$language
						),

					"description" =>
						$this->nullableString(
							$company["source_text"]
								?? null
						),

					"source" =>
						$source,

					"source_url" =>
						$this->nullableString(
							$company["source_url"]
								?? null
						),

					"ai_researched" =>
						true,

					"created_by" =>
						$userId
				]
			);


		return [
			"sales_company_id" =>
				$salesCompanyId,

			"created" =>
				true,

			"company_name" =>
				$companyName,

			"domain" =>
				$domain
		];
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
}