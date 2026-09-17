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

    public function testRejectsTransferWhenProductBelongsToAnotherCompany(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"company_id" => 8,
				"quantity" => 20,
				"product_name" => "Electronics"
			]);

		$repository
			->expects($this->never())
			->method('setStock');

		$service =
			new InventoryService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"This product does not belong to your company."
		);

		$service->transferStock(
			10,
			3,
			30,
			25,
			5
		);
	}


	public function testRejectsTransferWhenQuantityExceedsStock(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"company_id" => 3,
				"quantity" => 4,
				"product_name" => "Electronics"
			]);

		$repository
			->expects($this->never())
			->method('setStock');

		$service =
			new InventoryService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Requested quantity exceeds available stock."
		);

		$service->transferStock(
			10,
			3,
			30,
			25,
			5
		);
	}


	public function testRejectsTransferToSameCompany(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"company_id" => 3,
				"quantity" => 20,
				"product_name" => "Electronics"
			]);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(30)
			->willReturn(3);

		$repository
			->expects($this->never())
			->method('setStock');

		$service =
			new InventoryService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Cannot transfer stock to the same company."
		);

		$service->transferStock(
			10,
			3,
			30,
			25,
			5
		);
	}


	public function testTransfersStockToExistingProduct(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"company_id" => 3,
				"quantity" => 20,
				"product_name" => "Electronics"
			]);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(30)
			->willReturn(7);

		$repository
			->expects($this->once())
			->method('findProductByNameAndCompany')
			->with(
				"Electronics",
				7
			)
			->willReturn([
				"quantity" => 4
			]);

		$repository
			->expects($this->once())
			->method('setStock')
			->with(
				25,
				3,
				15
			);

		$repository
			->expects($this->once())
			->method('updateDestinationStock')
			->with(
				"Electronics",
				7,
				9
			);

		$repository
			->expects($this->never())
			->method('createTransferredProduct');

		$service =
			new InventoryService($repository);

		$result =
			$service->transferStock(
				10,
				3,
				30,
				25,
				5
			);

		$this->assertSame(
				[
					"product_name" => "Electronics",
					"origin_previous_stock" => 20,
					"origin_new_stock" => 15,
					"transferred_quantity" => 5,
					"destination_company_id" => 7
				],
				$result
			);
	}


	public function testCreatesDestinationProductWhenMissing(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"company_id" => 3,
				"quantity" => 20,
				"product_name" => "Electronics",
				"price" => 99,
				"currency" => "SEK"
			]);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(30)
			->willReturn(7);

		$repository
			->expects($this->once())
			->method('findProductByNameAndCompany')
			->with(
				"Electronics",
				7
			)
			->willReturn(null);

		$repository
			->expects($this->once())
			->method('setStock')
			->with(
				25,
				3,
				15
			);

		$repository
			->expects($this->never())
			->method('updateDestinationStock');

		$repository
			->expects($this->once())
			->method('createTransferredProduct')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["company_id"] === 7 &&
							$data["created_by"] === 10 &&
							$data["product_name"] === "Electronics" &&
							$data["quantity"] === 5 &&
							$data["price"] === 99 &&
							$data["currency"] === "SEK" &&
							$data["status"] === 1 &&
							!empty($data["created_at"]);
					}
				)
			)
			->willReturn(50);

		$service =
			new InventoryService($repository);

		$result =
			$service->transferStock(
				10,
				3,
				30,
				25,
				5
			);

		$this->assertSame(
				15,
				$result["origin_new_stock"]
			);

		$this->assertSame(
				7,
				$result["destination_company_id"]
			);
	}


	public function testRestoresOriginStockWhenDestinationUpdateFails(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->method('findProductById')
			->willReturn([
				"company_id" => 3,
				"quantity" => 20,
				"product_name" => "Electronics"
			]);

		$repository
			->method('findCompanyIdByUserId')
			->willReturn(7);

		$repository
			->method('findProductByNameAndCompany')
			->willReturn([
				"quantity" => 4
			]);

		$setStockCall = 0;

		$repository
			->expects($this->exactly(2))
			->method('setStock')
			->willReturnCallback(
				function (
					int $productId,
					int $companyId,
					int $quantity
				) use (&$setStockCall): void {
					$setStockCall++;

					$this->assertSame(
						25,
						$productId
					);

					$this->assertSame(
						3,
						$companyId
					);

					if ($setStockCall === 1) {
						$this->assertSame(
							15,
							$quantity
						);
					}

					if ($setStockCall === 2) {
						$this->assertSame(
							20,
							$quantity
						);
					}
				}
			);

		$repository
			->expects($this->once())
			->method('updateDestinationStock')
			->willThrowException(
				new RuntimeException(
					"Destination update failed."
				)
			);

		$service =
			new InventoryService($repository);

		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			"Destination update failed."
		);

		$service->transferStock(
			10,
			3,
			30,
			25,
			5
		);
	}


	public function testRestoresOriginStockWhenDestinationCreationFails(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->method('findProductById')
			->willReturn([
				"company_id" => 3,
				"quantity" => 20,
				"product_name" => "Electronics"
			]);

		$repository
			->method('findCompanyIdByUserId')
			->willReturn(7);

		$repository
			->method('findProductByNameAndCompany')
			->willReturn(null);

		$setStockCall = 0;

		$repository
			->expects($this->exactly(2))
			->method('setStock')
			->willReturnCallback(
				function (
					int $productId,
					int $companyId,
					int $quantity
				) use (&$setStockCall): void {
					$setStockCall++;

					if ($setStockCall === 1) {
						$this->assertSame(
							15,
							$quantity
						);
					}

					if ($setStockCall === 2) {
						$this->assertSame(
							20,
							$quantity
						);
					}
				}
			);

		$repository
			->expects($this->once())
			->method('createTransferredProduct')
			->willThrowException(
				new RuntimeException(
					"Creation failed."
				)
			);

		$service =
			new InventoryService($repository);

		$this->expectException(
				RuntimeException::class
		);

		$this->expectExceptionMessage(
				"Creation failed."
		);

		$service->transferStock(
			10,
			3,
			30,
			25,
			5
		);
	}

	public function testRejectsInvalidProductIdForSale(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->never())
			->method('findProductById');

		$service =
			new InventoryService($repository);

		$this->expectException(
			\InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid product_id in products array."
		);

		$service->consumeStockForSale(
			0,
			5
		);
	}


	public function testRejectsInvalidQuantityForSale(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->never())
			->method('findProductById');

		$service =
			new InventoryService($repository);

		$this->expectException(
			\InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Quantity must be greater than zero."
		);

		$service->consumeStockForSale(
			25,
			0
		);
	}


	public function testThrowsWhenSaleProductDoesNotExist(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('updateStockAfterSale');

		$service =
			new InventoryService($repository);

		$this->expectException(
			\Exception::class
		);

		$this->expectExceptionMessage(
			"Error fetching product stock for ID: 25"
		);

		$service->consumeStockForSale(
			25,
			5
		);
	}


	public function testRejectsSaleWhenStockIsInsufficient(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"product_id" => 25,
				"quantity" => 3,
				"min_quantity" => 2,
				"company_id" => 7,
				"product_name" => "Electronics"
			]);

		$repository
			->expects($this->never())
			->method('updateStockAfterSale');

		$service =
			new InventoryService($repository);

		$this->expectException(
			\Exception::class
		);

		$this->expectExceptionMessage(
			"Insufficient stock for product ID: 25. Available: 3, Requested: 5"
		);

		$service->consumeStockForSale(
			25,
			5
		);
	}


	public function testConsumesStockForSaleSuccessfully(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"product_id" => 25,
				"quantity" => 20,
				"min_quantity" => 5,
				"company_id" => 7,
				"product_name" => "Electronics"
			]);

		$repository
			->expects($this->once())
			->method('updateStockAfterSale')
			->with(
				25,
				15
			);

		$service =
			new InventoryService($repository);

		$result =
			$service->consumeStockForSale(
				25,
				5
			);

		$this->assertSame(
			[
				"product_id" => 25,
				"product_name" => "Electronics",
				"company_id" => 7,
				"min_quantity" => 5,
				"previous_stock" => 20,
				"quantity_sold" => 5,
				"new_stock" => 15
			],
			$result
		);
	}


	public function testSaleCanReduceStockToZero(): void
	{
		$repository =
			$this->createMock(
				InventoryRepository::class
			);

		$repository
			->expects($this->once())
			->method('findProductById')
			->with(25)
			->willReturn([
				"product_id" => 25,
				"quantity" => 5,
				"min_quantity" => 1,
				"company_id" => 7,
				"product_name" => "Electronics"
			]);

		$repository
			->expects($this->once())
			->method('updateStockAfterSale')
			->with(
				25,
				0
			);

		$service =
			new InventoryService($repository);

		$result =
			$service->consumeStockForSale(
				25,
				5
			);

		$this->assertSame(
				0,
				$result["new_stock"]
			);

		$this->assertSame(
				5,
				$result["quantity_sold"]
			);
	}
}