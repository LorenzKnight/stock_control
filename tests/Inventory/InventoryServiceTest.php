<?php

use App\Inventory\InventoryRepository;
use App\Inventory\InventoryService;
use PHPUnit\Framework\TestCase;

final class InventoryServiceTest extends TestCase
{
	public function testRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->never())
			->method('findCompanyIdByUserId');

		$service =
			new InventoryService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Unauthorized access."
		);

		$service->addStock(
			0,
			10,
			5
		);
	}


	public function testRejectsInvalidProductId(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->never())
			->method('findCompanyIdByUserId');

		$service =
			new InventoryService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid product ID."
		);

		$service->addStock(
			10,
			0,
			5
		);
	}


	public function testRejectsInvalidAmount(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->never())
			->method('findCompanyIdByUserId');

		$service =
			new InventoryService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Amount must be greater than 0."
		);

		$service->addStock(
			10,
			25,
			0
		);
	}


	public function testThrowsWhenUserCompanyCannotBeFound(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('findProductStock');

		$service =
			new InventoryService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Unable to verify user company."
		);

		$service->addStock(
			10,
			25,
			5
		);
	}


	public function testThrowsWhenProductDoesNotExist(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(3);

		$repository
			->expects($this->once())
			->method('findProductStock')
			->with(25)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('increaseStock');

		$service =
			new InventoryService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Product not found."
		);

		$service->addStock(
			10,
			25,
			5
		);
	}


	public function testRejectsProductFromAnotherCompany(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(3);

		$repository
			->expects($this->once())
			->method('findProductStock')
			->with(25)
			->willReturn([
				"company_id" => 7,
				"quantity" => 20
			]);

		$repository
			->expects($this->never())
			->method('increaseStock');

		$service =
			new InventoryService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Access denied."
		);

		$service->addStock(
			10,
			25,
			5
		);
	}


	public function testAddsStockSuccessfully(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(3);

		$repository
			->expects($this->once())
			->method('findProductStock')
			->with(25)
			->willReturn([
				"company_id" => 3,
				"quantity" => 20
			]);

		$repository
			->expects($this->once())
			->method('increaseStock')
			->with(
				25,
				5
			);

		$service =
			new InventoryService($repository);

		$result =
			$service->addStock(
				10,
				25,
				5
			);

		$this->assertSame(
			[
				"product_id" => 25,
				"added_amount" => 5,
				"previous_stock" => 20,
				"new_stock" => 25
			],
			$result
		);
	}
}