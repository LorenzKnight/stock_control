<?php

use App\AiSales\SalesMessageRepository;
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesCompanyRepository;
use App\AiSales\SalesContactRepository;
use App\AiSales\AiSalesAgentClient;
use App\AiSales\SalesMessageService;
use PHPUnit\Framework\TestCase;

final class SalesMessageServiceTest extends TestCase
{
	private function validMessage(
		array $overrides = []
	): array {
		return array_replace(
			[
				"sales_message_id" => 10,
				"conversation_id" => 20,
				"direction" => "OUTBOUND",
				"sender_type" => "AI",
				"subject" => "Test subject",
				"message" => "Test message",
				"status" => "APPROVED",
				"ai_generated" => true,
				"approved" => true,
				"approved_by_user_id" => 1,
				"approved_at" =>
					"2026-10-01 10:00:00",
				"sent_at" => null,
				"received_at" => null,
				"created_at" =>
					"2026-10-01 09:00:00",
				"updated_at" =>
					"2026-10-01 10:00:00"
			],
			$overrides
		);
	}


	private function validConversation(
		array $overrides = []
	): array {
		return array_replace(
			[
				"conversation_id" => 20,
				"sales_lead_id" => 30,
				"sales_contact_id" => 40,
				"channel" => "EMAIL",
				"subject" =>
					"Inventory management solution for Demo Logistics AB",
				"status" => "OPEN",
				"last_message_at" => null
			],
			$overrides
		);
	}


	private function validLead(
		array $overrides = []
	): array {
		return array_replace(
			[
				"sales_lead_id" => 30,
				"sales_company_id" => 50,
				"primary_contact_id" => 40,
				"market" => "SWEDEN",
				"country" => "Sweden",
				"language" => "en",
				"stage" => "NEW",
				"last_contact_at" => null,
				"next_action" => null,
				"next_action_at" => null
			],
			$overrides
		);
	}


	private function validCompany(
		array $overrides = []
	): array {
		return array_replace(
			[
				"sales_company_id" => 50,
				"company_name" => "Demo Logistics AB",
				"website" => null,
				"domain" => null,
				"market" => "SWEDEN",
				"country" => "Sweden",
				"country_code" => "SE",
				"city" => "Göteborg",
				"industry" => "Logistics",
				"language" => "en",
				"description" => null
			],
			$overrides
		);
	}


	private function validContact(
		array $overrides = []
	): array {
		return array_replace(
			[
				"sales_contact_id" => 40,
				"sales_company_id" => 50,
				"first_name" => "Anna",
				"last_name" => "Svensson",
				"job_title" => "Operations Manager",
				"language" => "en",
				"is_primary" => true,
				"do_not_contact" => false
			],
			$overrides
		);
	}


	public function testGetMessagesRejectsInvalidConversationId(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->never())
			->method(
				'findByConversationId'
			);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid conversation ID."
		);

		$service->getMessagesByConversation(
			0
		);
	}


	public function testGetMessagesReturnsEmptyArrayWhenNoMessagesExist(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->once())
			->method(
				'findByConversationId'
			)
			->with(20)
			->willReturn([
				"success" => true,
				"data" => []
			]);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->getMessagesByConversation(
				20
			);

		$this->assertSame(
			[],
			$result
		);
	}


	public function testGetMessagesReturnsEmptyArrayWhenRepositoryFails(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->once())
			->method(
				'findByConversationId'
			)
			->with(20)
			->willReturn([
				"success" => false,
				"data" => []
			]);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->getMessagesByConversation(
				20
			);

		$this->assertSame(
			[],
			$result
		);
	}


	public function testGetMessagesNormalizesValues(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->once())
			->method(
				'findByConversationId'
			)
			->with(20)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"sales_message_id" => "10",
						"conversation_id" => "20",
						"direction" => "OUTBOUND",
						"sender_type" => "AI",
						"status" => "SENT",
						"ai_generated" => "t",
						"approved" => "1",
						"approved_by_user_id" => "7"
					]
				]
			]);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->getMessagesByConversation(
				20
			);

		$this->assertCount(
			1,
			$result
		);

		$this->assertSame(
			10,
			$result[0]["sales_message_id"]
		);

		$this->assertSame(
			20,
			$result[0]["conversation_id"]
		);

		$this->assertSame(
			7,
			$result[0]["approved_by_user_id"]
		);

		$this->assertSame(
			"OUTBOUND",
			$result[0]["direction"]
		);

		$this->assertSame(
			"AI",
			$result[0]["sender_type"]
		);

		$this->assertSame(
			"SENT",
			$result[0]["status"]
		);

		$this->assertTrue(
			$result[0]["ai_generated"]
		);

		$this->assertTrue(
			$result[0]["approved"]
		);
	}


	public function testGetMessagesNormalizesFalseBooleans(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method(
				'findByConversationId'
			)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"sales_message_id" => "10",
						"conversation_id" => "20",
						"direction" => "OUTBOUND",
						"sender_type" => "AI",
						"status" => "DRAFT",
						"ai_generated" => "f",
						"approved" => "f",
						"approved_by_user_id" => null
					]
				]
			]);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->getMessagesByConversation(
				20
			);

		$this->assertFalse(
			$result[0]["ai_generated"]
		);

		$this->assertFalse(
			$result[0]["approved"]
		);

		$this->assertNull(
			$result[0]["approved_by_user_id"]
		);
	}


	public function testApproveRejectsInvalidMessageId(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->never())
			->method('findById');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid sales message ID."
		);

		$service->approveMessage(
			0,
			1
		);
	}


	public function testApproveRejectsInvalidUserId(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->never())
			->method('findById');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid approving user ID."
		);

		$service->approveMessage(
			10,
			0
		);
	}


	public function testApproveRejectsMissingMessage(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->once())
			->method('findById')
			->with(10)
			->willReturn(null);

		$messageRepository
			->expects($this->never())
			->method('approve');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales message not found."
		);

		$service->approveMessage(
			10,
			1
		);
	}


	public function testApproveRejectsMessageNotPendingApproval(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage([
					"status" => "DRAFT"
				])
			);

		$messageRepository
			->expects($this->never())
			->method('approve');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Only messages pending approval can be approved."
		);

		$service->approveMessage(
			10,
			1
		);
	}


	public function testApproveRejectsInboundMessage(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage([
					"status" =>
						"PENDING_APPROVAL",
					"direction" =>
						"INBOUND"
				])
			);

		$messageRepository
			->expects($this->never())
			->method('approve');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Only outbound messages can be approved."
		);

		$service->approveMessage(
			10,
			1
		);
	}


	public function testApproveApprovesValidOutboundMessage(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->once())
			->method('findById')
			->with(10)
			->willReturn(
				$this->validMessage([
					"status" =>
						"PENDING_APPROVAL",
					"direction" =>
						"OUTBOUND",
					"approved" =>
						false
				])
			);

		$messageRepository
			->expects($this->once())
			->method('approve')
			->with(
				10,
				7
			);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$service->approveMessage(
			10,
			7
		);
	}


	public function testSendRejectsInvalidMessageId(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->never())
			->method('findById');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid sales message ID."
		);

		$service->sendMessage(
			0
		);
	}


	public function testSendRejectsMissingMessage(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->expects($this->once())
			->method('findById')
			->with(10)
			->willReturn(null);

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales message not found."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendRejectsMessageThatIsNotApproved(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage([
					"status" =>
						"PENDING_APPROVAL"
				])
			);

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Only approved messages can be sent."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendRejectsInboundMessage(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage([
					"direction" =>
						"INBOUND"
				])
			);

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Only outbound messages can be sent."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendRejectsMessageWithoutApprovalFlag(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage([
					"approved" =>
						false
				])
			);

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Message must be approved before sending."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendRejectsInvalidConversationId(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage([
					"conversation_id" => 0
				])
			);

		$conversationRepository
			->expects($this->never())
			->method('findById');

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Conversation not found for sales message."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendRejectsMissingConversation(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage()
			);

		$conversationRepository
			->expects($this->once())
			->method('findById')
			->with(20)
			->willReturn(null);

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales conversation not found."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendRejectsInvalidLeadId(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage()
			);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation([
					"sales_lead_id" => 0
				])
			);

		$leadRepository
			->expects($this->never())
			->method('findById');

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales lead not found for conversation."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendRejectsMissingLead(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage()
			);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->expects($this->once())
			->method('findById')
			->with(30)
			->willReturn(null);

		$messageRepository
			->expects($this->never())
			->method('markAsSent');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales lead not found."
		);

		$service->sendMessage(
			10
		);
	}


	public function testSendChangesNewLeadToContacted(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage()
			);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead([
					"stage" => "NEW"
				])
			);

		$sentAt = null;

		$messageRepository
			->expects($this->once())
			->method('markAsSent')
			->with(
				10,
				$this->callback(
					function (
						string $value
					) use (&$sentAt): bool {
						$sentAt = $value;

						return preg_match(
							'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
							$value
						) === 1;
					}
				)
			);

		$conversationRepository
			->expects($this->once())
			->method('markWaitingReply')
			->with(
				20,
				$this->callback(
					function (
						string $value
					) use (&$sentAt): bool {
						return
							$sentAt !== null &&
							$value === $sentAt;
					}
				)
			);

		$leadRepository
			->expects($this->once())
			->method('updateAfterContact')
			->with(
				30,
				$this->callback(
					function (
						string $value
					) use (&$sentAt): bool {
						return
							$sentAt !== null &&
							$value === $sentAt;
					}
				),
				"CONTACTED"
			);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->sendMessage(
				10
			);

		$this->assertSame(
			10,
			$result["sales_message_id"]
		);

		$this->assertSame(
			20,
			$result["conversation_id"]
		);

		$this->assertSame(
			30,
			$result["sales_lead_id"]
		);

		$this->assertSame(
			"NEW",
			$result["previous_stage"]
		);

		$this->assertSame(
			"CONTACTED",
			$result["current_stage"]
		);

		$this->assertSame(
			$sentAt,
			$result["sent_at"]
		);
	}


	public function testSendChangesResearchingLeadToContacted(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage()
			);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead([
					"stage" =>
						"RESEARCHING"
				])
			);

		$messageRepository
			->expects($this->once())
			->method('markAsSent');

		$conversationRepository
			->expects($this->once())
			->method('markWaitingReply');

		$leadRepository
			->expects($this->once())
			->method('updateAfterContact')
			->with(
				30,
				$this->isType('string'),
				"CONTACTED"
			);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->sendMessage(
				10
			);

		$this->assertSame(
			"RESEARCHING",
			$result["previous_stage"]
		);

		$this->assertSame(
			"CONTACTED",
			$result["current_stage"]
		);
	}


	public function testSendDoesNotMoveInterestedLeadBackToContacted(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage()
			);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead([
					"stage" =>
						"INTERESTED"
				])
			);

		$messageRepository
			->expects($this->once())
			->method('markAsSent');

		$conversationRepository
			->expects($this->once())
			->method('markWaitingReply');

		$leadRepository
			->expects($this->once())
			->method('updateAfterContact')
			->with(
				30,
				$this->isType('string'),
				null
			);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->sendMessage(
				10
			);

		$this->assertSame(
			"INTERESTED",
			$result["previous_stage"]
		);

		$this->assertSame(
			"INTERESTED",
			$result["current_stage"]
		);
	}


	public function testSendAcceptsPostgresTrueValue(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage([
					"approved" => "t"
				])
			);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead()
			);

		$messageRepository
			->expects($this->once())
			->method('markAsSent');

		$conversationRepository
			->expects($this->once())
			->method('markWaitingReply');

		$leadRepository
			->expects($this->once())
			->method('updateAfterContact');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->sendMessage(
				10
			);

		$this->assertSame(
			"CONTACTED",
			$result["current_stage"]
		);
	}


	public function testSendUpdatesMessageConversationAndLeadWithSameTimestamp(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$messageRepository
			->method('findById')
			->willReturn(
				$this->validMessage()
			);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead()
			);

		$messageTimestamp = null;
		$conversationTimestamp = null;
		$leadTimestamp = null;

		$messageRepository
			->expects($this->once())
			->method('markAsSent')
			->willReturnCallback(
				function (
					int $salesMessageId,
					string $timestamp
				) use (
					&$messageTimestamp
				): void {
					$this->assertSame(
						10,
						$salesMessageId
					);

					$messageTimestamp =
						$timestamp;
				}
			);

		$conversationRepository
			->expects($this->once())
			->method('markWaitingReply')
			->willReturnCallback(
				function (
					int $conversationId,
					string $timestamp
				) use (
					&$conversationTimestamp
				): void {
					$this->assertSame(
						20,
						$conversationId
					);

					$conversationTimestamp =
						$timestamp;
				}
			);

		$leadRepository
			->expects($this->once())
			->method('updateAfterContact')
			->willReturnCallback(
				function (
					int $salesLeadId,
					string $timestamp,
					?string $stage
				) use (
					&$leadTimestamp
				): void {
					$this->assertSame(
						30,
						$salesLeadId
					);

					$this->assertSame(
						"CONTACTED",
						$stage
					);

					$leadTimestamp =
						$timestamp;
				}
			);

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$result =
			$service->sendMessage(
				10
			);

		$this->assertNotNull(
			$messageTimestamp
		);

		$this->assertSame(
			$messageTimestamp,
			$conversationTimestamp
		);

		$this->assertSame(
			$messageTimestamp,
			$leadTimestamp
		);

		$this->assertSame(
			$messageTimestamp,
			$result["sent_at"]
		);
	}

	public function testReceiveRejectsInvalidConversationId(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->expects($this->never())
			->method('findById');

		$messageRepository
			->expects($this->never())
			->method('createInbound');

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid conversation ID."
		);

		$service->receiveMessage(
			0,
			"Hello from Anna."
		);
	}


	public function testReceiveRejectsEmptyMessage(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->expects($this->never())
			->method('findById');

		$messageRepository
			->expects($this->never())
			->method('createInbound');

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Message cannot be empty."
		);

		$service->receiveMessage(
			20,
			"   "
		);
	}


	public function testReceiveRejectsMissingConversation(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->expects($this->once())
			->method('findById')
			->with(20)
			->willReturn(null);

		$messageRepository
			->expects($this->never())
			->method('createInbound');

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales conversation not found."
		);

		$service->receiveMessage(
			20,
			"Hello from Anna."
		);
	}


	public function testReceiveRejectsInvalidLeadId(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation([
					"sales_lead_id" => 0
				])
			);

		$leadRepository
			->expects($this->never())
			->method('findById');

		$messageRepository
			->expects($this->never())
			->method('createInbound');

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales lead not found for conversation."
		);

		$service->receiveMessage(
			20,
			"Hello from Anna."
		);
	}


	public function testReceiveRejectsMissingLead(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->expects($this->once())
			->method('findById')
			->with(30)
			->willReturn(null);

		$messageRepository
			->expects($this->never())
			->method('createInbound');

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales lead not found."
		);

		$service->receiveMessage(
			20,
			"Hello from Anna."
		);
	}


	public function testReceiveCreatesInboundMessageAndMarksLeadReplied(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation([
					"status" => "WAITING_REPLY"
				])
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead([
					"stage" => "CONTACTED"
				])
			);

		$receivedAt = null;

		$messageRepository
			->expects($this->once())
			->method('createInbound')
			->with(
				$this->callback(
					function (array $data) use (&$receivedAt): bool {
						$this->assertSame(
							20,
							$data["conversation_id"]
						);

						$this->assertSame(
							"INBOUND",
							$data["direction"]
						);

						$this->assertSame(
							"CONTACT",
							$data["sender_type"]
						);

						$this->assertSame(
							"RECEIVED",
							$data["status"]
						);

						$this->assertSame(
							"Re: Inventory management",
							$data["subject"]
						);

						$this->assertSame(
							"Hi, please send pricing.",
							$data["message"]
						);

						$this->assertSame(
							"email-123",
							$data["provider_message_id"]
						);

						$this->assertFalse(
							$data["ai_generated"]
						);

						$this->assertFalse(
							$data["approved"]
						);

						$receivedAt =
							$data["received_at"];

						$this->assertMatchesRegularExpression(
							'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
							$receivedAt
						);

						$this->assertSame(
							$receivedAt,
							$data["updated_at"]
						);

						return true;
					}
				)
			)
			->willReturn(77);

		$conversationRepository
			->expects($this->once())
			->method('markOpenAfterReply')
			->with(
				20,
				$this->callback(
					function (string $timestamp) use (&$receivedAt): bool {
						return
							$receivedAt !== null &&
							$timestamp === $receivedAt;
					}
				)
			);

		$leadRepository
			->expects($this->once())
			->method('markReplied')
			->with(
				30,
				$this->callback(
					function (string $timestamp) use (&$receivedAt): bool {
						return
							$receivedAt !== null &&
							$timestamp === $receivedAt;
					}
				)
			);

		$leadRepository
			->expects($this->never())
			->method('updateAfterContact');

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$result = $service->receiveMessage(
			20,
			"  Hi, please send pricing.  ",
			"  Re: Inventory management  ",
			"  email-123  "
		);

		$this->assertSame(
			77,
			$result["sales_message_id"]
		);

		$this->assertSame(
			20,
			$result["conversation_id"]
		);

		$this->assertSame(
			30,
			$result["sales_lead_id"]
		);

		$this->assertSame(
			"CONTACTED",
			$result["previous_stage"]
		);

		$this->assertSame(
			"REPLIED",
			$result["current_stage"]
		);

		$this->assertSame(
			$receivedAt,
			$result["received_at"]
		);
	}


	public function testReceiveChangesEarlyLeadStagesToReplied(): void
	{
		foreach (["NEW", "RESEARCHING"] as $stage) {

			$messageRepository =
				$this->createMock(SalesMessageRepository::class);

			$conversationRepository =
				$this->createMock(SalesConversationRepository::class);

			$leadRepository =
				$this->createMock(SalesLeadRepository::class);

			$conversationRepository
				->method('findById')
				->willReturn(
					$this->validConversation()
				);

			$leadRepository
				->method('findById')
				->willReturn(
					$this->validLead([
						"stage" => $stage
					])
				);

			$messageRepository
				->expects($this->once())
				->method('createInbound')
				->willReturn(77);

			$conversationRepository
				->expects($this->once())
				->method('markOpenAfterReply');

			$leadRepository
				->expects($this->once())
				->method('markReplied')
				->with(
					30,
					$this->isType('string')
				);

			$service = new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

			$result = $service->receiveMessage(
				20,
				"Hello from Anna."
			);

			$this->assertSame(
				$stage,
				$result["previous_stage"]
			);

			$this->assertSame(
				"REPLIED",
				$result["current_stage"]
			);
		}
	}


	public function testReceivePreservesAdvancedLeadStages(): void
	{
		$stages = [
			"REPLIED",
			"INTERESTED",
			"DEMO",
			"NEGOTIATION",
			"WON",
			"LOST"
		];

		foreach ($stages as $stage) {

			$messageRepository =
				$this->createMock(SalesMessageRepository::class);

			$conversationRepository =
				$this->createMock(SalesConversationRepository::class);

			$leadRepository =
				$this->createMock(SalesLeadRepository::class);

			$conversationRepository
				->method('findById')
				->willReturn(
					$this->validConversation()
				);

			$leadRepository
				->method('findById')
				->willReturn(
					$this->validLead([
						"stage" => $stage
					])
				);

			$messageRepository
				->expects($this->once())
				->method('createInbound')
				->willReturn(77);

			$conversationRepository
				->expects($this->once())
				->method('markOpenAfterReply');

			$leadRepository
				->expects($this->never())
				->method('markReplied');

			$leadRepository
				->expects($this->once())
				->method('updateAfterContact')
				->with(
					30,
					$this->isType('string')
				);

			$service = new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

			$result = $service->receiveMessage(
				20,
				"Hello from Anna."
			);

			$this->assertSame(
				$stage,
				$result["previous_stage"]
			);

			$this->assertSame(
				$stage,
				$result["current_stage"]
			);
		}
	}


	public function testReceiveNormalizesEmptyOptionalFields(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead([
					"stage" => "CONTACTED"
				])
			);

		$messageRepository
			->expects($this->once())
			->method('createInbound')
			->with(
				$this->callback(
					function (array $data): bool {
						$this->assertNull(
							$data["subject"]
						);

						$this->assertNull(
							$data["provider_message_id"]
						);

						return true;
					}
				)
			)
			->willReturn(77);

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$result = $service->receiveMessage(
			20,
			"Hello from Anna.",
			"   ",
			"   "
		);

		$this->assertSame(
			77,
			$result["sales_message_id"]
		);
	}


	public function testReceiveStopsWhenInboundCreationFails(): void
	{
		$messageRepository =
			$this->createMock(SalesMessageRepository::class);

		$conversationRepository =
			$this->createMock(SalesConversationRepository::class);

		$leadRepository =
			$this->createMock(SalesLeadRepository::class);

		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead([
					"stage" => "CONTACTED"
				])
			);

		$messageRepository
			->expects($this->once())
			->method('createInbound')
			->willThrowException(
				new RuntimeException(
					"Database insert failed."
				)
			);

		$conversationRepository
			->expects($this->never())
			->method('markOpenAfterReply');

		$leadRepository
			->expects($this->never())
			->method('markReplied');

		$service = new SalesMessageService(
			$messageRepository,
			$conversationRepository,
			$leadRepository
		);

		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			"Database insert failed."
		);

		$service->receiveMessage(
			20,
			"Hello from Anna."
		);
	}


	public function testGenerateAiDraftRejectsInvalidConversationId(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$conversationRepository
			->expects($this->never())
			->method('findById');

		$messageRepository
			->expects($this->never())
			->method('createAiDraft');

		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid conversation ID."
		);

		$service->generateAiDraft(
			0
		);
	}


	public function testGenerateAiDraftRejectsWhenLatestMessageIsNotInbound(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$companyRepository =
			$this->createMock(
				SalesCompanyRepository::class
			);

		$contactRepository =
			$this->createMock(
				SalesContactRepository::class
			);

		$aiSalesAgentClient =
			$this->createMock(
				AiSalesAgentClient::class
			);


		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead()
			);

		$companyRepository
			->method('findById')
			->willReturn(
				$this->validCompany()
			);

		$contactRepository
			->method('findById')
			->willReturn(
				$this->validContact()
			);


		$messageRepository
			->method('findByConversationId')
			->willReturn([
				"success" => true,
				"data" => [
					[
						"sales_message_id" => 1,
						"conversation_id" => 20,
						"direction" => "INBOUND",
						"sender_type" => "CONTACT",
						"subject" => null,
						"message" => "Hello.",
						"status" => "RECEIVED"
					],
					[
						"sales_message_id" => 2,
						"conversation_id" => 20,
						"direction" => "OUTBOUND",
						"sender_type" => "AI",
						"subject" => null,
						"message" => "Draft reply.",
						"status" => "PENDING_APPROVAL"
					]
				]
			]);


		$aiSalesAgentClient
			->expects($this->never())
			->method('generateReply');

		$messageRepository
			->expects($this->never())
			->method('createAiDraft');


		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository,
				$companyRepository,
				$contactRepository,
				$aiSalesAgentClient
			);


		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"AI draft can only be generated after an inbound message."
		);


		$service->generateAiDraft(
			20
		);
	}


	public function testGenerateAiDraftRejectsContactFromDifferentCompany(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$companyRepository =
			$this->createMock(
				SalesCompanyRepository::class
			);

		$contactRepository =
			$this->createMock(
				SalesContactRepository::class
			);

		$aiSalesAgentClient =
			$this->createMock(
				AiSalesAgentClient::class
			);


		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead()
			);

		$companyRepository
			->method('findById')
			->willReturn(
				$this->validCompany()
			);

		$contactRepository
			->method('findById')
			->willReturn(
				$this->validContact([
					"sales_company_id" => 999
				])
			);


		$aiSalesAgentClient
			->expects($this->never())
			->method('generateReply');

		$messageRepository
			->expects($this->never())
			->method('createAiDraft');


		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository,
				$companyRepository,
				$contactRepository,
				$aiSalesAgentClient
			);


		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Sales contact does not belong to sales company."
		);


		$service->generateAiDraft(
			20
		);
	}


	public function testGenerateAiDraftBuildsContextAndCreatesPendingApproval(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$companyRepository =
			$this->createMock(
				SalesCompanyRepository::class
			);

		$contactRepository =
			$this->createMock(
				SalesContactRepository::class
			);

		$aiSalesAgentClient =
			$this->createMock(
				AiSalesAgentClient::class
			);


		$conversationRepository
			->expects($this->once())
			->method('findById')
			->with(20)
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->expects($this->once())
			->method('findById')
			->with(30)
			->willReturn(
				$this->validLead([
					"stage" => "REPLIED"
				])
			);

		$companyRepository
			->expects($this->once())
			->method('findById')
			->with(50)
			->willReturn(
				$this->validCompany()
			);

		$contactRepository
			->expects($this->once())
			->method('findById')
			->with(40)
			->willReturn(
				$this->validContact()
			);


		$messageRepository
			->expects($this->once())
			->method('findByConversationId')
			->with(20)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"sales_message_id" => 1,
						"conversation_id" => 20,
						"direction" => "OUTBOUND",
						"sender_type" => "AI",
						"subject" =>
							"Inventory management solution for Demo Logistics AB",
						"message" =>
							"Hi Anna, AllStockControl helps companies manage inventory and stock operations from one system.",
						"status" => "SENT"
					],
					[
						"sales_message_id" => 2,
						"conversation_id" => 20,
						"direction" => "OUTBOUND",
						"sender_type" => "AI",
						"subject" => null,
						"message" =>
							"This unsent draft must not reach the AI.",
						"status" => "PENDING_APPROVAL"
					],
					[
						"sales_message_id" => 3,
						"conversation_id" => 20,
						"direction" => "INBOUND",
						"sender_type" => "CONTACT",
						"subject" =>
							"Re: Inventory management solution for Demo Logistics AB",
						"message" =>
							"Could you send information about pricing and available plans?",
						"status" => "RECEIVED"
					]
				]
			]);


		$aiSalesAgentClient
			->expects($this->once())
			->method('generateReply')
			->with(
				$this->callback(
					function (array $context): bool {

						$this->assertSame(
							"AllStockControl",
							$context["product"]["name"]
						);

						$this->assertSame(
							[
								"AllStockControl helps companies manage inventory and stock operations from one system."
							],
							$context["product"]["verified_facts"]
						);


						$this->assertSame(
							"Demo Logistics AB",
							$context["company"]["name"]
						);

						$this->assertSame(
							"Logistics",
							$context["company"]["industry"]
						);

						$this->assertSame(
							"Sweden",
							$context["company"]["country"]
						);

						$this->assertSame(
							"Göteborg",
							$context["company"]["city"]
						);


						$this->assertSame(
							"Anna Svensson",
							$context["contact"]["name"]
						);

						$this->assertSame(
							"Operations Manager",
							$context["contact"]["job_title"]
						);


						$this->assertSame(
							"REPLIED",
							$context["lead"]["stage"]
						);

						$this->assertSame(
							"SWEDEN",
							$context["lead"]["market"]
						);


						$this->assertSame(
							"EMAIL",
							$context["conversation"]["channel"]
						);

						$this->assertSame(
							"Inventory management solution for Demo Logistics AB",
							$context["conversation"]["subject"]
						);


						$this->assertCount(
							2,
							$context["messages"]
						);

						$this->assertSame(
							"OUTBOUND",
							$context["messages"][0]["direction"]
						);

						$this->assertSame(
							"INBOUND",
							$context["messages"][1]["direction"]
						);

						$this->assertStringNotContainsString(
							"unsent draft",
							strtolower(
								json_encode(
									$context["messages"]
								)
							)
						);


						return true;
					}
				)
			)
			->willReturn([
				"subject" =>
					"Re: Inventory management solution for Demo Logistics AB",

				"message" =>
					"Hi Anna, I don't currently have verified pricing information in the provided context.",

				"model" =>
					"test-model"
			]);


		$createdAt = null;

		$messageRepository
			->expects($this->once())
			->method('createAiDraft')
			->with(
				$this->callback(
					function (array $data) use (&$createdAt): bool {

						$this->assertSame(
							20,
							$data["conversation_id"]
						);

						$this->assertSame(
							"OUTBOUND",
							$data["direction"]
						);

						$this->assertSame(
							"AI",
							$data["sender_type"]
						);

						$this->assertSame(
							"Re: Inventory management solution for Demo Logistics AB",
							$data["subject"]
						);

						$this->assertSame(
							"Hi Anna, I don't currently have verified pricing information in the provided context.",
							$data["message"]
						);

						$this->assertSame(
							"PENDING_APPROVAL",
							$data["status"]
						);

						$this->assertTrue(
							$data["ai_generated"]
						);

						$this->assertSame(
							"test-model",
							$data["ai_model"]
						);

						$this->assertFalse(
							$data["approved"]
						);


						$createdAt =
							$data["updated_at"];

						$this->assertMatchesRegularExpression(
							'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
							$createdAt
						);


						return true;
					}
				)
			)
			->willReturn(
				77
			);


		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository,
				$companyRepository,
				$contactRepository,
				$aiSalesAgentClient
			);


		$result =
			$service->generateAiDraft(
				20
			);


		$this->assertSame(
			77,
			$result["sales_message_id"]
		);

		$this->assertSame(
			20,
			$result["conversation_id"]
		);

		$this->assertSame(
			30,
			$result["sales_lead_id"]
		);

		$this->assertSame(
			"PENDING_APPROVAL",
			$result["status"]
		);

		$this->assertSame(
			"test-model",
			$result["ai_model"]
		);

		$this->assertSame(
			$createdAt,
			$result["created_at"]
		);
	}


	public function testGenerateAiDraftStopsWhenAgentFails(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$companyRepository =
			$this->createMock(
				SalesCompanyRepository::class
			);

		$contactRepository =
			$this->createMock(
				SalesContactRepository::class
			);

		$aiSalesAgentClient =
			$this->createMock(
				AiSalesAgentClient::class
			);


		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead()
			);

		$companyRepository
			->method('findById')
			->willReturn(
				$this->validCompany()
			);

		$contactRepository
			->method('findById')
			->willReturn(
				$this->validContact()
			);

		$messageRepository
			->method('findByConversationId')
			->willReturn([
				"success" => true,
				"data" => [
					[
						"sales_message_id" => 1,
						"conversation_id" => 20,
						"direction" => "INBOUND",
						"sender_type" => "CONTACT",
						"subject" => null,
						"message" => "Hello from Anna.",
						"status" => "RECEIVED"
					]
				]
			]);


		$aiSalesAgentClient
			->expects($this->once())
			->method('generateReply')
			->willThrowException(
				new RuntimeException(
					"AI Sales Agent request failed."
				)
			);

		$messageRepository
			->expects($this->never())
			->method('createAiDraft');


		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository,
				$companyRepository,
				$contactRepository,
				$aiSalesAgentClient
			);


		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			"AI Sales Agent request failed."
		);


		$service->generateAiDraft(
			20
		);
	}


	public function testGenerateFirstOutreachCreatesPendingApprovalMessage(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$companyRepository =
			$this->createMock(
				SalesCompanyRepository::class
			);

		$contactRepository =
			$this->createMock(
				SalesContactRepository::class
			);

		$aiSalesAgentClient =
			$this->createMock(
				AiSalesAgentClient::class
			);


		$conversationRepository
			->expects($this->once())
			->method('findById')
			->with(20)
			->willReturn(
				$this->validConversation([
					"subject" => null
				])
			);


		$leadRepository
			->expects($this->once())
			->method('findById')
			->with(30)
			->willReturn(
				$this->validLead([
					"stage" => "NEW"
				])
			);


		$companyRepository
			->expects($this->once())
			->method('findById')
			->with(50)
			->willReturn(
				$this->validCompany([
					"language" => "en",
					"research_summary" =>
						"Demo Logistics AB provides logistics services.",
					"research_inventory_context" =>
						"The company operates warehousing activities.",
					"qualification_score" =>
						82,
					"qualification_reason" =>
						"Strong inventory relevance."
				])
			);


		$contactRepository
			->expects($this->once())
			->method('findById')
			->with(40)
			->willReturn(
				$this->validContact([
					"language" => "en"
				])
			);


		$messageRepository
			->expects($this->once())
			->method('findByConversationId')
			->with(20)
			->willReturn([
				"success" => true,
				"data" => []
			]);


		$aiSalesAgentClient
			->expects($this->once())
			->method('generateFirstOutreach')
			->with(
				$this->callback(
					function (array $context): bool {

						$this->assertSame(
							"AllStockControl",
							$context["product"]["name"]
						);

						$this->assertSame(
							"Demo Logistics AB",
							$context["company"]["name"]
						);

						$this->assertSame(
							"Logistics",
							$context["company"]["industry"]
						);

						$this->assertSame(
							"Anna Svensson",
							$context["contact"]["name"]
						);

						$this->assertSame(
							"Operations Manager",
							$context["contact"]["job_title"]
						);

						$this->assertSame(
							"NEW",
							$context["lead"]["stage"]
						);

						$this->assertSame(
							82,
							$context["lead"]["score"]
						);

						$this->assertSame(
							"EMAIL",
							$context["conversation"]["channel"]
						);

						$this->assertNull(
							$context["conversation"]["subject"]
						);


						return true;
					}
				)
			)
			->willReturn([
				"subject" =>
					"Inventory operations at Demo Logistics AB",

				"message" =>
					"Hi Anna, AllStockControl helps companies manage inventory and stock operations from one system.",

				"model" =>
					"test-model"
			]);


		$createdAt =
			null;


		$messageRepository
			->expects($this->once())
			->method('createAiDraft')
			->with(
				$this->callback(
					function (array $data) use (&$createdAt): bool {

						$this->assertSame(
							20,
							$data["conversation_id"]
						);

						$this->assertSame(
							"OUTBOUND",
							$data["direction"]
						);

						$this->assertSame(
							"AI",
							$data["sender_type"]
						);

						$this->assertSame(
							"Inventory operations at Demo Logistics AB",
							$data["subject"]
						);

						$this->assertSame(
							"Hi Anna, AllStockControl helps companies manage inventory and stock operations from one system.",
							$data["message"]
						);

						$this->assertSame(
							"PENDING_APPROVAL",
							$data["status"]
						);

						$this->assertTrue(
							$data["ai_generated"]
						);

						$this->assertSame(
							"test-model",
							$data["ai_model"]
						);

						$this->assertFalse(
							$data["approved"]
						);


						$createdAt =
							$data["updated_at"];


						$this->assertMatchesRegularExpression(
							'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
							$createdAt
						);


						return true;
					}
				)
			)
			->willReturn(
				77
			);


		$conversationRepository
			->expects($this->once())
			->method('updateSubject')
			->with(
				20,
				"Inventory operations at Demo Logistics AB"
			);


		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository,
				$companyRepository,
				$contactRepository,
				$aiSalesAgentClient
			);


		$result =
			$service->generateFirstOutreach(
				20
			);


		$this->assertSame(
			77,
			$result["sales_message_id"]
		);

		$this->assertSame(
			20,
			$result["conversation_id"]
		);

		$this->assertSame(
			30,
			$result["sales_lead_id"]
		);

		$this->assertSame(
			"PENDING_APPROVAL",
			$result["status"]
		);

		$this->assertSame(
			"test-model",
			$result["ai_model"]
		);

		$this->assertTrue(
			$result["created"]
		);

		$this->assertSame(
			$createdAt,
			$result["created_at"]
		);
	}


	public function testGenerateFirstOutreachReusesExistingAiOutreach(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$companyRepository =
			$this->createMock(
				SalesCompanyRepository::class
			);

		$contactRepository =
			$this->createMock(
				SalesContactRepository::class
			);

		$aiSalesAgentClient =
			$this->createMock(
				AiSalesAgentClient::class
			);


		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead()
			);

		$companyRepository
			->method('findById')
			->willReturn(
				$this->validCompany()
			);

		$contactRepository
			->method('findById')
			->willReturn(
				$this->validContact()
			);


		$messageRepository
			->expects($this->once())
			->method('findByConversationId')
			->with(20)
			->willReturn([
				"success" => true,
				"data" => [
					[
						"sales_message_id" =>
							77,

						"conversation_id" =>
							20,

						"direction" =>
							"OUTBOUND",

						"sender_type" =>
							"AI",

						"status" =>
							"PENDING_APPROVAL",

						"ai_model" =>
							"test-model"
					]
				]
			]);


		$aiSalesAgentClient
			->expects($this->never())
			->method('generateFirstOutreach');

		$messageRepository
			->expects($this->never())
			->method('createAiDraft');

		$conversationRepository
			->expects($this->never())
			->method('updateSubject');


		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository,
				$companyRepository,
				$contactRepository,
				$aiSalesAgentClient
			);


		$result =
			$service->generateFirstOutreach(
				20
			);


		$this->assertSame(
			77,
			$result["sales_message_id"]
		);

		$this->assertSame(
			20,
			$result["conversation_id"]
		);

		$this->assertSame(
			30,
			$result["sales_lead_id"]
		);

		$this->assertSame(
			"PENDING_APPROVAL",
			$result["status"]
		);

		$this->assertSame(
			"test-model",
			$result["ai_model"]
		);

		$this->assertFalse(
			$result["created"]
		);
	}


	public function testGenerateFirstOutreachRejectsConversationWithExistingNonAiMessage(): void
	{
		$messageRepository =
			$this->createMock(
				SalesMessageRepository::class
			);

		$conversationRepository =
			$this->createMock(
				SalesConversationRepository::class
			);

		$leadRepository =
			$this->createMock(
				SalesLeadRepository::class
			);

		$companyRepository =
			$this->createMock(
				SalesCompanyRepository::class
			);

		$contactRepository =
			$this->createMock(
				SalesContactRepository::class
			);

		$aiSalesAgentClient =
			$this->createMock(
				AiSalesAgentClient::class
			);


		$conversationRepository
			->method('findById')
			->willReturn(
				$this->validConversation()
			);

		$leadRepository
			->method('findById')
			->willReturn(
				$this->validLead()
			);

		$companyRepository
			->method('findById')
			->willReturn(
				$this->validCompany()
			);

		$contactRepository
			->method('findById')
			->willReturn(
				$this->validContact()
			);


		$messageRepository
			->method('findByConversationId')
			->willReturn([
				"success" => true,
				"data" => [
					[
						"sales_message_id" =>
							55,

						"direction" =>
							"INBOUND",

						"sender_type" =>
							"CONTACT",

						"status" =>
							"RECEIVED"
					]
				]
			]);


		$aiSalesAgentClient
			->expects($this->never())
			->method('generateFirstOutreach');

		$messageRepository
			->expects($this->never())
			->method('createAiDraft');


		$service =
			new SalesMessageService(
				$messageRepository,
				$conversationRepository,
				$leadRepository,
				$companyRepository,
				$contactRepository,
				$aiSalesAgentClient
			);


		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Initial outreach can only be generated for a new conversation."
		);


		$service->generateFirstOutreach(
			20
		);
	}
}