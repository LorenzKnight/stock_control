<?php

namespace App\Categories;

class CategoryService
{
	private CategoryRepository $repository;

	public function __construct(CategoryRepository $repository)
	{
		$this->repository = $repository;
	}

	public function getRootCategories(
		int $userId,
		int $companyId
	): array {
		$result = $this->repository->findRootCategories(
			$userId,
			$companyId
		);

		if (
			empty($result["success"]) ||
			empty($result["data"])
		) {
			throw new \Exception("No categories available.");
		}

		return $result["data"];
	}
}