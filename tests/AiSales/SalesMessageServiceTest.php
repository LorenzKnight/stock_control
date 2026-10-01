<?php

use App\AiSales\SalesMessageRepository;
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesLeadRepository;
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
				"stage" => "NEW",
				"last_contact_at" => null,
				"next_action" => null,
				"next_action_at" => null
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
}