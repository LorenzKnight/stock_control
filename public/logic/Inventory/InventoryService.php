<?php

namespace App\Inventory;

class InventoryService
{
	private InventoryRepository $repository;

	public function __construct(
		InventoryRepository $repository
	) {
		$this->repository = $repository;
	}


	public function addStock(
		int $userId,
		int $productId,
		int $amount
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access."
			);
		}

		if ($productId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid product ID."
			);
		}

		if ($amount <= 0) {
			throw new \InvalidArgumentException(
				"Amount must be greater than 0."
			);
		}

		$companyId =
			$this->repository
				->findCompanyIdByUserId(
					$userId
				);

		if ($companyId === null) {
			throw new \Exception(
				"Unable to verify user company."
			);
		}

		$product =
			$this->repository
				->findProductStock(
					$productId
				);

		if ($product === null) {
			throw new \Exception(
				"Product not found."
			);
		}

		if (
			(int)($product["company_id"] ?? 0)
			!== $companyId
		) {
			throw new \Exception(
				"Access denied."
			);
		}

		$previousQty =
			(int)($product["quantity"] ?? 0);

		$this->repository->increaseStock(
			$productId,
			$amount
		);

		return [
			"product_id" =>
				$productId,

			"added_amount" =>
				$amount,

			"previous_stock" =>
				$previousQty,

			"new_stock" =>
				$previousQty + $amount
		];
	}

    public function transferStock(
        int $userId,
        int $companyId,
        int $requestingUserId,
        int $productId,
        int $quantity
    ): array {
        if ($userId <= 0) {
            throw new \InvalidArgumentException(
                "Unauthorized access."
            );
        }

        if ($companyId <= 0) {
            throw new \InvalidArgumentException(
                "User company not found."
            );
        }

        if ($requestingUserId <= 0) {
            throw new \InvalidArgumentException(
                "Invalid requesting user."
            );
        }

        if ($productId <= 0) {
            throw new \InvalidArgumentException(
                "Invalid product."
            );
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException(
                "Quantity must be greater than zero."
            );
        }

        $product =
            $this->repository
                ->findProductById(
                    $productId
                );

        if ($product === null) {
            throw new \Exception(
                "Product not found."
            );
        }

        if (
            (int)($product["company_id"] ?? 0)
            !== $companyId
        ) {
            throw new \Exception(
                "This product does not belong to your company."
            );
        }

        $originQty =
            (int)($product["quantity"] ?? 0);

        if ($quantity > $originQty) {
            throw new \Exception(
                "Requested quantity exceeds available stock."
            );
        }

        $requestCompany =
            $this->repository
                ->findCompanyIdByUserId(
                    $requestingUserId
                );

        if (
            $requestCompany === null ||
            $requestCompany <= 0
        ) {
            throw new \Exception(
                "Requesting user's company not found."
            );
        }

        if ($requestCompany === $companyId) {
            throw new \Exception(
                "Cannot transfer stock to the same company."
            );
        }

        $productName =
            (string)(
                $product["product_name"] ?? ''
            );

        $destinationProduct =
            $this->repository
                ->findProductByNameAndCompany(
                    $productName,
                    $requestCompany
                );

        $newOriginQty =
            $originQty - $quantity;

        $this->repository->setStock(
            $productId,
            $companyId,
            $newOriginQty
        );

        try {
            if ($destinationProduct !== null) {
                $destinationQty =
                    (int)(
                        $destinationProduct["quantity"]
                        ?? 0
                    );

                $this->repository
                    ->updateDestinationStock(
                        $productName,
                        $requestCompany,
                        $destinationQty + $quantity
                    );

            } else {
                $newProductData = [
                    "company_id" =>
                        $requestCompany,

                    "created_by" =>
                        $userId,

                    "sale_unit_type" =>
                        $product["sale_unit_type"] ?? null,

                    "units_per_pack" =>
                        $product["units_per_pack"] ?? null,

                    "product_image" =>
                        $product["product_image"] ?? null,

                    "product_name" =>
                        $productName,

                    "hs_code" =>
                        $product["hs_code"] ?? null,

                    "product_type" =>
                        $product["product_type"] ?? null,

                    "product_year" =>
                        $product["product_year"] ?? null,

                    "description" =>
                        $product["description"] ?? null,

                    "currency" =>
                        $product["currency"] ?? null,

                    "price" =>
                        $product["price"] ?? null,

                    "purpose" =>
                        $product["purpose"] ?? null,

                    "quantity" =>
                        $quantity,

                    "min_quantity" =>
                        $product["min_quantity"] ?? null,

                    "weight_per_unit" =>
                        $product["weight_per_unit"] ?? null,

                    "total_weight" =>
                        $product["total_weight"] ?? null,

                    "status" => 1,

                    "created_at" =>
                        date("Y-m-d H:i:s")
                ];

                $this->repository
                    ->createTransferredProduct(
                        $newProductData
                    );
            }

        } catch (\Throwable $e) {
            $this->repository->setStock(
                $productId,
                $companyId,
                $originQty
            );

            throw $e;
        }

        return [
            "product_name" =>
                $productName,

            "origin_previous_stock" =>
                $originQty,

            "origin_new_stock" =>
                $newOriginQty,

            "transferred_quantity" =>
                $quantity,

            "destination_company_id" =>
                $requestCompany
        ];
    }

    public function consumeStockForSale(
		int $productId,
		int $quantity
	): array {
		if ($productId <= 0) {
			throw new \InvalidArgumentException(
				"Invalid product_id in products array."
			);
		}

		if ($quantity <= 0) {
			throw new \InvalidArgumentException(
				"Quantity must be greater than zero."
			);
		}

		$product =
			$this->repository
				->findProductById(
					$productId
				);

		if ($product === null) {
			throw new \Exception(
				"Error fetching product stock for ID: {$productId}"
			);
		}

		$currentStock =
			(int)($product["quantity"] ?? 0);

		if ($currentStock < $quantity) {
			throw new \Exception(
				"Insufficient stock for product ID: {$productId}. " .
				"Available: {$currentStock}, Requested: {$quantity}"
			);
		}

		$newStock =
			$currentStock - $quantity;

		$this->repository
			->updateStockAfterSale(
				$productId,
				$newStock
			);

		return [
			"product_id" =>
				$productId,

			"product_name" =>
				(string)(
					$product["product_name"]
					?? "Unknown Product"
				),

			"company_id" =>
				$product["company_id"] ?? null,

			"min_quantity" =>
				isset($product["min_quantity"])
					? (int)$product["min_quantity"]
					: null,

			"previous_stock" =>
				$currentStock,

			"quantity_sold" =>
				$quantity,

			"new_stock" =>
				$newStock
		];
	}

	public function restoreStockFromSale(
		array $products
	): void {
		foreach ($products as $product) {
			$productId =
				(int)($product["product_id"] ?? 0);

			$quantityToAdd =
				(int)($product["quantity"] ?? 0);

			try {
				$this->repository
					->increaseStock(
						$productId,
						$quantityToAdd
					);

			} catch (\Throwable $e) {
				throw new \RuntimeException(
					"Failed to update product quantity for product ID: " .
					"{$productId}. {$e->getMessage()}",
					0,
					$e
				);
			}
		}
	}
}