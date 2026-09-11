<?php

use App\Products\ProductRepository;
use App\Products\ProductService;
use PHPUnit\Framework\TestCase;

final class ProductServiceTest extends TestCase
{
	public function testRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->expects($this->never())
			->method('findProducts');

		$service =
			new ProductService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"No company selected or linked to this user."
		);

		$service->getProducts(0);
	}


	public function testReturnsEnrichedProducts(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$rawProduct = [
			"product_id" => 10,
			"company_id" => 5,
			"product_name" => "Phone",
			"product_mark" => 3
		];

		$enrichedProduct = [
			"product_id" => 10,
			"company_id" => 5,
			"product_name" => "Phone",
			"product_mark" => 3,
			"mark_name" => "Apple",
			"model_name" => "iPhone",
			"submodel_name" => "15 Pro"
		];

		$filters = [
			"search" => "Phone",
			"mark" => 3
		];

		$repository
			->expects($this->once())
			->method('findProducts')
			->with(
				5,
				$filters
			)
			->willReturn([
				$rawProduct
			]);

		$repository
			->expects($this->once())
			->method('enrichProduct')
			->with(
				$rawProduct,
				5
			)
			->willReturn(
				$enrichedProduct
			);

		$service =
			new ProductService(
				$repository
			);

		$result =
			$service->getProducts(
				5,
				$filters
			);

		$this->assertCount(
			1,
			$result
		);

		$this->assertSame(
			"Phone",
			$result[0]["product_name"]
		);

		$this->assertSame(
			"Apple",
			$result[0]["mark_name"]
		);
	}


	public function testBarcodeReturnsNotFound(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$repository
			->expects($this->once())
			->method('findByBarcode')
			->with(
				5,
				'123456'
			)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method('enrichProduct');

		$service =
			new ProductService(
				$repository
			);

		$result =
			$service->getProductByBarcode(
				5,
				'123456'
			);

		$this->assertFalse(
			$result["found"]
		);

		$this->assertNull(
			$result["product"]
		);
	}


	public function testReturnsProductByBarcode(): void
	{
		$repository =
			$this->createMock(
				ProductRepository::class
			);

		$rawProduct = [
			"product_id" => 10,
			"company_id" => 5,
			"product_name" => "Phone",
			"hs_code" => "123456"
		];

		$enrichedProduct =
			$rawProduct + [
				"mark_name" => "Apple",
				"model_name" => "iPhone",
				"submodel_name" => "15 Pro"
			];

		$repository
			->expects($this->once())
			->method('findByBarcode')
			->with(
				5,
				'123456'
			)
			->willReturn(
				$rawProduct
			);

		$repository
			->expects($this->once())
			->method('enrichProduct')
			->with(
				$rawProduct,
				5
			)
			->willReturn(
				$enrichedProduct
			);

		$service =
			new ProductService(
				$repository
			);

		$result =
			$service->getProductByBarcode(
				5,
				'123456'
			);

		$this->assertTrue(
			$result["found"]
		);

		$this->assertSame(
			"Phone",
			$result["product"]["product_name"]
		);
	}
}