<?php

use App\Sales\SaleRepository;
use App\Sales\SaleService;
use App\Inventory\InventoryService;
use PHPUnit\Framework\TestCase;

final class SaleServiceTest extends TestCase
{
	private function validSaleData(
		array $overrides = []
	): array {
		return array_replace(
			[
				"customer_id" => 7,
				"currency" => "sek",
				"price_sum" => 20,
				"initial" => 5,
				"delivery_date" =>
					"2026-09-22",
				"remaining" => 15,
				"interest" => 0,
				"installments_month" => 1,
				"no_installments" => 1,
				"payment_date" =>
					"2026-10-22",
				"due" => 15,
				"products" => [
					[
						"product_id" => 8,
						"quantity" => 2,
						"price" => 10,
						"discount" => 0,
						"total" => 20
					]
				]
			],
			$overrides
		);
	}


	public function testRejectsCreateWithInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->never())
			->method('create');

		$inventoryService
			->expects($this->never())
			->method(
				'consumeStockForSale'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Unauthorized access."
		);

		$service->createSale(
			0,
			5,
			[]
		);
	}


	public function testRejectsCreateWithInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Company ID is required."
		);

		$service->createSale(
			10,
			0,
			[]
		);
	}


	public function testRejectsCreateWithoutProducts(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"At least one product is required."
		);

		$service->createSale(
			10,
			5,
			[
				"products" => []
			]
		);
	}


	public function testRejectsCreateWithMissingRequiredField(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$data =
			$this->validSaleData();

		unset(
			$data["delivery_date"]
		);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Missing required field: delivery_date"
		);

		$service->createSale(
			10,
			5,
			$data
		);
	}


	public function testRejectsCreateWhenCustomerDoesNotBelongToCompany(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->once())
			->method('findCustomerById')
			->with(7, 5)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method(
				'productBelongsToCompany'
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"The selected customer does not exist or does not belong to this company."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData()
		);
	}


	public function testRejectsCreateWithInvalidDeliveryDate(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->method('findCustomerById')
			->willReturn([
				"customer_id" => 7
			]);

		$repository
			->expects($this->never())
			->method(
				'productBelongsToCompany'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid delivery_date."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"delivery_date" =>
					"invalid-date"
			])
		);
	}


	public function testRejectsCreateWithInvalidPaymentDate(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->method('findCustomerById')
			->willReturn([
				"customer_id" => 7
			]);

		$repository
			->expects($this->never())
			->method(
				'productBelongsToCompany'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid payment_date."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"payment_date" =>
					"invalid-date"
			])
		);
	}


	public function testRejectsProductFromAnotherCompany(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->method('findCustomerById')
			->willReturn([
				"customer_id" => 7
			]);

		$repository
			->expects($this->once())
			->method(
				'productBelongsToCompany'
			)
			->with(8, 5)
			->willReturn(false);

		$repository
			->expects($this->never())
			->method('create');

		$inventoryService
			->expects($this->never())
			->method(
				'consumeStockForSale'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Product ID 8 does not belong to this company."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData()
		);
	}


	public function testCreatesSaleWithProducts(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->once())
			->method('findCustomerById')
			->with(7, 5)
			->willReturn([
				"customer_id" => 7
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
			->method(
				'getNextOrderNumber'
			)
			->with(5)
			->willReturn(10000001);

		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["ord_no"] ===
								10000001 &&
							$data["customer_id"] ===
								7 &&
							$data["company_id"] ===
								5 &&
							$data["currency"] ===
								"SEK" &&
							$data["price_sum"] ===
								"20.00" &&
							$data["initial"] ===
								"5.00" &&
							$data["delivery_date"] ===
								"2026-09-22 00:00:00" &&
							$data["remaining"] ===
								"15.00" &&
							$data["interest"] ===
								0 &&
							$data[
								"installments_month"
							] === 1 &&
							$data[
								"no_installments"
							] === 1 &&
							$data["payment_date"] ===
								"2026-10-22 00:00:00" &&
							$data["due"] ===
								"15.00" &&
							$data["status"] ===
								1 &&
							$data["create_by"] ===
								10;
					}
				)
			)
			->willReturn(50);

		$inventoryService
			->expects($this->once())
			->method(
				'consumeStockForSale'
			)
			->with(8, 2)
			->willReturn([
				"product_id" => 8,
				"product_name" => "Laptop",
				"company_id" => 5,
				"min_quantity" => 5,
				"previous_stock" => 10,
				"quantity_sold" => 2,
				"new_stock" => 8
			]);

		$repository
			->expects($this->once())
			->method(
				'createPurchasedProduct'
			)
			->with(
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["sales_id"] ===
								50 &&
							$data["customer_id"] ===
								7 &&
							$data["product_id"] ===
								8 &&
							$data["quantity"] ===
								2 &&
							$data["price"] ===
								"10.00" &&
							$data["discount"] ===
								"0.00" &&
							$data["total"] ===
								"20.00" &&
							$data["create_by"] ===
								10;
					}
				)
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->createSale(
				10,
				5,
				$this->validSaleData()
			);

		$this->assertSame(
			50,
			$result["sale_id"]
		);

		$this->assertSame(
			10000001,
			$result["order_no"]
		);

		$this->assertFalse(
			$result["sum_mismatch"]
		);

		$this->assertSame(
			20.0,
			$result["products_sum"]
		);

		$this->assertSame(
			[],
			$result["low_stock_alerts"]
		);
	}


	public function testDetectsSaleSumMismatch(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->method('findCustomerById')
			->willReturn([
				"customer_id" => 7
			]);

		$repository
			->method(
				'productBelongsToCompany'
			)
			->willReturn(true);

		$repository
			->method(
				'getNextOrderNumber'
			)
			->willReturn(10000001);

		$repository
			->method('create')
			->willReturn(50);

		$inventoryService
			->method(
				'consumeStockForSale'
			)
			->willReturn([
				"product_id" => 8,
				"product_name" => "Laptop",
				"company_id" => 5,
				"min_quantity" => 5,
				"new_stock" => 8
			]);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->createSale(
				10,
				5,
				$this->validSaleData([
					"price_sum" => 25
				])
			);

		$this->assertTrue(
			$result["sum_mismatch"]
		);

		$this->assertSame(
			20.0,
			$result["products_sum"]
		);
	}


	public function testCreatesLowStockAlert(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->method('findCustomerById')
			->willReturn([
				"customer_id" => 7
			]);

		$repository
			->method(
				'productBelongsToCompany'
			)
			->willReturn(true);

		$repository
			->method(
				'getNextOrderNumber'
			)
			->willReturn(10000001);

		$repository
			->method('create')
			->willReturn(50);

		$inventoryService
			->expects($this->once())
			->method(
				'consumeStockForSale'
			)
			->with(8, 2)
			->willReturn([
				"product_id" => 8,
				"product_name" => "Laptop",
				"company_id" => 5,
				"min_quantity" => 8,
				"previous_stock" => 10,
				"quantity_sold" => 2,
				"new_stock" => 8
			]);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->createSale(
				10,
				5,
				$this->validSaleData()
			);

		$this->assertSame(
			[
				[
					"product_id" => 8,
					"product_name" =>
						"Laptop",
					"new_stock" => 8
				]
			],
			$result["low_stock_alerts"]
		);
	}


	public function testFirstSaleOnboardingShowsReward(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->once())
			->method(
				'findOnboardingByUserId'
			)
			->with(10)
			->willReturn([
				"company" => true,
				"product" => true,
				"client" => true,
				"sale" => false,
				"sale_reward_seen" =>
					false
			]);

		$repository
			->expects($this->once())
			->method('updateOnboarding')
			->with(
				10,
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["sale"] ===
								true &&
							$data[
								"onboarding_completed"
							] === true &&
							!empty(
								$data["updated_at"]
							);
					}
				)
			);

		$repository
			->expects($this->never())
			->method(
				'createOnboarding'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service
				->processSaleOnboarding(
					10
				);

		$this->assertTrue(
			$result
		);
	}


	public function testCompletedSaleOnboardingDoesNotShowReward(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->once())
			->method(
				'findOnboardingByUserId'
			)
			->with(10)
			->willReturn([
				"company" => true,
				"product" => true,
				"client" => true,
				"sale" => true,
				"sale_reward_seen" =>
					false
			]);

		$repository
			->expects($this->never())
			->method('updateOnboarding');

		$repository
			->expects($this->never())
			->method(
				'createOnboarding'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service
				->processSaleOnboarding(
					10
				);

		$this->assertFalse(
			$result
		);
	}


	public function testCreatesOnboardingForFirstSale(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->once())
			->method(
				'findOnboardingByUserId'
			)
			->with(10)
			->willReturn(null);

		$repository
			->expects($this->once())
			->method('createOnboarding')
			->with(
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["user_id"] ===
								10 &&
							$data["sale"] ===
								true &&
							$data[
								"sale_reward_seen"
							] === false &&
							$data[
								"onboarding_completed"
							] === false &&
							!empty(
								$data["created_at"]
							) &&
							!empty(
								$data["updated_at"]
							);
					}
				)
			);

		$repository
			->expects($this->never())
			->method('updateOnboarding');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service
				->processSaleOnboarding(
					10
				);

		$this->assertTrue(
			$result
		);
	}


	public function testOnboardingFailureDoesNotBreakSaleFlow(): void
	{
		$repository =
			$this->createMock(
				SaleRepository::class
			);

		$inventoryService =
			$this->createMock(
				InventoryService::class
			);

		$repository
			->expects($this->once())
			->method(
				'findOnboardingByUserId'
			)
			->with(10)
			->willThrowException(
				new RuntimeException(
					"Database error."
				)
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service
				->processSaleOnboarding(
					10
				);

		$this->assertFalse(
			$result
		);
	}
}