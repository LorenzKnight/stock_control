<?php

use App\Storages\StorageRepository;
use App\Storages\StorageService;
use PHPUnit\Framework\TestCase;

final class StorageServiceTest extends TestCase
{
	public function testRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->expects($this->never())
			->method('findSlots');

		$service =
			new StorageService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$service->getStorages(
			0,
			'rack'
		);
	}


	public function testRejectsRequestWithoutFilters(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->expects($this->never())
			->method('findSlots');

		$service =
			new StorageService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"No data available."
		);

		$service->getStorages(5);
	}


	public function testReturnsMatchingSlot(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->expects($this->once())
			->method('findSlots')
			->with(5, 'rack', null)
			->willReturn([
				[
					"slot_id" => 12,
					"company_id" => 5,
					"slot_name" => "Rack A",
					"status" => 1
				]
			]);

		$repository
			->expects($this->once())
			->method('findProductsByName')
			->with(5, 'rack')
			->willReturn([]);

		$repository
			->expects($this->once())
			->method('findStoragesBySlotId')
			->with(5, 12)
			->willReturn([]);

		$service =
			new StorageService($repository);

		$result = $service->getStorages(
			5,
			' rack '
		);

		$this->assertCount(
			1,
			$result["slots"]
		);

		$this->assertSame(
			"Rack A",
			$result["slots"][0]["slot_name"]
		);

		$this->assertSame(
			[],
			$result["products"]
		);

		$this->assertSame(
			[],
			$result["storages"]
		);
	}


	public function testReturnsSlotByIdFilter(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->expects($this->once())
			->method('findSlots')
			->with(5, '', 12)
			->willReturn([
				[
					"slot_id" => 12,
					"slot_name" => "Rack A"
				]
			]);

		$repository
			->expects($this->once())
			->method('findStoragesBySlotId')
			->with(5, 12)
			->willReturn([]);

		$service =
			new StorageService($repository);

		$result =
			$service->getStorages(
				5,
				'',
				12
			);

		$this->assertSame(
			12,
			$result["slots"][0]["slot_id"]
		);
	}
}