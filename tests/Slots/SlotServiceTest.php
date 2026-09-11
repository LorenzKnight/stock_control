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

    public function testRejectsCreateWithoutName(): void
    {
        $repository =
            $this->createMock(
                SlotRepository::class
            );

        $repository
            ->expects($this->never())
            ->method('create');

        $service =
            new SlotService($repository);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            "Slot Name is required."
        );

        $service->createSlot(
            10,
            5,
            [
                "slot_name" => " "
            ]
        );
    }


    public function testRejectsDuplicateSlotName(): void
    {
        $repository =
            $this->createMock(
                SlotRepository::class
            );

        $repository
            ->expects($this->once())
            ->method('findIdByName')
            ->with(
                5,
                'Rack A'
            )
            ->willReturn(12);

        $repository
            ->expects($this->never())
            ->method('create');

        $service =
            new SlotService($repository);

        $this->expectException(
            Exception::class
        );

        $this->expectExceptionMessage(
            "A slot with this name already exists."
        );

        $service->createSlot(
            10,
            5,
            [
                "slot_name" => "Rack A"
            ]
        );
    }


    public function testCreatesSlot(): void
    {
        $repository =
            $this->createMock(
                SlotRepository::class
            );

        $repository
            ->expects($this->once())
            ->method('findIdByName')
            ->with(
                5,
                'Rack A'
            )
            ->willReturn(null);

        $repository
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(
                    function (array $data): bool {
                        return
                            $data["company_id"] === 5 &&
                            $data["slot_name"] ===
                                "Rack A" &&
                            $data["current_capacity"] ===
                                10 &&
                            $data["max_capacity"] ===
                                100 &&
                            $data["slot_description"] ===
                                "Main rack" &&
                            $data["status"] === 1 &&
                            $data["created_by"] === 10;
                    }
                )
            )
            ->willReturn(22);

        $service =
            new SlotService($repository);

        $result =
            $service->createSlot(
                10,
                5,
                [
                    "slot_name" =>
                        " Rack A ",

                    "current_capacity" =>
                        10,

                    "max_capacity" =>
                        100,

                    "slot_description" =>
                        " Main rack ",

                    "status" => 1
                ]
            );

        $this->assertSame(
                22,
                $result
            );
    }

    public function testRejectsUpdateWithInvalidSlotId(): void
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

		$this->expectExceptionMessage(
			"Invalid slot ID."
		);

		$service->updateSlot(
			5,
			0,
			[
				"slot_name" => "Rack A"
			]
		);
	}


	public function testRejectsUpdateWhenSlotDoesNotExist(): void
	{
		$repository =
			$this->createMock(
				SlotRepository::class
			);

		$repository
			->expects($this->once())
			->method('findSlots')
			->with(5, '', 12)
			->willReturn([]);

		$repository
			->expects($this->never())
			->method('update');

		$service =
			new SlotService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Slot not found."
		);

		$service->updateSlot(
			5,
			12,
			[
				"slot_name" => "Rack A"
			]
		);
	}


	public function testRejectsDuplicateSlotNameOnUpdate(): void
	{
		$repository =
			$this->createMock(
				SlotRepository::class
			);

		$repository
			->method('findSlots')
			->with(5, '', 12)
			->willReturn([
				[
					"slot_id" => 12,
					"company_id" => 5,
					"slot_name" => "Rack A"
				]
			]);

		$repository
			->expects($this->once())
			->method('findIdByName')
			->with(5, 'Rack B')
			->willReturn(20);

		$repository
			->expects($this->never())
			->method('update');

		$service =
			new SlotService($repository);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"A slot with this name already exists."
		);

		$service->updateSlot(
			5,
			12,
			[
				"slot_name" => "Rack B"
			]
		);
	}


	public function testUpdatesSlot(): void
	{
		$repository =
			$this->createMock(
				SlotRepository::class
			);

		$repository
			->method('findSlots')
			->with(5, '', 12)
			->willReturn([
				[
					"slot_id" => 12,
					"company_id" => 5,
					"slot_name" => "Rack A"
				]
			]);

		$repository
			->method('findIdByName')
			->with(5, 'Rack A')
			->willReturn(12);

		$repository
			->expects($this->once())
			->method('update')
			->with(
				5,
				12,
				$this->callback(
					function (array $data): bool {
						return
							$data["slot_name"] ===
								"Rack A" &&

							$data["current_capacity"] ===
								20 &&

							$data["max_capacity"] ===
								150 &&

							$data["slot_description"] ===
								"Updated rack" &&

							$data["status"] === 1;
					}
				)
			);

		$service =
			new SlotService($repository);

		
		$service->updateSlot(
			5,
			12,
			[
				"slot_name" =>
					" Rack A ",

				"current_capacity" =>
					20,

				"max_capacity" =>
					150,

				"slot_description" =>
					" Updated rack ",

				"status" => 1
			]
		);
	}
}