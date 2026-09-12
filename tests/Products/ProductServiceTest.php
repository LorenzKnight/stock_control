<?php

use App\Products\ProductRepository;
use App\Products\ProductService;
use PHPUnit\Framework\TestCase;

final class ProductServiceTest extends TestCase
{
	public function testRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->expects($this->never())
			->method('findProducts');

		$service =
			new ProductService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"No company selected or linked to this user."
		);

		$service->getProducts(0);
	}


	public function testReturnsEnrichedProducts(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$rawProduct = [
			"product_id" => 10,
			"company_id" => 5,
			"product_name" => "Phone",
			"product_mark" => 3
		];

		$enrichedProduct = [
			"product_id" => 10,
			"company_id" => 5,
			"product_name" => "Phone",
			"product_mark" => 3,
			"mark_name" => "Apple",
			"model_name" => "iPhone",
			"submodel_name" => "15 Pro"
		];

		$filters = [
			"search" => "Phone",
			"mark" => 3
		];

		$repository
			->expects($this->once())
			->method('findProducts')
			->with(
				5,
				$filters
			)
			->willReturn([
				$rawProduct
			]);

		$repository
			->expects($this->once())
			->method('enrichProduct')
			->with(
				$rawProduct,
				5
			)
			->willReturn(
				$enrichedProduct
			);

		$service =
			new ProductService(
				$repository
			);

		$result =
			$service->getProducts(
				5,
				$filters
			);

		$this->assertCount(
			1,
			$result
		);

		$this->assertSame(
			"Phone",
			$result[0]["product_name"]
		);

		$this->assertSame(
			"Apple",
			$result[0]["mark_name"]
		);
	}


	public function testBarcodeReturnsNotFound(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->expects($this->once())
			->method('findByBarcode')
			->with(
				5,
				'123456'
			)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('enrichProduct');

		$service =
			new ProductService(
				$repository
			);

		$result =
			$service->getProductByBarcode(
				5,
				'123456'
			);

		$this->assertFalse(
			$result["found"]
		);

		$this->assertNull(
			$result["product"]
		);
	}


	public function testReturnsProductByBarcode(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$rawProduct = [
			"product_id" => 10,
			"company_id" => 5,
			"product_name" => "Phone",
			"hs_code" => "123456"
		];

		$enrichedProduct =
			$rawProduct + [
				"mark_name" => "Apple",
				"model_name" => "iPhone",
				"submodel_name" => "15 Pro"
			];

		$repository
			->expects($this->once())
			->method('findByBarcode')
			->with(
				5,
				'123456'
			)
			->willReturn(
				$rawProduct
			);

		$repository
			->expects($this->once())
			->method('enrichProduct')
			->with(
				$rawProduct,
				5
			)
			->willReturn(
				$enrichedProduct
			);

		$service =
			new ProductService(
				$repository
			);

		$result =
			$service->getProductByBarcode(
				5,
				'123456'
			);

		$this->assertTrue(
			$result["found"]
		);

		$this->assertSame(
			"Phone",
			$result["product"]["product_name"]
		);
	}

    public function testRejectsCreateWithoutName(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->expects($this->never())
			->method('findExistingProduct');

		$service =
			new ProductService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Product name is required."
		);

		$service->createProduct(
			10,
			5,
			[
				"product_name" => " "
			]
		);
	}


	public function testRejectsCreateWithNegativeQuantity(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->expects($this->never())
			->method('findExistingProduct');

		$service =
			new ProductService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Quantity must be 0 or more."
		);

		$service->createProduct(
			10,
			5,
			[
				"product_name" => "Phone",
				"quantity" => -1
			]
		);
	}


	public function testRequestsConfirmationForExistingProduct(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->expects($this->once())
			->method('findExistingProduct')
			->with(
				5,
				'Phone',
				3,
				4,
				5,
				2026
			)
			->willReturn([
				"product_id" => 20,
				"quantity" => 7
			]);

		$repository
			->expects($this->never())
			->method('create');

		$repository
			->expects($this->never())
			->method('updateQuantity');

		$service =
			new ProductService($repository);

		$result =
			$service->createProduct(
				10,
				5,
				[
					"product_name" => "Phone",
					"product_mark" => 3,
					"product_model" => 4,
					"product_sub_model" => 5,
					"product_year" => 2026,
					"quantity" => 2
				]
			);

		$this->assertTrue(
			$result["needs_confirmation"]
		);

		$this->assertSame(
			20,
			$result["existing_product_id"]
		);

		$this->assertSame(
			7,
			$result["existing_quantity"]
		);
	}


	public function testUpdatesQuantityWhenDuplicateConfirmed(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->method('findExistingProduct')
			->willReturn([
				"product_id" => 20,
				"quantity" => 7
			]);

		$repository
			->expects($this->once())
			->method('updateQuantity')
			->with(
				5,
				20,
				9
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new ProductService($repository);

		$result =
			$service->createProduct(
				10,
				5,
				[
					"product_name" => "Phone",
					"quantity" => 2
				],
				null,
				true
			);

		$this->assertFalse(
			$result["needs_confirmation"]
		);

		$this->assertSame(
			20,
			$result["product_id"]
		);
	}


	public function testCreatesProduct(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->method('findExistingProduct')
			->willReturn(null);

		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["created_by"] === 10 &&
							$data["company_id"] === 5 &&
							$data["product_name"] ===
								"Phone" &&
							$data["quantity"] === 3 &&
							$data["price"] ===
								"199.99" &&
							$data["units_per_pack"] === 2 &&
							$data["weight_per_unit"] ===
								1.5 &&
							$data["total_weight"] === 3.0 &&
							$data["product_image"] ===
								"phone.webp";
					}
				)
			)
			->willReturn(30);

		$repository
			->expects($this->once())
			->method('countCreatedByUser')
			->with(5, 10)
			->willReturn(2);

		$service =
			new ProductService($repository);

		$result =
			$service->createProduct(
				10,
				5,
				[
					"product_name" =>
						" Phone ",
					"quantity" => 3,
					"price" => "199.99",
					"unit_type" => 2,
					"units" => 2,
					"weight_unit" => 1.5
				],
				"phone.webp"
			);

		$this->assertSame(
			30,
			$result["product_id"]
		);

		$this->assertFalse(
			$result["show_reward_modal"]
		);
	}


	public function testFirstProductShowsReward(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->method('findExistingProduct')
			->willReturn(null);

		$repository
			->method('create')
			->willReturn(30);

		$repository
			->expects($this->once())
			->method('countCreatedByUser')
			->with(5, 10)
			->willReturn(1);

		$repository
			->expects($this->once())
			->method('findProductOnboardingByUserId')
			->with(10)
			->willReturn(null);

		$repository
			->expects($this->once())
			->method('createProductOnboarding')
			->with(10)
			->willReturn(true);

		$service =
			new ProductService($repository);

		$result =
			$service->createProduct(
				10,
				5,
				[
					"product_name" => "Phone",
					"quantity" => 1,
					"price" => "100"
				]
			);

		$this->assertTrue(
			$result["show_reward_modal"]
		);

		$this->assertSame(
			"first_product",
			$result["reward_type"]
		);
	}
}