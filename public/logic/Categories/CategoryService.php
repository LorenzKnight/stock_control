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

    public function getSubCategories(
        int $userId,
        int $parentCategoryId,
        ?int $companyId = null
    ): array {
        $result = $this->repository->findSubCategories(
            $userId,
            $parentCategoryId,
            $companyId
        );

        if (
            empty($result["success"]) ||
            empty($result["data"])
        ) {
            throw new \Exception(
                "No subcategories available for this mark."
            );
        }

        return $result["data"];
    }

    public function createCategory(
        int $userId,
        int $ownerUserId,
        int $companyId,
        string $categoryName,
        ?int $catParentSub = null,
        ?int $subParent = null
    ): int {
        $categoryName = trim($categoryName);

        if ($categoryName === '') {
            throw new \InvalidArgumentException(
                "Category name is required."
            );
        }

        $data = [
            "category_name" => $categoryName,
            "user_id"       => $ownerUserId,
            "company_id"    => $companyId,
            "create_by"     => $userId,
            "created_at"    => date("Y-m-d H:i:s")
        ];

        if (
            $catParentSub !== null &&
            $subParent === null
        ) {
            $data["cat_parent_sub"] = $catParentSub;
        }

        if ($subParent !== null) {
            $data["sub_parent"] = $subParent;
        }

        return $this->repository->create($data);
    }
}