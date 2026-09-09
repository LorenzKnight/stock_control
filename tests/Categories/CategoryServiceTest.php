<?php

use App\Categories\CategoryRepository;
use App\Categories\CategoryService;
use PHPUnit\Framework\TestCase;

final class CategoryServiceTest extends TestCase
{
	public function testReturnsRootCategories(): void
	{
		$repository = $this->createMock(
			CategoryRepository::class
		);

		$repository
			->expects($this->once())
			->method('findRootCategories')
			->with(10, 5)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"category_id" => 1,
						"category_name" => "Cars"
					]
				]
			]);

		$service = new CategoryService($repository);

		$result = $service->getRootCategories(10, 5);

		$this->assertCount(1, $result);
		$this->assertSame("Cars", $result[0]["category_name"]);
	}


	public function testThrowsWhenNoCategoriesExist(): void
	{
		$repository = $this->createMock(
			CategoryRepository::class
		);

		$repository
			->method('findRootCategories')
			->willReturn([
				"success" => false,
				"data" => []
			]);

		$service = new CategoryService($repository);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage(
			"No categories available."
		);

		$service->getRootCategories(10, 5);
	}


	public function testRejectsEmptyCategoryName(): void
	{
		$repository = $this->createMock(
			CategoryRepository::class
		);

		$service = new CategoryService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$service->createCategory(
			10,
			10,
			5,
			'   '
		);
	}
}