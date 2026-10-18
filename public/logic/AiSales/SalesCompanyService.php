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
						false,

					"research_status" =>
						"PENDING",

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


	public function markResearching(
		int $salesCompanyId
	): void {
		$this->repository->updateResearch(
			$salesCompanyId,
			[
				"research_status" =>
					"RESEARCHING",

				"updated_at" =>
					date(
						"Y-m-d H:i:s"
					)
			]
		);
	}


	public function completeResearch(
		int $salesCompanyId,
		array $company,
		array $research
	): void {
		$summary =
			trim(
				(string)(
					$research["summary"] ?? ''
				)
			);

		if ($summary === '') {
			throw new \InvalidArgumentException(
				"Research summary is required."
			);
		}


		$sources =
			$research["sources"] ?? [];

		if (!is_array($sources)) {
			$sources = [];
		}


		$encodedSources =
			json_encode(
				array_values(
					$sources
				),
				JSON_UNESCAPED_UNICODE |
				JSON_UNESCAPED_SLASHES
			);

		if ($encodedSources === false) {
			throw new \RuntimeException(
				"Could not encode company research sources."
			);
		}


		$researchedAt =
			date(
				"Y-m-d H:i:s"
			);


		$this->repository->updateResearch(
			$salesCompanyId,
			[
				"city" =>
					$this->nullableString(
						$research["city"]
							?? $company["city"]
							?? null
					),

				"industry" =>
					$this->nullableString(
						$research["industry"]
							?? $company["industry"]
							?? null
					),

				"language" =>
					$this->nullableString(
						$research["language"]
							?? $company["language"]
							?? null
					),

				"research_summary" =>
					$summary,

				"research_inventory_context" =>
					$this->nullableString(
						$research["inventory_context"]
							?? null
					),

				"research_sources" =>
					$encodedSources,

				"research_status" =>
					"COMPLETED",

				"ai_researched" =>
					true,

				"researched_at" =>
					$researchedAt,

				"updated_at" =>
					$researchedAt
			]
		);
	}


	public function markResearchFailed(
		int $salesCompanyId
	): void {
		$this->repository->updateResearch(
			$salesCompanyId,
			[
				"research_status" =>
					"FAILED",

				"ai_researched" =>
					false,

				"updated_at" =>
					date(
						"Y-m-d H:i:s"
					)
			]
		);
	}
}