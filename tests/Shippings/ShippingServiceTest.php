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
}