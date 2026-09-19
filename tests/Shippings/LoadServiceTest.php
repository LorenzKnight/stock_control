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
}