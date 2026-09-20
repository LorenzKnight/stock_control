<?php

use App\Shippings\LoadRepository;
use App\Shippings\LoadService;
use PHPUnit\Framework\TestCase;

final class LoadServiceTest extends TestCase
{
	public function testRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->never())
			->method('findByShippingId');

		$service =
			new LoadService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Company ID is required."
		);

		$service->getLoadsForShipping(
			0,
			42
		);
	}


	public function testRejectsInvalidShippingId(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->never())
			->method('findByShippingId');

		$service =
			new LoadService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid shipping ID."
		);

		$service->getLoadsForShipping(
			5,
			0
		);
	}


	public function testReturnsEmptyLoads(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->once())
			->method('findByShippingId')
			->with(5, 42)
			->willReturn([]);

		$service =
			new LoadService(
				$repository
			);

		$result =
			$service->getLoadsForShipping(
				5,
				42
			);

		$this->assertSame(
			[],
			$result
		);
	}


	public function testReturnsLoadsWithCustomer(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->once())
			->method('findByShippingId')
			->with(5, 42)
			->willReturn([
				[
					"load_id" => 100,
					"load_no" => 540000,
					"customer_id" => 7,
					"status" => 1
				]
			]);

		$repository
			->expects($this->once())
			->method('findCustomerById')
			->with(7, 5)
			->willReturn([
				"customer_name" => "John",
				"customer_surname" => "Doe",
				"customer_phone" => "12345",
				"customer_image" => "john.webp"
			]);

		$service =
			new LoadService(
				$repository
			);

		$result =
			$service->getLoadsForShipping(
				5,
				42
			);

		$this->assertCount(
			1,
			$result
		);

		$this->assertSame(
			"John Doe",
			$result[0]["customer"]["full_name"]
		);

		$this->assertSame(
			"12345",
			$result[0]["customer"]["phone"]
		);

		$this->assertSame(
			"john.webp",
			$result[0]["customer"]["image"]
		);
	}


    public function testRejectsProductsWithInvalidLoadId(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findLoadedProductsByLoadId'
			);

		$service =
			new LoadService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid load ID."
		);

		$service->getProductsForLoad(0);
	}


	public function testReturnsEmptyProductsForLoad(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findLoadedProductsByLoadId'
			)
			->with(100)
			->willReturn([]);

		$service =
			new LoadService(
				$repository
			);

		$result =
				$service
					->getProductsForLoad(100);

		$this->assertSame(
			[],
			$result["products"]
		);

		$this->assertSame(
			0.0,
			$result["total_weight"]
		);
	}


	public function testReturnsProductsForLoad(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findLoadedProductsByLoadId'
			)
			->with(100)
			->willReturn([
				[
					"product_id" => 8,
					"quantity" => 2,
					"total_kg" => 3.5,
					"from_currency" => "USD",
					"total_kg_price" => 35,
					"to_currency" => "SEK",
					"total_price_exchanged" => 350
				]
			]);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(8)
			->willReturn([
				"product_name" => "Laptop",
				"product_year" => 2026,
				"product_image" => "laptop.webp",
				"product_mark" => 11,
				"product_model" => 12,
				"product_sub_model" => 13,
				"price" => 1000,
				"total_weight" => 1.75
			]);

		$repository
			->expects($this->exactly(3))
			->method('findCategoryNameById')
			->willReturnMap([
				[11, "Apple"],
				[12, "MacBook Pro"],
				[13, "M4"]
			]);

		$service =
			new LoadService(
				$repository
			);

		$result =
				$service
					->getProductsForLoad(100);

		$this->assertCount(
				1,
				$result["products"]
			);

		$this->assertSame(
				"Laptop",
				$result["products"][0]["name"]
			);

		$this->assertSame(
				"Apple",
				$result["products"][0]["mark_name"]
			);

		$this->assertSame(
				"MacBook Pro",
				$result["products"][0]["model_name"]
			);

		$this->assertSame(
				"M4",
				$result["products"][0]["submodel_name"]
			);

		$this->assertSame(
				3.5,
				$result["total_weight"]
			);
	}


	public function testDeletesLoadsForShipping(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->once())
			->method('findIdsByShippingId')
			->with(5, 42)
			->willReturn([
				100,
				101
			]);

		$repository
			->expects($this->exactly(2))
			->method(
				'deleteLoadedProductsByLoadId'
			);

		$repository
			->expects($this->exactly(2))
			->method('deleteById');

		$service =
			new LoadService(
				$repository
			);

		$service->deleteLoadsForShipping(
				5,
				42
			);

		$this->assertTrue(true);
	}


	public function testDeleteShippingWithNoLoadsDoesNothing(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->once())
			->method('findIdsByShippingId')
			->with(5, 42)
			->willReturn([]);

		$repository
			->expects($this->never())
			->method(
				'deleteLoadedProductsByLoadId'
			);

		$repository
			->expects($this->never())
			->method('deleteById');

		$service =
			new LoadService(
				$repository
			);

		$service->deleteLoadsForShipping(
				5,
				42
			);

		$this->assertTrue(true);
	}


	public function testRejectsCreateWithInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new LoadService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Unauthorized access."
		);

		$service->createLoad(
			0,
			5,
			[]
		);
	}


	public function testRejectsCreateWhenShippingDoesNotBelongToCompany(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->expects($this->once())
			->method('findShippingById')
			->with(42, 5)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new LoadService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"The selected shipping does not exist or does not belong to this company."
		);

		$service->createLoad(
			10,
			5,
			[
				"customer_id" => 7,
				"shippings_id" => 42,
				"from_currency" => "USD",
				"to_currency" => "SEK",
				"price_per_kg" => 10,
				"total_kg" => 5
			]
		);
	}


	public function testRejectsCreateWhenCustomerDoesNotBelongToCompany(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->method('findShippingById')
			->with(42, 5)
			->willReturn([
				"shippings_id" => 42
			]);

		$repository
			->expects($this->once())
			->method('findCustomerById')
			->with(7, 5)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new LoadService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"The selected customer does not exist or does not belong to this company."
		);

		$service->createLoad(
			10,
			5,
			[
				"customer_id" => 7,
				"shippings_id" => 42,
				"from_currency" => "USD",
				"to_currency" => "SEK",
				"price_per_kg" => 10,
				"total_kg" => 5
			]
		);
	}


	public function testCreatesLoadWithProducts(): void
	{
		$repository =
			$this->createMock(
				LoadRepository::class
			);

		$repository
			->method('findShippingById')
			->with(42, 5)
			->willReturn([
				"shippings_id" => 42
			]);

		$repository
			->method('findCustomerById')
			->with(7, 5)
			->willReturn([
				"customer_name" => "John",
				"customer_surname" => "Doe"
			]);

		$repository
			->expects($this->once())
			->method(
				'productBelongsToCompany'
			)
			->with(8, 5)
			->willReturn(true);

		$repository
			->expects($this->once())
			->method('getNextLoadNumber')
			->with(5)
			->willReturn(540000);

		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["shippings_id"] === 42 &&
							$data["customer_id"] === 7 &&
							$data["company_id"] === 5 &&
							$data["load_no"] === 540000 &&
							$data["from_currency"] === "USD" &&
							$data["to_currency"] === "SEK" &&
							$data["price_per_kg"] === "10.00" &&
							$data["total_kg"] === "5.000" &&
							$data["price_sum"] === "50.00" &&
							$data["discount"] === "5.00" &&
							$data["taxes"] === "10.00" &&
							$data["price_total"] === "49.50" &&
							$data["status"] === 1 &&
							$data["create_by"] === 10;
					}
				)
			)
			->willReturn(100);

		$repository
			->expects($this->once())
			->method('createLoadedProduct')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["load_id"] === 100 &&
							$data["product_id"] === 8 &&
							$data["quantity"] === 2 &&
							$data["total_kg"] === "3.500" &&
							$data["total_kg_price"] === "35.00" &&
							$data["total_price_exchanged"] === "350.00" &&
							$data["create_by"] === 10;
					}
				)
			);

		$service =
			new LoadService(
				$repository
			);

		$result =
				$service->createLoad(
					10,
					5,
					[
						"customer_id" => 7,
						"shippings_id" => 42,
						"from_currency" => "usd",
						"to_currency" => "sek",
						"price_per_kg" => 10,
						"total_kg" => 5,
						"discount" => 5,
						"taxes" => 10,
						"price_total_exchanged" =>
							500,
						"destination" =>
							" Stockholm ",
						"comment" =>
							" Test load ",
						"products" => [
							[
								"product_id" => 8,
								"quantity" => 2,
								"total_kg" => 3.5,
								"total_kg_price" => 35,
								"total_price_exchanged" =>
									350
							]
						]
					]
				);

		$this->assertSame(
			100,
			$result["load_id"]
		);

		$this->assertSame(
			540000,
			$result["load_no"]
		);
	}
}