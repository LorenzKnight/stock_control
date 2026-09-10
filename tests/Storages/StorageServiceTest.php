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

    public function testRejectsSaveWithoutSlot(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->expects($this->never())
			->method('findCompanyIdByUserId');

		$service =
			new StorageService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Slot is required."
		);

		$service->saveStorageSelection(
			10,
			0,
			[100]
		);
	}


	public function testRejectsSaveWithoutProducts(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(5);

		$service =
			new StorageService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"At least one product is required."
		);

		$service->saveStorageSelection(
			10,
			12,
			[]
		);
	}


	public function testSavesNewStorageSelection(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(5);

		$repository
			->method('findStoragesBySlotId')
			->with(5, 12)
			->willReturn([]);

		$repository
			->expects($this->exactly(2))
			->method('createStorage');

		$repository
			->expects($this->never())
			->method('deleteStorage');

		$service =
			new StorageService($repository);

		$result =
			$service->saveStorageSelection(
				10,
				12,
				[100, 200]
			);

		$this->assertSame(
			2,
			$result["inserted_count"]
		);

		$this->assertSame(
			0,
			$result["deleted_count"]
		);
	}


	public function testSynchronizesExistingStorageSelection(): void
	{
		$repository =
			$this->createMock(
				StorageRepository::class
			);

		$repository
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(5);

		$repository
			->method('findStoragesBySlotId')
			->with(5, 12)
			->willReturn([
				[
					"storage_id" => 1,
					"product_id" => 100
				],
				[
					"storage_id" => 2,
					"product_id" => 200
				]
			]);

		$repository
			->expects($this->once())
			->method('createStorage')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["company_id"] === 5 &&
							$data["slot_id"] === 12 &&
							$data["product_id"] === 300 &&
							$data["created_by"] === 10;
					}
				)
			);

		$repository
			->expects($this->once())
			->method('deleteStorage')
			->with(2);

		$service =
			new StorageService($repository);

		$result =
			$service->saveStorageSelection(
				10,
				12,
				[
					100,
					300,
					300,
					0
				]
			);

		$this->assertSame(
				1,
				$result["inserted_count"]
			);

		$this->assertSame(
				1,
				$result["deleted_count"]
			);
	}
}