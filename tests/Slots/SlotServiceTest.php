<?php

use App\Slots\SlotRepository;
use App\Slots\SlotService;
use PHPUnit\Framework\TestCase;

final class SlotServiceTest extends TestCase
{
	public function testRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				SlotRepository::class
			);

		$repository
			->expects($this->never())
			->method('findSlots');

		$service =
			new SlotService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$service->getSlots(0);
	}


	public function testReturnsCompanySlots(): void
	{
		$repository =
			$this->createMock(
				SlotRepository::class
			);

		$repository
			->expects($this->once())
			->method('findSlots')
			->with(5, '', null)
			->willReturn([
				[
					"slot_id" => 10,
					"company_id" => 5,
					"slot_name" => "Rack A"
				],
				[
					"slot_id" => 11,
					"company_id" => 5,
					"slot_name" => "Rack B"
				]
			]);

		$service =
			new SlotService($repository);

		$result =
			$service->getSlots(5);

		$this->assertCount(2, $result);

		$this->assertSame(
			"Rack A",
			$result[0]["slot_name"]
		);
	}


	public function testSearchesSlotsByName(): void
	{
		$repository =
			$this->createMock(
				SlotRepository::class
			);

		$repository
			->expects($this->once())
			->method('findSlots')
			->with(
				5,
				'rack',
				null
			)
			->willReturn([
				[
					"slot_id" => 10,
					"slot_name" => "Rack A"
				]
			]);

		$service =
			new SlotService($repository);

		$result =
			$service->getSlots(
				5,
				' rack '
			);

		$this->assertCount(1, $result);

		$this->assertSame(
			"Rack A",
			$result[0]["slot_name"]
		);
	}


	public function testReturnsSelectedSlot(): void
	{
		$repository =
			$this->createMock(
				SlotRepository::class
			);

		$repository
			->expects($this->once())
			->method('findSlots')
			->with(
				5,
				'',
				12
			)
			->willReturn([
				[
					"slot_id" => 12,
					"slot_name" => "Shelf C"
				]
			]);

		$service =
			new SlotService($repository);

		$result =
			$service->getSlots(
				5,
				'',
				12
			);

		$this->assertCount(1, $result);

		$this->assertSame(
			12,
			$result[0]["slot_id"]
		);
	}
}