<?php

use App\ProductTypes\ProductTypeRepository;
use App\ProductTypes\ProductTypeService;
use PHPUnit\Framework\TestCase;

final class ProductTypeServiceTest extends TestCase
{
	public function testRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				ProductTypeRepository::class
			);

		$repository
			->expects($this->never())
			->method('findOwnerUserId');

		$service =
			new ProductTypeService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Unauthorized access. User not found or invalid token."
		);

		$service->getProductTypes(0);
	}


	public function testReturnsProductTypes(): void
	{
		$repository =
			$this->createMock(
				ProductTypeRepository::class
			);

		$repository
			->expects($this->once())
			->method('findOwnerUserId')
			->with(10)
			->willReturn(5);

		$repository
			->expects($this->once())
			->method('findProductTypes')
			->with(5, 3)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"product_type_id" => 1,
						"product_type_name" => "Electronics",
						"company_id" => 3
					],
					[
						"product_type_id" => 2,
						"product_type_name" => "Accessories",
						"company_id" => 3
					]
				]
			]);

		$service =
			new ProductTypeService($repository);

		$result =
			$service->getProductTypes(
				10,
				3
			);

		$this->assertCount(
			2,
			$result
		);

		$this->assertSame(
			"Electronics",
			$result[0]["product_type_name"]
		);
	}


	public function testReturnsEmptyArrayWhenNoProductTypesExist(): void
	{
		$repository =
			$this->createMock(
				ProductTypeRepository::class
			);

		$repository
			->expects($this->once())
			->method('findOwnerUserId')
			->with(10)
			->willReturn(10);

		$repository
			->expects($this->once())
			->method('findProductTypes')
			->with(10, null)
			->willReturn([
				"success" => false,
				"message" => "No records found",
				"data" => []
			]);

		$service =
			new ProductTypeService($repository);

		$result =
			$service->getProductTypes(10);

		$this->assertSame(
			[],
			$result
		);
	}

    
    public function testRejectsCreateWithInvalidUserId(): void
    {
        $repository =
            $this->createMock(
                ProductTypeRepository::class
            );

        $repository
            ->expects($this->never())
            ->method('findExisting');

        $repository
            ->expects($this->never())
            ->method('create');

        $service =
            new ProductTypeService($repository);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            "Unauthorized access: invalid or missing token."
        );

        $service->createProductType(
            0,
            "Electronics"
        );
    }


    public function testRejectsCreateWithoutName(): void
    {
        $repository =
            $this->createMock(
                ProductTypeRepository::class
            );

        $repository
            ->expects($this->never())
            ->method('findExisting');

        $repository
            ->expects($this->never())
            ->method('create');

        $service =
            new ProductTypeService($repository);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            "Type name is required."
        );

        $service->createProductType(
            10,
            "   "
        );
    }


    public function testRejectsCreateWithNameTooLong(): void
    {
        $repository =
            $this->createMock(
                ProductTypeRepository::class
            );

        $repository
            ->expects($this->never())
            ->method('findExisting');

        $repository
            ->expects($this->never())
            ->method('create');

        $service =
            new ProductTypeService($repository);

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            "Type name too long (max 100)."
        );

        $service->createProductType(
            10,
            str_repeat("A", 101)
        );
    }


    public function testReturnsExistingProductType(): void
    {
        $repository =
            $this->createMock(
                ProductTypeRepository::class
            );

        $repository
            ->expects($this->once())
            ->method('findExisting')
            ->with(
                10,
                "Electronics",
                3
            )
            ->willReturn([
                "product_type_id" => 7,
                "product_type_name" => "Electronics"
            ]);

        $repository
            ->expects($this->never())
            ->method('create');

        $service =
            new ProductTypeService($repository);

        $result =
            $service->createProductType(
                10,
                "Electronics",
                3
            );

        $this->assertSame(
            7,
            $result["id"]
        );

        $this->assertSame(
            "Electronics",
            $result["name"]
        );

        $this->assertTrue(
            $result["already_exists"]
        );
    }


    public function testCreatesProductType(): void
    {
        $repository =
            $this->createMock(
                ProductTypeRepository::class
            );

        $repository
            ->expects($this->once())
            ->method('findExisting')
            ->with(
                10,
                "Electronics",
                3
            )
            ->willReturn(null);

        $repository
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(
                    function (array $data): bool {
                        return
                            $data["user_id"] === 10 &&
                            $data["product_type_name"] === "Electronics" &&
                            $data["create_by"] === 10 &&
                            $data["company_id"] === 3 &&
                            !empty($data["created_at"]);
                    }
                )
            )
            ->willReturn(25);

        $service =
            new ProductTypeService($repository);

        $result =
            $service->createProductType(
                10,
                " Electronics ",
                3
            );

        $this->assertSame(
            25,
            $result["id"]
        );

        $this->assertSame(
            "Electronics",
            $result["name"]
        );

        $this->assertFalse(
            $result["already_exists"]
        );
    }
}