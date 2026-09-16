<?php

use App\ProductTypes\ProductTypeRepository;
use App\ProductTypes\ProductTypeService;
use PHPUnit\Framework\TestCase;

final class ProductTypeServiceTest extends TestCase
{
	public function testRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				ProductTypeRepository::class
			);

		$repository
			->expects($this->never())
			->method('findOwnerUserId');

		$service =
			new ProductTypeService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Unauthorized access. User not found or invalid token."
		);

		$service->getProductTypes(0);
	}


	public function testReturnsProductTypes(): void
	{
		$repository =
			$this->createMock(
				ProductTypeRepository::class
			);

		$repository
			->expects($this->once())
			->method('findOwnerUserId')
			->with(10)
			->willReturn(5);

		$repository
			->expects($this->once())
			->method('findProductTypes')
			->with(5, 3)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"product_type_id" => 1,
						"product_type_name" => "Electronics",
						"company_id" => 3
					],
					[
						"product_type_id" => 2,
						"product_type_name" => "Accessories",
						"company_id" => 3
					]
				]
			]);

		$service =
			new ProductTypeService($repository);

		$result =
			$service->getProductTypes(
				10,
				3
			);

		$this->assertCount(
			2,
			$result
		);

		$this->assertSame(
			"Electronics",
			$result[0]["product_type_name"]
		);
	}


	public function testReturnsEmptyArrayWhenNoProductTypesExist(): void
	{
		$repository =
			$this->createMock(
				ProductTypeRepository::class
			);

		$repository
			->expects($this->once())
			->method('findOwnerUserId')
			->with(10)
			->willReturn(10);

		$repository
			->expects($this->once())
			->method('findProductTypes')
			->with(10, null)
			->willReturn([
				"success" => false,
				"message" => "No records found",
				"data" => []
			]);

		$service =
			new ProductTypeService($repository);

		$result =
			$service->getProductTypes(10);

		$this->assertSame(
			[],
			$result
		);
	}
}