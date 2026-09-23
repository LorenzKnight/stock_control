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
}