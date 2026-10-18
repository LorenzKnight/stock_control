<?php

use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesCompanyService;
use PHPUnit\Framework\TestCase;


final class SalesCompanyServiceTest extends TestCase
{
	public function testCreateProspectStartsPendingResearch(): void
	{
		$repository =
			$this->createMock(
				SalesCompanyRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByDomain')
			->with(
				'eps.com.do'
			)
			->willReturn(
				null
			);


		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {

						$this->assertSame(
							'EPS',
							$data['company_name']
						);

						$this->assertSame(
							'eps.com.do',
							$data['domain']
						);

						$this->assertSame(
							'LATAM',
							$data['market']
						);

						$this->assertSame(
							'Dominican Republic',
							$data['country']
						);

						$this->assertSame(
							'Logistics',
							$data['industry']
						);

						$this->assertSame(
							'WEB',
							$data['source']
						);

						$this->assertFalse(
							$data['ai_researched']
						);

						$this->assertSame(
							'PENDING',
							$data['research_status']
						);


						return true;
					}
				)
			)
			->willReturn(
				77
			);


		$service =
			new SalesCompanyService(
				$repository
			);


		$result =
			$service->createProspect(
				[
					'name' =>
						'EPS',

					'website' =>
						'https://www.eps.com.do',

					'domain' =>
						'eps.com.do',

					'country' =>
						'Dominican Republic',

					'industry' =>
						'Logistics',

					'source_type' =>
						'DISCOVERY',

					'source_url' =>
						'https://www.eps.com.do',

					'source_text' =>
						'Courier and logistics services.'
				],
				1,
				'LATAM',
				null
			);


		$this->assertSame(
			77,
			$result['sales_company_id']
		);

		$this->assertTrue(
			$result['created']
		);
	}


	public function testMarkResearchingUpdatesStatus(): void
	{
		$repository =
			$this->createMock(
				SalesCompanyRepository::class
			);


		$repository
			->expects($this->once())
			->method('updateResearch')
			->with(
				77,
				$this->callback(
					function (array $data): bool {

						$this->assertSame(
							'RESEARCHING',
							$data['research_status']
						);

						$this->assertMatchesRegularExpression(
							'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
							$data['updated_at']
						);


						return true;
					}
				)
			);


		$service =
			new SalesCompanyService(
				$repository
			);


		$service->markResearching(
			77
		);
	}


	public function testCompleteResearchStoresResearchData(): void
	{
		$repository =
			$this->createMock(
				SalesCompanyRepository::class
			);


		$repository
			->expects($this->once())
			->method('updateResearch')
			->with(
				77,
				$this->callback(
					function (array $data): bool {

						$this->assertSame(
							'Santo Domingo',
							$data['city']
						);

						$this->assertSame(
							'Logistics',
							$data['industry']
						);

						$this->assertSame(
							'es',
							$data['language']
						);

						$this->assertSame(
							'EPS provides courier and logistics services.',
							$data['research_summary']
						);

						$this->assertSame(
							'The company handles cargo and physical goods distribution.',
							$data['research_inventory_context']
						);

						$this->assertSame(
							[
								'https://www.eps.com.do/',
								'https://www.eps.com.do/services/'
							],
							json_decode(
								$data['research_sources'],
								true
							)
						);

						$this->assertSame(
							'COMPLETED',
							$data['research_status']
						);

						$this->assertTrue(
							$data['ai_researched']
						);

						$this->assertMatchesRegularExpression(
							'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
							$data['researched_at']
						);

						$this->assertSame(
							$data['researched_at'],
							$data['updated_at']
						);


						return true;
					}
				)
			);


		$service =
			new SalesCompanyService(
				$repository
			);


		$service->completeResearch(
			77,
			[
				'city' =>
					null,

				'industry' =>
					'Logistics',

				'language' =>
					null
			],
			[
				'summary' =>
					'EPS provides courier and logistics services.',

				'inventory_context' =>
					'The company handles cargo and physical goods distribution.',

				'city' =>
					'Santo Domingo',

				'industry' =>
					'Logistics',

				'language' =>
					'es',

				'sources' =>
					[
						'https://www.eps.com.do/',
						'https://www.eps.com.do/services/'
					]
			]
		);
	}


	public function testCompleteResearchKeepsExistingCompanyDataWhenResearchReturnsNull(): void
	{
		$repository =
			$this->createMock(
				SalesCompanyRepository::class
			);


		$repository
			->expects($this->once())
			->method('updateResearch')
			->with(
				77,
				$this->callback(
					function (array $data): bool {

						$this->assertSame(
							'Santo Domingo',
							$data['city']
						);

						$this->assertSame(
							'Logistics',
							$data['industry']
						);

						$this->assertSame(
							'es',
							$data['language']
						);


						return true;
					}
				)
			);


		$service =
			new SalesCompanyService(
				$repository
			);


		$service->completeResearch(
			77,
			[
				'city' =>
					'Santo Domingo',

				'industry' =>
					'Logistics',

				'language' =>
					'es'
			],
			[
				'summary' =>
					'EPS provides logistics services.',

				'inventory_context' =>
					null,

				'city' =>
					null,

				'industry' =>
					null,

				'language' =>
					null,

				'sources' =>
					[
						'https://www.eps.com.do/'
					]
			]
		);
	}


	public function testCompleteResearchRejectsEmptySummary(): void
	{
		$repository =
			$this->createMock(
				SalesCompanyRepository::class
			);


		$repository
			->expects($this->never())
			->method(
				'updateResearch'
			);


		$service =
			new SalesCompanyService(
				$repository
			);


		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			'Research summary is required.'
		);


		$service->completeResearch(
			77,
			[],
			[
				'summary' =>
					'   '
			]
		);
	}


	public function testMarkResearchFailedUpdatesStatus(): void
	{
		$repository =
			$this->createMock(
				SalesCompanyRepository::class
			);


		$repository
			->expects($this->once())
			->method('updateResearch')
			->with(
				77,
				$this->callback(
					function (array $data): bool {

						$this->assertSame(
							'FAILED',
							$data['research_status']
						);

						$this->assertFalse(
							$data['ai_researched']
						);

						$this->assertMatchesRegularExpression(
							'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
							$data['updated_at']
						);


						return true;
					}
				)
			);


		$service =
			new SalesCompanyService(
				$repository
			);


		$service->markResearchFailed(
			77
		);
	}
}