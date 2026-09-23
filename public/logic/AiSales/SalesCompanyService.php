<?php

namespace App\AiSales;

class SalesCompanyService
{
	private SalesCompanyRepository $repository;

	public function __construct(
		SalesCompanyRepository $repository
	) {
		$this->repository = $repository;
	}

	public function getCompanies(
		string $search = ''
	): array {
		$search = trim($search);

		$result = $this->repository->findAll(
			$search
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			return [];
		}

		return array_values($result["data"]);
	}
}