<?php

use App\Customers\CustomerRepository;
use App\Customers\CustomerService;
use PHPUnit\Framework\TestCase;

final class CustomerServiceTest extends TestCase
{
	public function testRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$service =
			new CustomerService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$service->getCustomers(0);
	}


	public function testReturnsFormattedCustomers(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->expects($this->once())
			->method('findCompanyIdByUserId')
			->with(10)
			->willReturn(5);

		$repository
			->expects($this->once())
			->method('findCustomers')
			->with(5, 'john')
			->willReturn([
				"success" => true,
				"data" => [
					[
						"customer_id" => 1,
						"customer_name" => "John",
						"customer_surname" => "Doe",
						"customer_document_no" => "ABC123",
						"customer_address" => "Main Street",
						"customer_status" => 1,
						"customer_document_type" => 2,
						"customer_image" => "john.webp"
					]
				]
			]);

		$service =
			new CustomerService($repository);

		$customers =
			$service->getCustomers(
				10,
				' john '
			);

		$this->assertCount(1, $customers);

		$this->assertSame(
			"John Doe",
			$customers[0]["full_name"]
		);

		$this->assertSame(
			"ABC123",
			$customers[0]["document_no"]
		);

		$this->assertSame(
			"Main Street",
			$customers[0]["address"]
		);

		$this->assertSame(
			"Active",
			$customers[0]["status"]
		);

		$this->assertSame(
			"Passport",
			$customers[0]["document_type"]
		);
	}
}