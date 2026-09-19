<?php

use App\Shippings\ShippingRepository;
use App\Shippings\ShippingService;
use PHPUnit\Framework\TestCase;

final class ShippingServiceTest extends TestCase
{
	public function testRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Unauthorized access."
		);

		$service->createShipping(
			0,
			5,
			[
				"destination" => "Göteborg"
			]
		);
	}


	public function testRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Company ID is required."
		);

		$service->createShipping(
			10,
			0,
			[
				"destination" => "Göteborg"
			]
		);
	}


	public function testRejectsShippingWithoutDestination(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Destination is required."
		);

		$service->createShipping(
			10,
			5,
			[
				"destination" => "   "
			]
		);
	}


	public function testCreatesShipping(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'getNextShippingNumber'
			)
			->with(5)
			->willReturn(530000);

		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["shipping_no"]
								=== 530000 &&
							$data["company_id"]
								=== 5 &&
							$data["destination"]
								=== "Stockholm" &&
							$data["shipping_method"]
								=== 2 &&
							$data["status"]
								=== 1 &&
							$data["create_by"]
								=== 10;
					}
				)
			)
			->willReturn(42);

		$service =
			new ShippingService(
				$repository
			);

		$result =
			$service->createShipping(
				10,
				5,
				[
					"shipping_method" => 2,
					"destination" =>
						" Stockholm ",
					"delivery_date" =>
						"2026-10-01",
					"description" =>
						"Test shipment",
					"status" => 1
				]
			);

		$this->assertSame(
			42,
			$result["shipping_id"]
		);

		$this->assertSame(
			530000,
			$result["shipping_no"]
		);

		$this->assertSame(
			"530000.png",
			$result["qr_image"]
		);
	}


	public function testAttachesQrImage(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('updateQrImage')
			->with(
				42,
				5,
				"530000.png"
			);

		$service =
			new ShippingService(
				$repository
			);

		$service->attachQrImage(
			42,
			5,
			"530000.png"
		);

		$this->assertTrue(true);
	}

    public function testRejectsUpdateWithInvalidShippingId(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('update');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Missing shipping ID."
		);

		$service->updateShipping(
			0,
			5,
			[]
		);
	}


	public function testRejectsUpdateWithInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('findById');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Company ID is required."
		);

		$service->updateShipping(
			42,
			0,
			[]
		);
	}


	public function testRejectsUpdateWhenShippingDoesNotExist(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findById')
			->with(42, 5)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('update');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Shipping not found."
		);

		$service->updateShipping(
			42,
			5,
			[]
		);
	}


	public function testUpdatesShipping(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findById')
			->with(42, 5)
			->willReturn([
				"shippings_id" => 42,
				"company_id" => 5
			]);

		$repository
			->expects($this->once())
			->method('update')
			->with(
				42,
				5,
				$this->callback(
					function (array $data): bool {
						return
							$data["shipping_method"] === 2 &&
							$data["destination"] === "Stockholm" &&
							$data["delivery_date"] === "2026-10-05" &&
							$data["description"] === "Updated shipment" &&
							$data["status"] === 1;
					}
				)
			);

		$service =
			new ShippingService(
				$repository
			);

		$service->updateShipping(
				42,
				5,
				[
					"shipping_method" => 2,
					"destination" => " Stockholm ",
					"delivery_date" =>
						"2026-10-05",
					"description" =>
						" Updated shipment ",
					"status" => 1
				]
			);

		$this->assertTrue(true);
	}


	public function testRejectsGetShippingsWithInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('findShippings');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Company ID is required."
		);

		$service->getShippings(0);
	}


	public function testThrowsWhenNoShippingsExist(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findShippings')
			->with(5, '')
			->willReturn([]);

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"No shippings available."
		);

		$service->getShippings(5);
	}


	public function testReturnsShippings(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findShippings')
			->with(5, '1')
			->willReturn([
				[
					"shippings_id" => 42,
					"shipping_no" => 530000,
					"company_id" => 5,
					"destination" => "Stockholm",
					"status" => 1
				]
			]);

		$service =
			new ShippingService(
				$repository
			);

		$result =
				$service->getShippings(
					5,
					' 1 '
				);

		$this->assertCount(
				1,
				$result
			);

		$this->assertSame(
				42,
				$result[0]["shippings_id"]
			);

		$this->assertSame(
				530000,
				$result[0]["shipping_no"]
			);
	}


	public function testRejectsTrackingWithInvalidShippingId(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('findTrackingByShippingId');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid shipping ID."
		);

		$service->getShippingTracking(0);
	}


	public function testReturnsShippingTracking(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findTrackingByShippingId')
			->with(42)
			->willReturn([
				[
					"tracking_id" => 10,
					"checkpoint_name" =>
						"Göteborg",
					"status" => 1
				],
				[
					"tracking_id" => 9,
					"checkpoint_name" =>
						"Stockholm",
					"status" => 1
				]
			]);

		$service =
			new ShippingService(
				$repository
			);

		$result =
				$service
					->getShippingTracking(42);

		$this->assertCount(
				2,
				$result["all_tracking"]
			);

		$this->assertSame(
				10,
				$result["tracking"]["tracking_id"]
			);

		$this->assertSame(
				"Göteborg",
				$result["tracking"]["checkpoint_name"]
			);
	}


	public function testReturnsEmptyShippingTracking(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findTrackingByShippingId')
			->with(42)
			->willReturn([]);

		$service =
			new ShippingService(
				$repository
			);

		$result =
				$service
					->getShippingTracking(42);

		$this->assertSame(
				[],
				$result["all_tracking"]
			);

		$this->assertNull(
				$result["tracking"]
			);
	}


	public function testReturnsEmptyProductSummary(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$service =
			new ShippingService(
				$repository
			);

		$result =
				$service->buildProductSummary([]);

		$this->assertSame(
			[],
			$result
		);
	}


	public function testBuildsProductSummary(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$service =
			new ShippingService(
				$repository
			);

		$result =
				$service->buildProductSummary([
					[
						"products" => [
							[
								"product_id" => 8,
								"name" => "Laptop",
								"mark_name" => "Apple",
								"model_name" =>
									"MacBook Pro",
								"submodel_name" => "M4",
								"image" =>
									"laptop.webp",
								"quantity" => 2,
								"total_kg_price" => 35,
								"total_price_exchanged" =>
									350,
								"total_kg" => 3.5
							]
						]
					],
					[
						"products" => [
							[
								"product_id" => 8,
								"name" => "Laptop",
								"mark_name" => "Apple",
								"model_name" =>
									"MacBook Pro",
								"submodel_name" => "M4",
								"image" =>
									"laptop.webp",
								"quantity" => 1,
								"total_kg_price" => 20,
								"total_price_exchanged" =>
									200,
								"total_kg" => 1.5
							]
						]
					]
				]);

		$this->assertCount(
			1,
			$result
		);

		$this->assertSame(
			8,
			$result[0]["product_id"]
		);

		$this->assertSame(
			3,
			$result[0]["quantity"]
		);

		$this->assertSame(
			55.0,
			$result[0]["total_price"]
		);

		$this->assertSame(
			550.0,
			$result[0]["total_exchanged"]
		);

		$this->assertSame(
			5.0,
			$result[0]["total_weight"]
		);
	}


	public function testRejectsDeleteWithInvalidShippingId(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->never())
			->method('findById');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Shipping ID is required."
		);

		$service->deleteShipping(
			0,
			5
		);
	}


	public function testRejectsDeleteWhenShippingDoesNotExist(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findById')
			->with(42, 5)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('delete');

		$service =
			new ShippingService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Shipping not found."
		);

		$service->deleteShipping(
			42,
			5
		);
	}


	public function testDeletesShippingAndReturnsQrImage(): void
	{
		$repository =
			$this->createMock(
				ShippingRepository::class
			);

		$repository
			->expects($this->once())
			->method('findById')
			->with(42, 5)
			->willReturn([
				"shippings_id" => 42,
				"company_id" => 5,
				"shipping_img" => "530000.png"
			]);

		$repository
			->expects($this->once())
			->method(
				'deleteTrackingByShippingId'
			)
			->with(42);

		$repository
			->expects($this->once())
			->method('delete')
			->with(42, 5);

		$service =
			new ShippingService(
				$repository
			);

		$image =
				$service->deleteShipping(
					42,
					5
				);

		$this->assertSame(
				"530000.png",
				$image
			);
	}
}