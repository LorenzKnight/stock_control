<?php

use App\Payments\PaymentRepository;
use App\Payments\PaymentService;
use PHPUnit\Framework\TestCase;

final class PaymentServiceTest extends TestCase
{
	private function validPaymentData(
		array $overrides = []
	): array {
		return array_replace(
			[
				"ord_no" => 10000001,
				"person_who_paid" =>
					"John Doe",
				"payer_document_type" =>
					1,
				"payer_document_no" =>
					"ABC123",
				"payer_phone" =>
					"0700000000",
				"customer_email" =>
					"john@example.com",
				"currency" =>
					"SEK",
				"payment_method" =>
					2,
				"amount" =>
					100,
				"payment_status" =>
					1
			],
			$overrides
		);
	}


	private function validSaleData(
		array $overrides = []
	): array {
		return array_replace(
			[
				"sales_id" => 50,
				"customer_id" => 7,
				"price_sum" => 1000,
				"remaining" => 400,
				"interest_type" => 1,
				"interest" => 10,
				"installments_month" => 4,
				"due" => 400,
				"currency" => "SEK"
			],
			$overrides
		);
	}


	public function testRejectsCreateWithInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findSaleByOrderNumber'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Unauthorized access. User not found or invalid token."
		);

		$service->createPayment(
			0,
			5,
			$this->validPaymentData()
		);
	}


	public function testRejectsCreateWithInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findSaleByOrderNumber'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"User company not found."
		);

		$service->createPayment(
			10,
			0,
			$this->validPaymentData()
		);
	}


	public function testRejectsCreateWithMissingRequiredField(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$data =
			$this->validPaymentData();

		unset(
			$data["customer_email"]
		);

		$repository
			->expects($this->never())
			->method(
				'findSaleByOrderNumber'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Missing required field: customer_email"
		);

		$service->createPayment(
			10,
			5,
			$data
		);
	}


	public function testRejectsCreateWithInvalidOrderNumber(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findSaleByOrderNumber'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid order number."
		);

		$service->createPayment(
			10,
			5,
			$this->validPaymentData([
				"ord_no" => 0
			])
		);
	}


	public function testRejectsCreateWithInvalidAmount(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findSaleByOrderNumber'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Payment amount must be greater than zero."
		);

		$service->createPayment(
			10,
			5,
			$this->validPaymentData([
				"amount" => 0
			])
		);
	}


	public function testRejectsCreateWhenOrderDoesNotExist(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findSaleByOrderNumber'
			)
			->with(
				10000001,
				5
			)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method(
				'getNextPaymentNumber'
			);

		$repository
			->expects($this->never())
			->method(
				'createPayment'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"Order not found."
		);

		$service->createPayment(
			10,
			5,
			$this->validPaymentData()
		);
	}


	public function testRejectsPaymentGreaterThanDebt(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findSaleByOrderNumber'
			)
			->with(
				10000001,
				5
			)
			->willReturn(
				$this->validSaleData([
					"due" => 80
				])
			);

		$repository
			->method(
				'getNextPaymentNumber'
			)
			->willReturn(
				20000001
			);

		$repository
			->method(
				'getNextInstallmentNumber'
			)
			->willReturn(2);

		$repository
			->expects($this->never())
			->method(
				'createPayment'
			);

		$repository
			->expects($this->never())
			->method(
				'createInterestEarning'
			);

		$repository
			->expects($this->never())
			->method(
				'updateSaleDue'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"The debt (80.00 SEK) is less than the amount being paid (100.00)."
		);

		$service->createPayment(
			10,
			5,
			$this->validPaymentData()
		);
	}


	public function testCreatesFixedInterestPaymentAndUpdatesSaleDue(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findSaleByOrderNumber'
			)
			->with(
				10000001,
				5
			)
			->willReturn(
				$this->validSaleData()
			);

		$repository
			->expects($this->once())
			->method(
				'getNextPaymentNumber'
			)
			->with(5)
			->willReturn(
				20000001
			);

		$repository
			->expects($this->once())
			->method(
				'getNextInstallmentNumber'
			)
			->with(50)
			->willReturn(2);

		$repository
			->expects($this->once())
			->method(
				'createPayment'
			)
			->with(
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["ord_no"] ===
								10000001 &&
							$data["payment_no"] ===
								20000001 &&
							$data["sales_id"] ===
								50 &&
							$data["customer_id"] ===
								7 &&
							$data["person_who_paid"] ===
								"John Doe" &&
							$data["payer_document_type"] ===
								1 &&
							$data["payer_document_no"] ===
								"ABC123" &&
							$data["payer_phone"] ===
								"0700000000" &&
							$data["customer_email"] ===
								"john@example.com" &&
							$data["currency"] ===
								"SEK" &&
							$data["payment_method"] ===
								2 &&
							$data["amount"] ===
								100.0 &&
							$data["interest"] ===
								10.0 &&
							$data["installments_month"] ===
								4 &&
							$data["no_installments"] ===
								2 &&
							!empty(
								$data["payment_date"]
							) &&
							$data["due"] ===
								300.0 &&
							$data["status"] ===
								1 &&
							$data["company_id"] ===
								5 &&
							$data["created_by"] ===
								10;
					}
				)
			)
			->willReturn(77);

		$repository
			->expects($this->once())
			->method(
				'createInterestEarning'
			)
			->with(
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["sales_id"] ===
								50 &&
							$data["payment_id"] ===
								77 &&
							$data["customer_id"] ===
								7 &&
							$data["payment_no"] ===
								20000001 &&
							$data["ord_no"] ===
								10000001 &&
							$data["interest"] ===
								10.0 &&
							$data["installments_month"] ===
								4 &&
							$data["no_installments"] ===
								2 &&
							!empty(
								$data["payment_date"]
							) &&
							$data["initial_debt"] ===
								400.0 &&
							$data["created_by"] ===
								10 &&
							!empty(
								$data["created_at"]
							);
					}
				)
			);

		$repository
			->expects($this->once())
			->method(
				'updateSaleDue'
			)
			->with(
				50,
				5,
				300.0
			);

		$service =
			new PaymentService(
				$repository
			);

		$result =
			$service->createPayment(
				10,
				5,
				$this->validPaymentData()
			);

		$this->assertSame(
			77,
			$result["payment_id"]
		);

		$this->assertSame(
			20000001,
			$result["payment_no"]
		);

		$this->assertSame(
			50,
			$result["sale_id"]
		);

		$this->assertSame(
			10000001,
			$result["ord_no"]
		);

		$this->assertSame(
			400.0,
			$result["previous_due"]
		);

		$this->assertSame(
			100.0,
			$result["amount"]
		);

		$this->assertSame(
			1,
			$result["interest_type"]
		);

		$this->assertSame(
			10.0,
			$result["interest_rate"]
		);

		$this->assertSame(
			10.0,
			$result["interest"]
		);

		$this->assertSame(
			300.0,
			$result["due"]
		);

		$this->assertSame(
			2,
			$result["no_installments"]
		);
	}


	public function testCreatesReducingBalancePaymentAndUpdatesSaleDue(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findSaleByOrderNumber'
			)
			->with(
				10000001,
				5
			)
			->willReturn(
				$this->validSaleData([
					"interest_type" => 2,
					"interest" => 10,
					"due" => 400
				])
			);

		$repository
			->expects($this->once())
			->method(
				'getNextPaymentNumber'
			)
			->with(5)
			->willReturn(
				20000001
			);

		$repository
			->expects($this->once())
			->method(
				'getNextInstallmentNumber'
			)
			->with(50)
			->willReturn(1);

		$repository
			->expects($this->once())
			->method(
				'createPayment'
			)
			->with(
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["sales_id"] ===
								50 &&

							$data["amount"] ===
								100.0 &&

							$data["interest"] ===
								40.0 &&

							$data["installments_month"] ===
								4 &&

							$data["no_installments"] ===
								1 &&

							$data["due"] ===
								300.0;
					}
				)
			)
			->willReturn(77);

		$repository
			->expects($this->once())
			->method(
				'createInterestEarning'
			)
			->with(
				$this->callback(
					function (
						array $data
					): bool {
						return
							$data["sales_id"] ===
								50 &&

							$data["payment_id"] ===
								77 &&

							$data["interest"] ===
								40.0 &&

							$data["no_installments"] ===
								1 &&

							$data["initial_debt"] ===
								400.0;
					}
				)
			);

		$repository
			->expects($this->once())
			->method(
				'updateSaleDue'
			)
			->with(
				50,
				5,
				300.0
			);

		$service =
			new PaymentService(
				$repository
			);

		$result =
				$service->createPayment(
					10,
					5,
					$this->validPaymentData()
				);

		$this->assertSame(
			77,
			$result["payment_id"]
		);

		$this->assertSame(
			400.0,
			$result["previous_due"]
		);

		$this->assertSame(
			100.0,
			$result["amount"]
		);

		$this->assertSame(
			2,
			$result["interest_type"]
		);

		$this->assertSame(
			10.0,
			$result["interest_rate"]
		);

		$this->assertSame(
			40.0,
			$result["interest"]
		);

		$this->assertSame(
			300.0,
			$result["due"]
		);

		$this->assertSame(
			1,
			$result["no_installments"]
		);
	}


	public function testKeepsActualFinalInstallmentNumber(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->method(
				'findSaleByOrderNumber'
			)
			->willReturn(
				$this->validSaleData()
			);

		$repository
			->method(
				'getNextPaymentNumber'
			)
			->willReturn(
				20000001
			);

		$repository
			->expects($this->once())
			->method(
				'getNextInstallmentNumber'
			)
			->with(50)
			->willReturn(4);

		$repository
			->expects($this->once())
			->method(
				'createPayment'
			)
			->with(
				$this->callback(
					static function (
						array $data
					): bool {
						return
							$data[
								"no_installments"
							] === 4;
					}
				)
			)
			->willReturn(77);

		$repository
			->expects($this->once())
			->method(
				'createInterestEarning'
			)
			->with(
				$this->callback(
					static function (
						array $data
					): bool {
						return
							$data[
								"no_installments"
							] === 4;
					}
				)
			);

		$repository
			->expects($this->once())
			->method(
				'updateSaleDue'
			);

		$service =
			new PaymentService(
				$repository
			);

		$result =
				$service->createPayment(
					10,
					5,
					$this->validPaymentData()
				);

		$this->assertSame(
			4,
			$result["no_installments"]
		);
	}


	public function testStopsWhenInterestRecordCannotBeCreated(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->method(
				'findSaleByOrderNumber'
			)
			->willReturn(
				$this->validSaleData()
			);

		$repository
			->method(
				'getNextPaymentNumber'
			)
			->willReturn(
				20000001
			);

		$repository
			->method(
				'getNextInstallmentNumber'
			)
			->willReturn(2);

		$repository
			->expects($this->once())
			->method(
				'createPayment'
			)
			->willReturn(77);

		$repository
			->expects($this->once())
			->method(
				'createInterestEarning'
			)
			->willThrowException(
				new RuntimeException(
					"Failed to insert interest record."
				)
			);

		/*
		 * Si interest_earnings falla,
		 * sales.due todavía no debe actualizarse.
		 *
		 * El endpoint hará ROLLBACK y también
		 * desaparecerá el payment creado.
		 */
		$repository
			->expects($this->never())
			->method(
				'updateSaleDue'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			RuntimeException::class
		);

		$this->expectExceptionMessage(
			"Failed to insert interest record."
		);

		$service->createPayment(
			10,
			5,
			$this->validPaymentData()
		);
	}
}