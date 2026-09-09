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

	public function testRejectsCustomerWithoutName(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->expects($this->never())
			->method('create');

		$service =
			new CustomerService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Customer name is required."
		);

		$service->createCustomer(
			10,
			5,
			[
				"customer_name" => "   ",
				"customer_birthday" => "1984-09-03"
			]
		);
	}


	public function testCreatesCustomer(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {
						return
							$data["customer_name"]
								=== "John" &&
							$data["customer_surname"]
								=== "Doe" &&
							$data["company_id"] === 5 &&
							$data["create_by"] === 10;
					}
				)
			)
			->willReturn(42);

		$repository
			->method('findOnboardingByUserId')
			->with(10)
			->willReturn([
				"client" => true,
				"client_reward_seen" => false
			]);

		$service =
			new CustomerService($repository);

		$result = $service->createCustomer(
			10,
			5,
			[
				"customer_name" => " John ",
				"customer_surname" => "Doe",
				"customer_birthday" =>
					"1984-09-03"
			]
		);

		$this->assertSame(
			42,
			$result["customer_id"]
		);

		$this->assertSame(
			"John Doe",
			$result["customer_name"]
		);

		$this->assertFalse(
			$result["show_reward_modal"]
		);
	}


	public function testFirstCustomerShowsReward(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->method('create')
			->willReturn(50);

		$repository
			->method('findOnboardingByUserId')
			->willReturn(null);

		$repository
			->expects($this->once())
			->method('createCustomerOnboarding')
			->with(10)
			->willReturn(true);

		$service =
			new CustomerService($repository);

		$result = $service->createCustomer(
			10,
			5,
			[
				"customer_name" => "Jane",
				"customer_surname" => "Doe",
				"customer_birthday" =>
					"1990-01-01"
			]
		);

		$this->assertTrue(
			$result["show_reward_modal"]
		);

		$this->assertSame(
			"first_client",
			$result["reward_type"]
		);
	}

	public function testRejectsUpdateWithoutCustomerId(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->expects($this->never())
			->method('update');

		$service =
			new CustomerService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Missing customer ID."
		);

		$service->updateCustomer(
			0,
			[
				"customer_name" => "John"
			]
		);
	}


	public function testRejectsUpdateWithoutCustomerName(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->expects($this->never())
			->method('update');

		$service =
			new CustomerService($repository);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Customer name is required."
		);

		$service->updateCustomer(
			42,
			[
				"customer_name" => "   "
			]
		);
	}


	public function testReturnsExistingCustomerImage(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->expects($this->once())
			->method('findImageById')
			->with(42)
			->willReturn("customer_42.webp");

		$service =
			new CustomerService($repository);

		$image =
			$service->getCustomerImage(42);

		$this->assertSame(
			"customer_42.webp",
			$image
		);
	}


	public function testUpdatesCustomerWithImage(): void
	{
		$repository =
			$this->createMock(CustomerRepository::class);

		$repository
			->expects($this->once())
			->method('update')
			->with(
				42,
				$this->callback(
					function (array $data): bool {
						return
							$data["customer_name"]
								=== "John" &&
							$data["customer_surname"]
								=== "Doe" &&
							$data["customer_status"]
								=== 1 &&
							$data["customer_image"]
								=== "new_image.webp";
					}
				)
			);

		$service =
			new CustomerService($repository);

		$service->updateCustomer(
			42,
			[
				"customer_name" => " John ",
				"customer_surname" => "Doe",
				"customer_status" => 1
			],
			"new_image.webp"
		);

		$this->assertTrue(true);
	}
}