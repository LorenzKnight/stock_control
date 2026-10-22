<?php

use App\AiSales\SalesContactRepository;
use App\AiSales\SalesContactService;
use PHPUnit\Framework\TestCase;


final class SalesContactServiceTest extends TestCase
{
	public function testSaveFoundContactsCreatesVerifiedContacts(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByCompanyId')
			->with(
				77
			)
			->willReturn(
				[
					"success" =>
						true,

					"data" =>
						[]
				]
			);


		$repository
			->expects($this->exactly(2))
			->method('create')
			->willReturnCallback(
				function (array $data): int {

					if (
						$data["first_name"] ===
						"Pedro"
					) {
						$this->assertSame(
							"Martinez",
							$data["last_name"]
						);

						$this->assertSame(
							"Supply Chain Director",
							$data["job_title"]
						);

						$this->assertSame(
							"https://www.linkedin.com/in/pedro-martinez",
							$data["linkedin_url"]
						);

						$this->assertSame(
							"LINKEDIN",
							$data["source_type"]
						);

						$this->assertTrue(
							$data["is_primary"]
						);

						$this->assertFalse(
							$data["do_not_contact"]
						);

						$this->assertSame(
							1,
							$data["created_by"]
						);

						return 101;
					}


					$this->assertSame(
						"Anna",
						$data["first_name"]
					);

					$this->assertSame(
						"Operations Manager",
						$data["job_title"]
					);

					$this->assertSame(
						"COMPANY_WEBSITE",
						$data["source_type"]
					);

					$this->assertFalse(
						$data["is_primary"]
					);


					return 102;
				}
			);


		$service =
			new SalesContactService(
				$repository
			);


		$result =
			$service->saveFoundContacts(
				77,
				[
					[
						"first_name" =>
							"Pedro",

						"last_name" =>
							"Martinez",

						"job_title" =>
							"Supply Chain Director",

						"email" =>
							null,

						"phone" =>
							null,

						"linkedin_url" =>
							"https://www.linkedin.com/in/pedro-martinez",

						"source_url" =>
							"https://www.linkedin.com/in/pedro-martinez",

						"source_type" =>
							"LINKEDIN",

						"language" =>
							"en",

						"is_primary" =>
							true
					],
					[
						"first_name" =>
							"Anna",

						"last_name" =>
							"Svensson",

						"job_title" =>
							"Operations Manager",

						"email" =>
							null,

						"phone" =>
							null,

						"linkedin_url" =>
							null,

						"source_url" =>
							"https://example.com/anna",

						"source_type" =>
							"COMPANY_WEBSITE",

						"language" =>
							"en",

						"is_primary" =>
							false
					]
				],
				1
			);


		$this->assertCount(
			2,
			$result
		);

		$this->assertSame(
			101,
			$result[0]["sales_contact_id"]
		);

		$this->assertSame(
			102,
			$result[1]["sales_contact_id"]
		);
	}


	public function testSaveFoundContactsSkipsDuplicateByEmail(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByCompanyId')
			->willReturn(
				[
					"success" =>
						true,

					"data" =>
						[
							[
								"first_name" =>
									"Anna",

								"last_name" =>
									"Svensson",

								"email" =>
									"anna@example.com",

								"linkedin_url" =>
									null
							]
						]
				]
			);


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesContactService(
				$repository
			);


		$result =
			$service->saveFoundContacts(
				77,
				[
					[
						"first_name" =>
							"Different",

						"last_name" =>
							"Name",

						"email" =>
							"ANNA@example.com",

						"is_primary" =>
							true
					]
				],
				1
			);


		$this->assertSame(
			[],
			$result
		);
	}


	public function testSaveFoundContactsSkipsDuplicateByLinkedin(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByCompanyId')
			->willReturn(
				[
					"success" =>
						true,

					"data" =>
						[
							[
								"first_name" =>
									"Pedro",

								"last_name" =>
									"Martinez",

								"email" =>
									null,

								"linkedin_url" =>
									"https://www.linkedin.com/in/pedro"
							]
						]
				]
			);


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesContactService(
				$repository
			);


		$result =
			$service->saveFoundContacts(
				77,
				[
					[
						"first_name" =>
							"Pedro",

						"last_name" =>
							"Martinez",

						"linkedin_url" =>
							"HTTPS://WWW.LINKEDIN.COM/IN/PEDRO"
					]
				],
				1
			);


		$this->assertSame(
			[],
			$result
		);
	}


	public function testSaveFoundContactsSkipsDuplicateByName(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByCompanyId')
			->willReturn(
				[
					"success" =>
						true,

					"data" =>
						[
							[
								"first_name" =>
									"Anna",

								"last_name" =>
									"Svensson",

								"email" =>
									null,

								"linkedin_url" =>
									null
							]
						]
				]
			);


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesContactService(
				$repository
			);


		$result =
			$service->saveFoundContacts(
				77,
				[
					[
						"first_name" =>
							"ANNA",

						"last_name" =>
							"SVENSSON"
					]
				],
				1
			);


		$this->assertSame(
			[],
			$result
		);
	}


	public function testSaveFoundContactsSkipsContactWithoutFirstName(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByCompanyId')
			->willReturn(
				[
					"success" =>
						true,

					"data" =>
						[]
				]
			);


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesContactService(
				$repository
			);


		$result =
			$service->saveFoundContacts(
				77,
				[
					[
						"first_name" =>
							"   ",

						"job_title" =>
							"Operations Manager"
					]
				],
				1
			);


		$this->assertSame(
			[],
			$result
		);
	}


	public function testSaveFoundContactsRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$service =
			new SalesContactService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Invalid sales company ID.'
		);


		$service->saveFoundContacts(
			0,
			[],
			1
		);
	}


	public function testSaveFoundContactsRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$service =
			new SalesContactService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Invalid user ID.'
		);


		$service->saveFoundContacts(
			77,
			[],
			0
		);
	}


		public function testGetPrimaryContactByCompanyReturnsPrimaryContact(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByCompanyId')
			->with(
				77,
				''
			)
			->willReturn(
				[
					"success" =>
						true,

					"data" =>
						[
							[
								"sales_contact_id" =>
									101,

								"first_name" =>
									"Anna",

								"last_name" =>
									"Svensson",

								"is_primary" =>
									false,

								"do_not_contact" =>
									false
							],
							[
								"sales_contact_id" =>
									102,

								"first_name" =>
									"Pedro",

								"last_name" =>
									"Martinez",

								"is_primary" =>
									"t",

								"do_not_contact" =>
									false
							]
						]
				]
			);


		$service =
			new SalesContactService(
				$repository
			);


		$contact =
			$service->getPrimaryContactByCompany(
				77
			);


		$this->assertNotNull(
			$contact
		);

		$this->assertSame(
			102,
			$contact["sales_contact_id"]
		);

		$this->assertSame(
			"Pedro Martinez",
			$contact["full_name"]
		);

		$this->assertTrue(
			$contact["is_primary"]
		);
	}


	public function testGetPrimaryContactByCompanyReturnsNullWhenNoPrimaryExists(): void
	{
		$repository =
			$this->createMock(
				SalesContactRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByCompanyId')
			->willReturn(
				[
					"success" =>
						true,

					"data" =>
						[
							[
								"sales_contact_id" =>
									101,

								"first_name" =>
									"Anna",

								"last_name" =>
									"Svensson",

								"is_primary" =>
									false,

								"do_not_contact" =>
									false
							]
						]
				]
			);


		$service =
			new SalesContactService(
				$repository
			);


		$contact =
			$service->getPrimaryContactByCompany(
				77
			);


		$this->assertNull(
			$contact
		);
	}
}