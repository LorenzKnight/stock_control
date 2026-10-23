<?php

use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesConversationService;
use PHPUnit\Framework\TestCase;


final class SalesConversationServiceTest extends TestCase
{
	public function testCreatesInitialEmailConversation(): void
	{
		$repository =
			$this->createMock(
				SalesConversationRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByLeadId')
			->with(30)
			->willReturn([
				"success" => true,
				"data" => []
			]);


		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {

						$this->assertSame(
							30,
							$data["sales_lead_id"]
						);

						$this->assertSame(
							40,
							$data["sales_contact_id"]
						);

						$this->assertSame(
							"EMAIL",
							$data["channel"]
						);

						$this->assertNull(
							$data["subject"]
						);

						$this->assertSame(
							"OPEN",
							$data["status"]
						);


						return true;
					}
				)
			)
			->willReturn(
				20
			);


		$service =
			new SalesConversationService(
				$repository
			);


		$result =
			$service->getOrCreateInitialConversation(
				[
					"sales_lead_id" =>
						30,

					"primary_contact_id" =>
						40
				],
				[
					"sales_contact_id" =>
						40,

					"email" =>
						"anna@example.com"
				]
			);


		$this->assertSame(
			20,
			$result["conversation_id"]
		);

		$this->assertSame(
			"EMAIL",
			$result["channel"]
		);

		$this->assertTrue(
			$result["created"]
		);
	}


	public function testUsesLinkedInWhenEmailIsMissing(): void
	{
		$repository =
			$this->createMock(
				SalesConversationRepository::class
			);


		$repository
			->method('findByLeadId')
			->willReturn([
				"success" => true,
				"data" => []
			]);


		$repository
			->expects($this->once())
			->method('create')
			->with(
				$this->callback(
					function (array $data): bool {
						$this->assertSame(
							"LINKEDIN",
							$data["channel"]
						);

						return true;
					}
				)
			)
			->willReturn(
				21
			);


		$service =
			new SalesConversationService(
				$repository
			);


		$result =
			$service->getOrCreateInitialConversation(
				[
					"sales_lead_id" =>
						30,

					"primary_contact_id" =>
						40
				],
				[
					"sales_contact_id" =>
						40,

					"email" =>
						null,

					"linkedin_url" =>
						"https://linkedin.com/in/anna"
				]
			);


		$this->assertSame(
			"LINKEDIN",
			$result["channel"]
		);
	}


	public function testReturnsExistingConversation(): void
	{
		$repository =
			$this->createMock(
				SalesConversationRepository::class
			);


		$repository
			->expects($this->once())
			->method('findByLeadId')
			->with(30)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"conversation_id" =>
							20,

						"sales_contact_id" =>
							40,

						"channel" =>
							"EMAIL"
					]
				]
			]);


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesConversationService(
				$repository
			);


		$result =
			$service->getOrCreateInitialConversation(
				[
					"sales_lead_id" =>
						30,

					"primary_contact_id" =>
						40
				],
				[
					"sales_contact_id" =>
						40,

					"email" =>
						"anna@example.com"
				]
			);


		$this->assertSame(
			20,
			$result["conversation_id"]
		);

		$this->assertFalse(
			$result["created"]
		);
	}


	public function testRejectsContactWithoutSupportedChannel(): void
	{
		$repository =
			$this->createMock(
				SalesConversationRepository::class
			);


		$repository
			->method('findByLeadId')
			->willReturn([
				"success" => true,
				"data" => []
			]);


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesConversationService(
				$repository
			);


		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			"Primary contact has no supported outreach channel."
		);


		$service->getOrCreateInitialConversation(
			[
				"sales_lead_id" =>
					30,

				"primary_contact_id" =>
					40
			],
			[
				"sales_contact_id" =>
					40
			]
		);
	}


	public function testRejectsDifferentPrimaryContact(): void
	{
		$repository =
			$this->createMock(
				SalesConversationRepository::class
			);


		$repository
			->expects($this->never())
			->method('findByLeadId');


		$repository
			->expects($this->never())
			->method('create');


		$service =
			new SalesConversationService(
				$repository
			);


		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			"Sales contact does not match lead primary contact."
		);


		$service->getOrCreateInitialConversation(
			[
				"sales_lead_id" =>
					30,

				"primary_contact_id" =>
					40
			],
			[
				"sales_contact_id" =>
					99,

				"email" =>
					"wrong@example.com"
			]
		);
	}
}