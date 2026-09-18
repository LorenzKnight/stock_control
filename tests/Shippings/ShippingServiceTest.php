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
}