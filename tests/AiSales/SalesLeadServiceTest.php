<?php

use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesLeadService;
use PHPUnit\Framework\TestCase;


final class SalesLeadServiceTest extends TestCase
{
	public function testCreateFromQualifiedCompanyCreatesSalesLead(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
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
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {

						$this->assertSame(
							77,
							$data["sales_company_id"]
						);

						$this->assertSame(
							101,
							$data["primary_contact_id"]
						);

						$this->assertSame(
							"LATAM",
							$data["market"]
						);

						$this->assertSame(
							"Dominican Republic",
							$data["country"]
						);

						$this->assertSame(
							"en",
							$data["language"]
						);

						$this->assertSame(
							"NEW",
							$data["stage"]
						);

						$this->assertSame(
							75,
							$data["score"]
						);

						$this->assertSame(
							"Strong warehousing and inventory relevance.",
							$data["score_reason"]
						);

						$this->assertSame(
							"AI",
							$data["source"]
						);

						$this->assertSame(
							"Generate first outreach",
							$data["next_action"]
						);

						$this->assertSame(
							"DP World provides logistics and warehousing services.",
							$data["ai_summary"]
						);

						$this->assertSame(
							1,
							$data["created_by"]
						);


						return true;
					}
				)
			)
			->willReturn(
				500
			);


		$service =
			new SalesLeadService(
				$repository
			);


		$result =
			$service->createFromQualifiedCompany(
				[
					"sales_company_id" =>
						77,

					"market" =>
						"LATAM",

					"country" =>
						"Dominican Republic",

					"language" =>
						"en",

					"qualification_status" =>
						"COMPLETED",

					"qualification_level" =>
						"HIGH",

					"qualification_score" =>
						75,

					"qualification_reason" =>
						"Strong warehousing and inventory relevance.",

					"contact_search_status" =>
						"COMPLETED",

					"research_summary" =>
						"DP World provides logistics and warehousing services."
				],
				[
					"sales_contact_id" =>
						101,

					"is_primary" =>
						true
				],
				1
			);


		$this->assertSame(
			500,
			$result["sales_lead_id"]
		);

		$this->assertTrue(
			$result["created"]
		);
	}


	public function testCreateFromQualifiedCompanyReturnsExistingLead(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
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
						[
							[
								"sales_lead_id" =>
									900,

								"sales_company_id" =>
									77
							]
						]
				]
			);


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$result =
			$service->createFromQualifiedCompany(
				[
					"sales_company_id" =>
						77,

					"qualification_status" =>
						"COMPLETED",

					"qualification_level" =>
						"HIGH",

					"qualification_score" =>
						80,

					"qualification_reason" =>
						"Strong inventory relevance.",

					"contact_search_status" =>
						"COMPLETED"
				],
				[
					"sales_contact_id" =>
						101,

					"is_primary" =>
						true
				],
				1
			);


		$this->assertSame(
			900,
			$result["sales_lead_id"]
		);

		$this->assertFalse(
			$result["created"]
		);
	}


	public function testCreateFromQualifiedCompanyAcceptsMediumQualification(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
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
			->expects($this->once())
			->method('create')
			->willReturn(
				501
			);


		$service =
			new SalesLeadService(
				$repository
			);


		$result =
			$service->createFromQualifiedCompany(
				[
					"sales_company_id" =>
						77,

					"qualification_status" =>
						"COMPLETED",

					"qualification_level" =>
						"MEDIUM",

					"qualification_score" =>
						55,

					"qualification_reason" =>
						"Moderate inventory relevance.",

					"contact_search_status" =>
						"COMPLETED"
				],
				[
					"sales_contact_id" =>
						101,

					"is_primary" =>
						true
				],
				1
			);


		$this->assertSame(
			501,
			$result["sales_lead_id"]
		);

		$this->assertTrue(
			$result["created"]
		);
	}


	public function testCreateFromQualifiedCompanyRejectsIncompleteQualification(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			'Company qualification must be completed before creating a sales lead.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77,

				"qualification_status" =>
					"PENDING",

				"qualification_level" =>
					"HIGH",

				"contact_search_status" =>
					"COMPLETED"
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					true
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsLowQualification(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			'Company is not qualified for a sales lead.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77,

				"qualification_status" =>
					"COMPLETED",

				"qualification_level" =>
					"LOW",

				"contact_search_status" =>
					"COMPLETED"
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					true
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsIncompleteContactSearch(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			'Contact search must be completed before creating a sales lead.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77,

				"qualification_status" =>
					"COMPLETED",

				"qualification_level" =>
					"HIGH",

				"contact_search_status" =>
					"NOT_FOUND"
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					true
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsMissingPrimaryContact(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Valid primary contact is required.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77,

				"qualification_status" =>
					"COMPLETED",

				"qualification_level" =>
					"HIGH",

				"contact_search_status" =>
					"COMPLETED"
			],
			[
				"sales_contact_id" =>
					0,

				"is_primary" =>
					true
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsNonPrimaryContact(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Primary sales contact is required.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77,

				"qualification_status" =>
					"COMPLETED",

				"qualification_level" =>
					"HIGH",

				"contact_search_status" =>
					"COMPLETED"
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					false
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsInvalidScore(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
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
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Invalid qualification score.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77,

				"qualification_status" =>
					"COMPLETED",

				"qualification_level" =>
					"HIGH",

				"qualification_score" =>
					101,

				"qualification_reason" =>
					"Test reason.",

				"contact_search_status" =>
					"COMPLETED"
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					true
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsEmptyQualificationReason(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
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
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Qualification reason is required.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77,

				"qualification_status" =>
					"COMPLETED",

				"qualification_level" =>
					"HIGH",

				"qualification_score" =>
					75,

				"qualification_reason" =>
					"   ",

				"contact_search_status" =>
					"COMPLETED"
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					true
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Invalid sales company ID.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					0
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					true
			],
			1
		);
	}


	public function testCreateFromQualifiedCompanyRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				SalesLeadRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByCompanyId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesLeadService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Invalid user ID.'
		);


		$service->createFromQualifiedCompany(
			[
				"sales_company_id" =>
					77
			],
			[
				"sales_contact_id" =>
					101,

				"is_primary" =>
					true
			],
			0
		);
	}
}