<?php

namespace App\AiSales;

class SalesLeadService
{
	private SalesLeadRepository $repository;

	public function __construct(
		SalesLeadRepository $repository
	) {
		$this->repository = $repository;
	}

	public function getLeadsByCompany(
		int $salesCompanyId
	): array {
		if ($salesCompanyId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid sales company ID."
			);
		}

		$result = $this->repository->findByCompanyId(
			$salesCompanyId
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return [];
		}

		$leads = array_values($result["data"]);

		foreach ($leads as &$lead) {
			$lead["sales_lead_id"] = (int)($lead["sales_lead_id"] ?? 0);
			$lead["sales_company_id"] = (int)($lead["sales_company_id"] ?? 0);
			$primaryContactId = $lead["primary_contact_id"] ?? null;
			$lead["primary_contact_id"] = $primaryContactId !== null
                ? (int)$primaryContactId
                : null;
			$lead["score"] = (int)($lead["score"] ?? 0);
			$lead["stage"] = (string)($lead["stage"] ?? "NEW");
		}

		unset($lead);

		return $leads;
	}


	public function createFromQualifiedCompany(
		array $company,
		array $primaryContact,
		int $userId
	): array {
		$salesCompanyId =
			(int)(
				$company["sales_company_id"]
					?? 0
			);


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


		$qualificationStatus =
			strtoupper(
				trim(
					(string)(
						$company["qualification_status"]
							?? ''
					)
				)
			);


		if (
			$qualificationStatus !==
				"COMPLETED"
		) {
			throw new \RuntimeException(
				"Company qualification must be completed before creating a sales lead."
			);
		}


		$qualificationLevel =
			strtoupper(
				trim(
					(string)(
						$company["qualification_level"]
							?? ''
					)
				)
			);


		if (
			!in_array(
				$qualificationLevel,
				[
					"MEDIUM",
					"HIGH"
				],
				true
			)
		) {
			throw new \RuntimeException(
				"Company is not qualified for a sales lead."
			);
		}


		$contactSearchStatus =
			strtoupper(
				trim(
					(string)(
						$company["contact_search_status"]
							?? ''
					)
				)
			);


		if (
			$contactSearchStatus !==
				"COMPLETED"
		) {
			throw new \RuntimeException(
				"Contact search must be completed before creating a sales lead."
			);
		}


		$primaryContactId =
			(int)(
				$primaryContact["sales_contact_id"]
					?? 0
			);


		if ($primaryContactId <= 0) {
			throw new \InvalidArgumentException(
				"Valid primary contact is required."
			);
		}


		$isPrimary =
			$this->isDatabaseTrue(
				$primaryContact["is_primary"]
					?? false
			);


		if (!$isPrimary) {
			throw new \InvalidArgumentException(
				"Primary sales contact is required."
			);
		}


		$existingResult =
			$this->repository->findByCompanyId(
				$salesCompanyId
			);


		if (
			!empty($existingResult["success"]) &&
			!empty($existingResult["data"])
		) {
			$existingLead =
				array_values(
					$existingResult["data"]
				)[0];


			return [
				"sales_lead_id" =>
					(int)(
						$existingLead["sales_lead_id"]
							?? 0
					),

				"created" =>
					false
			];
		}


		$score =
			(int)(
				$company["qualification_score"]
					?? 0
			);


		if (
			$score < 0 ||
			$score > 100
		) {
			throw new \InvalidArgumentException(
				"Invalid qualification score."
			);
		}


		$scoreReason =
			trim(
				(string)(
					$company["qualification_reason"]
						?? ''
				)
			);


		if ($scoreReason === '') {
			throw new \InvalidArgumentException(
				"Qualification reason is required."
			);
		}


		$salesLeadId =
			$this->repository->create(
				[
					"sales_company_id" =>
						$salesCompanyId,

					"primary_contact_id" =>
						$primaryContactId,

					"market" =>
						$this->nullableString(
							$company["market"]
								?? null
						),

					"country" =>
						$this->nullableString(
							$company["country"]
								?? null
						),

					"language" =>
						$this->nullableString(
							$company["language"]
								?? null
						),

					"stage" =>
						"NEW",

					"score" =>
						$score,

					"score_reason" =>
						$scoreReason,

					"source" =>
						"AI",

					"next_action" =>
						"Generate first outreach",

					"ai_summary" =>
						$this->nullableString(
							$company["research_summary"]
								?? null
						),

					"created_by" =>
						$userId
				]
			);


		return [
			"sales_lead_id" =>
				$salesLeadId,

			"created" =>
				true
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