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
				"interest_type" => 1,
				"interest" => 0,
				"installments_month" => 1,
				"payment_date" =>
					"2026-10-22",
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


	private function validSaleUpdateData(
		array $overrides = []
	): array {
		return array_replace(
			[
				"customer_id" => 7,
				"price_sum" => 100,
				"initial" => 20,
				"delivery_date" =>
					"2026-09-23",
				"interest_type" => 1,
				"interest" => 10,
				"installments_month" => 4,
				"payment_date" =>
					"2026-10-23",
				"products" => [
					[
						"product_id" => 8,
						"quantity" => 2,
						"price" => 50,
						"discount" => 0,
						"total" => 100
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
							$data["interest_type"] ===
								1 &&
							$data["interest"] ===
								0 &&
							$data["installments_month"] === 
								1 &&
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


    public function testRejectsReadWithInvalidCompanyId(): void
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
			->method(
				'findSalesByCompanyId'
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
			"Company ID is required."
		);

		$service->getSales(
			0
		);
	}


	public function testReadThrowsWhenNoSalesExist(): void
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
				'findSalesByCompanyId'
			)
			->with(5)
			->willReturn([]);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"No sales available."
		);

		$service->getSales(
			5
		);
	}


	public function testReadsSaleWithCustomerProductsCategoriesAndPayments(): void
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
				'findSalesByCompanyId'
			)
			->with(5)
			->willReturn([
				[
					"sales_id" => 50,
					"ord_no" => 10000001,
					"customer_id" => 7,
					"price_sum" => 100,
					"initial" => 20,
					"delivery_date" =>
						"2026-09-22 00:00:00",
					"currency" => "SEK",
					"remaining" => 80,
					"interest_type" => 1,
					"interest" => 10,
					"installments_month" => 1,
					"payment_date" =>
						"2026-10-22 00:00:00",
					"due" => 80,
					"created_at" =>
						"2026-09-22 10:00:00"
				]
			]);

		$repository
			->expects($this->once())
			->method(
				'findCustomerDetailsById'
			)
			->with(7, 5)
			->willReturn([
				"customer_name" =>
					"John",
				"customer_surname" =>
					"Doe",
				"customer_phone" =>
					"0700000000",
				"customer_document_type" =>
					null,
				"customer_document_no" =>
					"ABC123",
				"customer_image" =>
					"john.jpg"
			]);

		$repository
			->expects($this->once())
			->method(
				'findPurchasedProductsBySaleId'
			)
			->with(50)
			->willReturn([
				[
					"product_id" => 8,
					"quantity" => 2,
					"price" => 40,
					"discount" => 5,
					"total" => 80
				]
			]);

		$repository
			->expects($this->once())
			->method(
				'findProductDetailsById'
			)
			->with(8, 5)
			->willReturn([
				"sale_unit_type" => "1",
				"units_per_pack" => 1,
				"weight_per_unit" => 2.5,
				"total_weight" => 5,
				"product_image" =>
					"product.jpg",
				"product_name" =>
					"Laptop",
				"product_year" =>
					2026,
				"product_mark" =>
					10,
				"product_model" =>
					11,
				"product_sub_model" =>
					12,
				"price" => 45
			]);

		$repository
			->expects($this->exactly(3))
			->method(
				'findCategoryNameById'
			)
			->willReturnMap([
				[10, "Apple"],
				[11, "MacBook"],
				[12, "Pro"]
			]);

		$repository
			->expects($this->once())
			->method(
				'countPaymentsForSale'
			)
			->with(50)
			->willReturn(2);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->getSales(
				5
			);

		$this->assertCount(
				1,
				$result
			);

		$this->assertSame(
				50,
				$result[0]["sales_id"]
			);

		$this->assertSame(
				10000001,
				$result[0]["ord_no"]
			);

		$this->assertSame(
				"2026-09-22",
				$result[0]["delivery_date"]
			);

		$this->assertSame(
				"2026-10-22",
				$result[0]["payment_date"]
			);

		$this->assertSame(
				8.0,
				$result[0]["total_interest"]
			);

		$this->assertSame(
			1,
			$result[0]["interest_type"]
		);

		$this->assertSame(
			80,
			$result[0]["due"]
		);

		$this->assertSame(
				2,
				$result[0]["payments"]
			);

		$this->assertSame(
				7,
				$result[0]["customer"][
					"customer_id"
				]
			);

		$this->assertSame(
				"John Doe",
				$result[0]["customer"][
					"full_name"
				]
			);

		$this->assertSame(
				"ABC123",
				$result[0]["customer"][
					"document_no"
				]
			);

		$this->assertSame(
				8,
				$result[0]["products"][0][
					"product_id"
				]
			);

		$this->assertSame(
				"Laptop",
				$result[0]["products"][0][
					"name"
				]
			);

		$this->assertSame(
				"Apple",
				$result[0]["products"][0][
					"mark_name"
				]
			);

		$this->assertSame(
				"MacBook",
				$result[0]["products"][0][
					"model_name"
				]
			);

		$this->assertSame(
				"Pro",
				$result[0]["products"][0][
					"submodel_name"
				]
			);

		/*
		* Debemos preservar el comportamiento
		* anterior de get_sales.php:
		* price viene del producto actual.
		*/
		$this->assertSame(
				45,
				$result[0]["products"][0][
					"price"
				]
			);

		$this->assertSame(
				80,
				$result[0]["products"][0][
					"total"
				]
			);
	}


	public function testSearchesSaleByCustomerName(): void
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
			->method(
				'findSalesByCompanyId'
			)
			->willReturn([
				[
					"sales_id" => 50,
					"ord_no" => 10000001,
					"customer_id" => 7,
					"price_sum" => 100,
					"initial" => 20,
					"delivery_date" =>
						"2026-09-22",
					"remaining" => 80,
					"interest_type" => 1,
					"interest" => 0,
					"installments_month" => 1,
					"payment_date" =>
						"2026-10-22",
					"due" => 80
				]
			]);

		$repository
			->method(
				'findCustomerDetailsById'
			)
			->willReturn([
				"customer_name" =>
					"John",
				"customer_surname" =>
					"Doe",
				"customer_document_type" =>
					null,
				"customer_document_no" =>
					"ABC123"
			]);

		$repository
			->method(
				'findPurchasedProductsBySaleId'
			)
			->willReturn([]);

		$repository
			->method(
				'countPaymentsForSale'
			)
			->willReturn(0);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->getSales(
				5,
				"john"
			);

		$this->assertCount(
				1,
				$result
			);

		$this->assertSame(
				"John Doe",
				$result[0]["customer"][
					"full_name"
				]
			);
	}


	public function testSearchesSaleByCustomerDocument(): void
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
			->method(
				'findSalesByCompanyId'
			)
			->willReturn([
				[
					"sales_id" => 50,
					"ord_no" => 10000001,
					"customer_id" => 7,
					"price_sum" => 100,
					"initial" => 20,
					"delivery_date" =>
						"2026-09-22",
					"remaining" => 80,
					"interest_type" => 1,
					"interest" => 0,
					"installments_month" => 1,
					"payment_date" =>
						"2026-10-22",
					"due" => 80
				]
			]);

		$repository
			->method(
				'findCustomerDetailsById'
			)
			->willReturn([
				"customer_name" =>
					"John",
				"customer_surname" =>
					"Doe",
				"customer_document_type" =>
					null,
				"customer_document_no" =>
					"ABC123"
			]);

		$repository
			->method(
				'findPurchasedProductsBySaleId'
			)
			->willReturn([]);

		$repository
			->method(
				'countPaymentsForSale'
			)
			->willReturn(0);

		$service =
				new SaleService(
					$repository,
					$inventoryService
				);

		$result =
			$service->getSales(
				5,
				"abc123"
			);

		$this->assertCount(
				1,
				$result
			);
	}


	public function testSearchesSaleByOrderNumber(): void
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
			->method(
				'findSalesByCompanyId'
			)
			->willReturn([
				[
					"sales_id" => 50,
					"ord_no" => 10000001,
					"customer_id" => 7,
					"price_sum" => 100,
					"initial" => 20,
					"delivery_date" =>
						"2026-09-22",
					"remaining" => 80,
					"interest_type" => 1,
					"interest" => 0,
					"installments_month" => 1,
					"payment_date" =>
						"2026-10-22",
					"due" => 80
				]
			]);

		$repository
			->method(
				'findCustomerDetailsById'
			)
			->willReturn([
				"customer_name" =>
					"John",
				"customer_surname" =>
					"Doe",
				"customer_document_type" =>
					null,
				"customer_document_no" =>
					"ABC123"
			]);

		$repository
			->method(
				'findPurchasedProductsBySaleId'
			)
			->willReturn([]);

		$repository
			->method(
				'countPaymentsForSale'
			)
			->willReturn(0);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->getSales(
				5,
				"10000001"
			);

		$this->assertCount(
				1,
				$result
			);

		$this->assertSame(
				10000001,
				$result[0]["ord_no"]
			);
	}


	public function testSearchReturnsEmptyArrayWhenNothingMatches(): void
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
			->method(
				'findSalesByCompanyId'
			)
			->willReturn([
				[
					"sales_id" => 50,
					"ord_no" => 10000001,
					"customer_id" => 7,
					"price_sum" => 100,
					"initial" => 20,
					"delivery_date" =>
						"2026-09-22",
					"remaining" => 80,
					"interest_type" => 1,
					"interest" => 0,
					"installments_month" => 1,
					"payment_date" =>
						"2026-10-22",
					"due" => 80
				]
			]);

		$repository
			->method(
				'findCustomerDetailsById'
			)
			->willReturn([
				"customer_name" =>
					"John",
				"customer_surname" =>
					"Doe",
				"customer_document_type" =>
					null,
				"customer_document_no" =>
					"ABC123"
			]);

		/*
		* Como la venta no coincide con search,
		* no debemos cargar productos ni pagos.
		*/
		$repository
			->expects($this->never())
			->method(
				'findPurchasedProductsBySaleId'
			);

		$repository
			->expects($this->never())
			->method(
				'countPaymentsForSale'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->getSales(
				5,
				"nothing-here"
			);

		$this->assertSame(
				[],
				$result
			);
	}


	public function testRejectsUpdateWithInvalidUserId(): void
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
			->method('findCustomerById');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"User session not found."
		);

		$service->updateSale(
			0,
			5,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsUpdateWithInvalidCompanyId(): void
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
			->method('findCustomerById');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"User company not found."
		);

		$service->updateSale(
			10,
			0,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsUpdateWithInvalidSaleId(): void
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
			->method('findCustomerById');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Incomplete data to update the sale."
		);

		$service->updateSale(
			10,
			5,
			0,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsUpdateWithoutProducts(): void
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
			->method('findCustomerById');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"No products received."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData([
				"products" => []
			])
		);
	}


	public function testRejectsUpdateWhenCustomerDoesNotBelongToCompany(): void
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
			->method(
				'findSaleForUpdate'
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
			"The selected customer does not exist or does not belong to this company."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsUpdateWhenProductDoesNotBelongToCompany(): void
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
			->method(
				'findSaleForUpdate'
			);

		$inventoryService
			->expects($this->never())
			->method(
				'restoreStockFromSale'
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

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsUpdateWhenSaleDoesNotExist(): void
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
			->expects($this->once())
			->method(
				'findSaleForUpdate'
			)
			->with(50, 5)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method(
				'countPaymentsForSale'
			);

		$inventoryService
			->expects($this->never())
			->method(
				'restoreStockFromSale'
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
			"Sale not found."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsUpdateWhenSaleHasPayments(): void
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
				'findSaleForUpdate'
			)
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->expects($this->once())
			->method(
				'countPaymentsForSale'
			)
			->with(50)
			->willReturn(1);

		$repository
			->expects($this->never())
			->method(
				'hasInterestEarnings'
			);

		$inventoryService
			->expects($this->never())
			->method(
				'restoreStockFromSale'
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
			"This sale cannot be edited because it has registered payments."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsUpdateWhenSaleHasInterestEarnings(): void
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
				'findSaleForUpdate'
			)
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->method(
				'countPaymentsForSale'
			)
			->willReturn(0);

		$repository
			->expects($this->once())
			->method(
				'hasInterestEarnings'
			)
			->with(50)
			->willReturn(true);

		$inventoryService
			->expects($this->never())
			->method(
				'restoreStockFromSale'
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
			"This sale cannot be edited because it has financial records."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testUpdatesSaleAndReplacesProducts(): void
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
				'findSaleForUpdate'
			)
			->with(50, 5)
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->expects($this->once())
			->method(
				'countPaymentsForSale'
			)
			->with(50)
			->willReturn(0);

		$repository
			->expects($this->once())
			->method(
				'hasInterestEarnings'
			)
			->with(50)
			->willReturn(false);

		$oldProducts = [
			[
				"product_id" => 9,
				"quantity" => 3,
				"price" => 20,
				"discount" => 0,
				"total" => 60
			]
		];

		$repository
			->expects($this->once())
			->method(
				'findPurchasedProductsBySaleId'
			)
			->with(50)
			->willReturn(
				$oldProducts
			);

		$inventoryService
			->expects($this->once())
			->method(
				'restoreStockFromSale'
			)
			->with(
				$oldProducts
			);

		$inventoryService
			->expects($this->once())
			->method(
				'consumeStockForSale'
			)
			->with(8, 2);

		$repository
			->expects($this->once())
			->method('updateSale')
			->with(
				50,
				5,
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data[
								"customer_id"
							] === 7 &&
							$data[
								"price_sum"
							] === "100.00" &&
							$data[
								"initial"
							] === "20.00" &&
							$data[
								"delivery_date"
							] ===
								"2026-09-23 00:00:00" &&
							$data[
								"remaining"
							] === "80.00" &&
							$data[
								"interest_type"
							] === 1 &&
							$data[
								"interest"
							] === 10 &&
							$data[
								"installments_month"
							] === 4 &&
							$data[
								"payment_date"
							] ===
								"2026-10-23 00:00:00" &&
							$data[
								"due"
							] === "80.00";
					}
				)
			);

		$repository
			->expects($this->once())
			->method(
				'deletePurchasedProducts'
			)
			->with(50);

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
							$data[
								"sales_id"
							] === 50 &&
							$data[
								"customer_id"
							] === 7 &&
							$data[
								"product_id"
							] === 8 &&
							$data[
								"quantity"
							] === 2 &&
							$data[
								"price"
							] === "50.00" &&
							$data[
								"discount"
							] === "0.00" &&
							$data[
								"total"
							] === "100.00" &&
							$data[
								"create_by"
							] === 10;
					}
				)
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->updateSale(
				10,
				5,
				50,
				$this->validSaleUpdateData()
			);

		$this->assertSame(
				50,
				$result["sale_id"]
			);
	}


	public function testUpdateDoesNotRestoreStockWhenSaleHasNoProducts(): void
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
				'findSaleForUpdate'
			)
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->method(
				'countPaymentsForSale'
			)
			->willReturn(0);

		$repository
			->method(
				'hasInterestEarnings'
			)
			->willReturn(false);

		$repository
			->method(
				'findPurchasedProductsBySaleId'
			)
			->willReturn([]);

		$inventoryService
			->expects($this->never())
			->method(
				'restoreStockFromSale'
			);

		$inventoryService
			->expects($this->once())
			->method(
				'consumeStockForSale'
			)
			->with(8, 2);

		$repository
			->expects($this->once())
			->method('updateSale');

		$repository
			->expects($this->once())
			->method(
				'deletePurchasedProducts'
			);

		$repository
			->expects($this->once())
			->method(
				'createPurchasedProduct'
			);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->updateSale(
				10,
				5,
				50,
				$this->validSaleUpdateData()
			);

		$this->assertSame(
				50,
				$result["sale_id"]
			);
	}


	public function testUpdateStopsWhenNewStockCannotBeConsumed(): void
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
				'findSaleForUpdate'
			)
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->method(
				'countPaymentsForSale'
			)
			->willReturn(0);

		$repository
			->method(
				'hasInterestEarnings'
			)
			->willReturn(false);

		$oldProducts = [
				[
					"product_id" => 9,
					"quantity" => 1
				]
		];

		$repository
			->method(
				'findPurchasedProductsBySaleId'
			)
			->willReturn(
				$oldProducts
			);

		$inventoryService
			->expects($this->once())
			->method(
				'restoreStockFromSale'
			)
			->with(
				$oldProducts
			);

		$inventoryService
			->expects($this->once())
			->method(
				'consumeStockForSale'
			)
			->with(8, 2)
			->willThrowException(
				new Exception(
					"Insufficient stock for product ID: 8."
				)
			);

		/*
		* Si falla inventario, todavía no debemos
		* haber modificado la venta ni sus relaciones.
		*/
		$repository
			->expects($this->never())
			->method('updateSale');

		$repository
			->expects($this->never())
			->method(
				'deletePurchasedProducts'
			);

		$repository
			->expects($this->never())
			->method(
				'createPurchasedProduct'
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
			"Insufficient stock for product ID: 8."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData()
		);
	}


	public function testRejectsDeleteWithInvalidUserId(): void
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
			->method('findSaleForDelete');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"User session not found."
		);

		$service->deleteSale(
			0,
			5,
			50
		);
	}


	public function testRejectsDeleteWithInvalidCompanyId(): void
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
			->method('findSaleForDelete');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"User company not found."
		);

		$service->deleteSale(
			10,
			0,
			50
		);
	}


	public function testRejectsDeleteWithInvalidSaleId(): void
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
			->method('findSaleForDelete');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Sale ID is required."
		);

		$service->deleteSale(
			10,
			5,
			0
		);
	}


	public function testRejectsDeleteWhenSaleDoesNotExist(): void
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
			->method('findSaleForDelete')
			->with(50, 5)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('countPaymentsForSale');

		$repository
			->expects($this->never())
			->method('deleteSale');

		$inventoryService
			->expects($this->never())
			->method('restoreStockFromSale');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sale not found."
		);

		$service->deleteSale(
			10,
			5,
			50
		);
	}


	public function testRejectsDeleteWhenSaleHasPayments(): void
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
			->method('findSaleForDelete')
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->expects($this->once())
			->method('countPaymentsForSale')
			->with(50)
			->willReturn(1);

		$repository
			->expects($this->never())
			->method('hasInterestEarnings');

		$repository
			->expects($this->never())
			->method('findPurchasedProductsBySaleId');

		$repository
			->expects($this->never())
			->method('deleteSale');

		$inventoryService
			->expects($this->never())
			->method('restoreStockFromSale');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"This sale cannot be deleted because it has registered payments."
		);

		$service->deleteSale(
			10,
			5,
			50
		);
	}


	public function testRejectsDeleteWhenSaleHasInterestEarnings(): void
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
			->method('findSaleForDelete')
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->method('countPaymentsForSale')
			->willReturn(0);

		$repository
			->expects($this->once())
			->method('hasInterestEarnings')
			->with(50)
			->willReturn(true);

		$repository
			->expects($this->never())
			->method('findPurchasedProductsBySaleId');

		$repository
			->expects($this->never())
			->method('deleteSale');

		$inventoryService
			->expects($this->never())
			->method('restoreStockFromSale');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"This sale cannot be deleted because it has financial records."
		);

		$service->deleteSale(
			10,
			5,
			50
		);
	}


	public function testDeletesSaleAndRestoresStock(): void
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
			->method('findSaleForDelete')
			->with(50, 5)
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->expects($this->once())
			->method('countPaymentsForSale')
			->with(50)
			->willReturn(0);

		$repository
			->expects($this->once())
			->method('hasInterestEarnings')
			->with(50)
			->willReturn(false);

		$products = [
				[
					"product_id" => 8,
					"quantity" => 2,
					"price" => 50,
					"discount" => 0,
					"total" => 100
				],
				[
					"product_id" => 9,
					"quantity" => 3,
					"price" => 20,
					"discount" => 0,
					"total" => 60
				]
			];

		$repository
			->expects($this->once())
			->method(
				'findPurchasedProductsBySaleId'
			)
			->with(50)
			->willReturn($products);

		$inventoryService
			->expects($this->once())
			->method('restoreStockFromSale')
			->with($products);

		$repository
			->expects($this->once())
			->method('deletePurchasedProducts')
			->with(50);

		$repository
			->expects($this->once())
			->method('deleteSale')
			->with(50, 5);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->deleteSale(
				10,
				5,
				50
			);

		$this->assertSame(
			50,
			$result["sale_id"]
		);
	}


	public function testDeletesSaleWithoutProducts(): void
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
			->method('findSaleForDelete')
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->method('countPaymentsForSale')
			->willReturn(0);

		$repository
			->method('hasInterestEarnings')
			->willReturn(false);

		$repository
			->expects($this->once())
			->method(
				'findPurchasedProductsBySaleId'
			)
			->with(50)
			->willReturn([]);

		$inventoryService
			->expects($this->never())
			->method('restoreStockFromSale');

		$repository
			->expects($this->never())
			->method('deletePurchasedProducts');

		$repository
			->expects($this->once())
			->method('deleteSale')
			->with(50, 5);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->deleteSale(
				10,
				5,
				50
			);

		$this->assertSame(
			50,
			$result["sale_id"]
		);
	}


	public function testDeleteStopsWhenStockCannotBeRestored(): void
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
			->method('findSaleForDelete')
			->willReturn([
				"sales_id" => 50
			]);

		$repository
			->method('countPaymentsForSale')
			->willReturn(0);

		$repository
			->method('hasInterestEarnings')
			->willReturn(false);

		$products = [
				[
					"product_id" => 8,
					"quantity" => 2
				]
			];

		$repository
			->method(
				'findPurchasedProductsBySaleId'
			)
			->willReturn($products);

		$inventoryService
			->expects($this->once())
			->method('restoreStockFromSale')
			->with($products)
			->willThrowException(
				new RuntimeException(
					"Could not restore stock."
				)
			);

		/*
		* Si falla la restauración del inventario,
		* no debemos borrar ninguna información.
		*/
		$repository
			->expects($this->never())
			->method('deletePurchasedProducts');

		$repository
			->expects($this->never())
			->method('deleteSale');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			"Could not restore stock."
		);

		$service->deleteSale(
			10,
			5,
			50
		);
	}


	public function testReducingBalanceSaleReturnsNullTotalInterest(): void
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
				'findSalesByCompanyId'
			)
			->with(5)
			->willReturn([
				[
					"sales_id" => 50,
					"ord_no" => 10000001,
					"customer_id" => 7,
					"price_sum" => 100,
					"initial" => 20,
					"delivery_date" =>
						"2026-09-22 00:00:00",
					"currency" => "SEK",
					"remaining" => 80,
					"interest_type" => 2,
					"interest" => 10,
					"installments_month" => 4,
					"payment_date" =>
						"2026-10-22 00:00:00",
					"due" => 80,
					"created_at" =>
						"2026-09-22 10:00:00"
				]
			]);

		$repository
			->expects($this->once())
			->method(
				'findCustomerDetailsById'
			)
			->with(7, 5)
			->willReturn([
				"customer_name" => "John",
				"customer_surname" => "Doe",
				"customer_document_type" => null,
				"customer_document_no" => "ABC123"
			]);

		$repository
			->expects($this->once())
			->method(
				'findPurchasedProductsBySaleId'
			)
			->with(50)
			->willReturn([]);

		$repository
			->expects($this->once())
			->method(
				'countPaymentsForSale'
			)
			->with(50)
			->willReturn(0);

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$result =
			$service->getSales(
				5
			);

		$this->assertSame(
			2,
			$result[0]["interest_type"]
		);

		$this->assertNull(
			$result[0]["total_interest"]
		);

		$this->assertSame(
			80,
			$result[0]["due"]
		);
	}


	public function testRejectsCreateWithNonPositivePriceSum(): void
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
			"Sale price must be greater than zero."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"price_sum" => 0
			])
		);
	}


	public function testRejectsCreateWithNegativeInitial(): void
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
			"Initial payment cannot be negative."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"initial" => -1
			])
		);
	}


	public function testRejectsCreateWhenInitialExceedsPriceSum(): void
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
			"Initial payment cannot exceed the sale total."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"price_sum" => 20,
				"initial" => 21
			])
		);
	}


	public function testRejectsCreateWithInvalidInterestType(): void
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
			"Invalid interest type."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"interest_type" => 3
			])
		);
	}


	public function testRejectsCreateWithNegativeInterest(): void
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
			"Interest cannot be negative."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"interest" => -1
			])
		);
	}


	public function testRejectsCreateWithNonPositiveInstallments(): void
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
			"Installments must be greater than zero."
		);

		$service->createSale(
			10,
			5,
			$this->validSaleData([
				"installments_month" => 0
			])
		);
	}


	public function testRejectsUpdateWithNonPositivePriceSum(): void
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
			->method('findSaleForUpdate');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Sale price must be greater than zero."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData([
				"price_sum" => 0
			])
		);
	}


	public function testRejectsUpdateWithNegativeInitial(): void
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
			->method('findSaleForUpdate');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Initial payment cannot be negative."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData([
				"initial" => -1
			])
		);
	}


	public function testRejectsUpdateWhenInitialExceedsPriceSum(): void
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
			->method('findSaleForUpdate');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Initial payment cannot exceed the sale total."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData([
				"price_sum" => 100,
				"initial" => 101
			])
		);
	}


	public function testRejectsUpdateWithInvalidInterestType(): void
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
			->method('findSaleForUpdate');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid interest type."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData([
				"interest_type" => 3
			])
		);
	}


	public function testRejectsUpdateWithNegativeInterest(): void
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
			->method('findSaleForUpdate');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Interest cannot be negative."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData([
				"interest" => -1
			])
		);
	}


	public function testRejectsUpdateWithNonPositiveInstallments(): void
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
			->method('findSaleForUpdate');

		$service =
			new SaleService(
				$repository,
				$inventoryService
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Installments must be greater than zero."
		);

		$service->updateSale(
			10,
			5,
			50,
			$this->validSaleUpdateData([
				"installments_month" => 0
			])
		);
	}
}