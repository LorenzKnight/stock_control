<?php

use App\ExtraServices\ExtraServiceRepository;
use App\ExtraServices\ExtraServiceService;
use PHPUnit\Framework\TestCase;

final class ExtraServiceServiceTest extends TestCase
{
	public function testCreatesValidExtraService(): void
	{
		$repository = $this->createMock(
			ExtraServiceRepository::class
		);

		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["user_id"] === 10 &&
							$data["service_name"] === "Delivery" &&
							$data["service_price"] === 150.0 &&
							$data["status"] === 1;
					}
				)
			)
			->willReturn(25);

		$service = new ExtraServiceService($repository);

		$id = $service->createExtraService(
			10,
			1,
			' Delivery ',
			150.0,
			1
		);

		$this->assertSame(25, $id);
	}


	public function testRejectsInvalidPrice(): void
	{
		$repository = $this->createMock(
			ExtraServiceRepository::class
		);

		$service = new ExtraServiceService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$service->createExtraService(
			10,
			1,
			'Delivery',
			0,
			1
		);
	}


	public function testDeletesExistingExtraService(): void
	{
		$repository = $this->createMock(
			ExtraServiceRepository::class
		);

		$repository
			->method('findById')
			->with(20)
			->willReturn([
				"service_id" => 20,
				"service_name" => "Delivery"
			]);

		$repository
			->expects($this->once())
			->method('delete')
			->with(20);

		$service = new ExtraServiceService($repository);

		$name = $service->deleteExtraService(20);

		$this->assertSame("Delivery", $name);
	}
}