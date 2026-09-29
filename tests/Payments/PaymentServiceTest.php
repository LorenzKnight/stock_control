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
				"due" => 440,
				"currency" => "SEK"
			],
			$overrides
		);
	}


	private function validStoredPayment(
		array $overrides = []
	): array {
		return array_replace(
			[
				"payment_id" => 77,
				"ord_no" => 10000001,
				"payment_no" => 20000001,
				"sales_id" => 50,
				"customer_id" => 7,
				"currency" => "SEK",
				"payment_method" => 2,
				"amount" => 100,
				"interest" => 9.09,
				"installments_month" => 4,
				"no_installments" => 2,
				"payment_date" =>
					"2026-09-28 10:30:00",
				"due" => 340,
				"status" => 1,
				"created_by" => 10,
				"created_at" =>
					"2026-09-28 10:30:00"
			],
			$overrides
		);
	}


	public function testGetPaymentsReturnsFormattedPayments(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findPayments'
			)
			->with(
				5,
				'',
				null
			)
			->willReturn([
				$this->validStoredPayment()
			]);

		$repository
			->expects($this->once())
			->method(
				'findCustomerInfoById'
			)
			->with(
				7,
				5
			)
			->willReturn([
				"customer_name" =>
					"John",

				"customer_surname" =>
					"Doe",

				"customer_document_type" =>
					1,

				"customer_document_no" =>
					"ABC123"
			]);

		$service =
			new PaymentService(
				$repository
			);

		$payments =
				$service->getPayments(
					5
				);

		$this->assertCount(
			1,
			$payments
		);

		$payment =
			$payments[0];

		$this->assertSame(
			77,
			$payment["payment_id"]
		);

		$this->assertSame(
			10000001,
			$payment["ord_no"]
		);

		$this->assertSame(
			20000001,
			$payment["payment_no"]
		);

		$this->assertSame(
			"John Doe",
			$payment["full_name"]
		);

		$this->assertSame(
			"National ID / Cedula",
			$payment["document_type"]
		);

		$this->assertSame(
			"ABC123",
			$payment["document_no"]
		);

		$this->assertSame(
			"Credit Card",
			$payment["payment_method"]
		);

		$this->assertSame(
			"100.00",
			$payment["amount"]
		);

		$this->assertSame(
			"9.09",
			$payment["interest"]
		);

		$this->assertSame(
			"90.91",
			$payment["principal_paid"]
		);

		$this->assertSame(
			4,
			$payment["installments_month"]
		);

		$this->assertSame(
			2,
			$payment["no_installments"]
		);

		$this->assertSame(
			"340.00",
			$payment["due"]
		);

		$this->assertSame(
			"2026-09-28",
			$payment["payment_date"]
		);

		$this->assertSame(
			"2026-09-28",
			$payment["created_at"]
		);
	}


	public function testGetPaymentsPassesTrimmedSearchToRepository(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findPayments'
			)
			->with(
				5,
				'10000001',
				null
			)
			->willReturn([
				$this->validStoredPayment()
			]);

		$repository
			->method(
				'findCustomerInfoById'
			)
			->willReturn([
				"customer_name" =>
					"John",

				"customer_surname" =>
					"Doe"
			]);

		$service =
			new PaymentService(
				$repository
			);

		$payments =
				$service->getPayments(
					5,
					' 10000001 '
				);

		$this->assertCount(
			1,
			$payments
		);
	}


	public function testGetPaymentsFiltersByPaymentId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findPayments'
			)
			->with(
				5,
				'',
				77
			)
			->willReturn([
				$this->validStoredPayment()
			]);

		$repository
			->method(
				'findCustomerInfoById'
			)
			->willReturn([
				"customer_name" =>
					"John",

				"customer_surname" =>
					"Doe"
			]);

		$service =
			new PaymentService(
				$repository
			);

		$payments =
				$service->getPayments(
					5,
					'',
					77
				);

		$this->assertCount(
			1,
			$payments
		);

		$this->assertSame(
			77,
			$payments[0]["payment_id"]
		);
	}


	public function testGetPaymentsRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findPayments'
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

		$service->getPayments(
			0
		);
	}


	public function testGetPaymentsRejectsInvalidPaymentId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findPayments'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			InvalidArgumentException::class
		);

		$this->expectExceptionMessage(
			"Invalid payment ID."
		);

		$service->getPayments(
			5,
			'',
			0
		);
	}


	public function testGetPaymentsThrowsWhenNoPaymentsExist(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findPayments'
			)
			->with(
				5,
				'',
				null
			)
			->willReturn([]);

		$repository
			->expects($this->never())
			->method(
				'findCustomerInfoById'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"No payments available."
		);

		$service->getPayments(
			5
		);
	}


	public function testGetOrderInfoReturnsRemainingInstallments(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findSaleInfoByOrderNumber'
			)
			->with(
				10000001,
				5
			)
			->willReturn([
				"sales_id" => 50,
				"ord_no" => 10000001,
				"customer_id" => 7,
				"currency" => "SEK",
				"interest_type" => 1,
				"interest" => 10,
				"installments_month" => 6,
				"remaining" => 1000,
				"due" => 1200
			]);

		$repository
			->expects($this->once())
			->method(
				'findCustomerInfoById'
			)
			->with(
				7,
				5
			)
			->willReturn([
				"customer_name" => "John",
				"customer_surname" => "Doe",
				"customer_document_type" => 1,
				"customer_document_no" => "ABC123",
				"customer_phone" => "0700000000",
				"customer_email" => "john@example.com"
			]);

		/*
		* Ya existe un pago.
		* El próximo será el número 2.
		*
		* 6 cuotas totales
		* - 1 ya utilizada
		* = 5 restantes.
		*/
		$repository
			->expects($this->once())
			->method(
				'getNextInstallmentNumber'
			)
			->with(50)
			->willReturn(2);

		$service =
			new PaymentService(
				$repository
			);

		$result =
			$service->getOrderInfo(
				5,
				10000001
			);

		$this->assertSame(
			10000001,
			$result["ord_no"]
		);

		$this->assertSame(
			6,
			$result["installments_month"]
		);

		$this->assertSame(
			2,
			$result["next_installment"]
		);

		$this->assertSame(
			5,
			$result["remaining_installments"]
		);

		$this->assertSame(
			1000.0,
			$result["remaining"]
		);

		$this->assertSame(
			1200.0,
			$result["due"]
		);

		$this->assertSame(
			"John Doe",
			$result["customer_name"]
		);
	}


	public function testGetOrderInfoReturnsAllInstallmentsBeforeFirstPayment(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->method(
				'findSaleInfoByOrderNumber'
			)
			->willReturn([
				"sales_id" => 50,
				"ord_no" => 10000001,
				"customer_id" => 7,
				"currency" => "SEK",
				"interest_type" => 1,
				"interest" => 10,
				"installments_month" => 6,
				"remaining" => 1000,
				"due" => 1200
			]);

		$repository
			->method(
				'findCustomerInfoById'
			)
			->willReturn([
				"customer_name" => "John",
				"customer_surname" => "Doe"
			]);

		$repository
			->expects($this->once())
			->method(
				'getNextInstallmentNumber'
			)
			->with(50)
			->willReturn(1);

		$service =
			new PaymentService(
				$repository
			);

		$result =
			$service->getOrderInfo(
				5,
				10000001
			);

		$this->assertSame(
			1,
			$result["next_installment"]
		);

		$this->assertSame(
			6,
			$result["remaining_installments"]
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
					"remaining" => 70,
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
				'updateSaleBalances'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
			Exception::class
		);

		$this->expectExceptionMessage(
			"The outstanding debt (80.00 SEK) is less than the amount being paid (100.00)."
		);

		$service->createPayment(
			10,
			5,
			$this->validPaymentData()
		);
	}


	public function testCreatesFixedInterestPaymentAndUpdatesSaleBalances(): void
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
								9.09 &&
							$data["installments_month"] ===
								4 &&
							$data["no_installments"] ===
								2 &&
							!empty(
								$data["payment_date"]
							) &&
							$data["due"] ===
								340.0 &&
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
								9.09 &&
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
				'updateSaleBalances'
			)
			->with(
				50,
				5,
				309.09,
				340.0
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
			$result["previous_remaining"]
		);

		$this->assertSame(
			440.0,
			$result["previous_due"]
		);

		$this->assertSame(
			100.0,
			$result["amount"]
		);

		$this->assertSame(
			90.91,
			$result["principal_paid"]
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
			9.09,
			$result["interest"]
		);

		$this->assertSame(
			309.09,
			$result["remaining"]
		);

		$this->assertSame(
			340.0,
			$result["due"]
		);

		$this->assertSame(
			2,
			$result["no_installments"]
		);
	}


	public function testCreatesReducingBalancePaymentAndUpdatesSaleBalances(): void
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
					"remaining" => 400,
					"due" => 500
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
								408.0;
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
				'updateSaleBalances'
			)
			->with(
				50,
				5,
				340.0,
				408.0
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
			$result["previous_remaining"]
		);

		$this->assertSame(
			500.0,
			$result["previous_due"]
		);

		$this->assertSame(
			100.0,
			$result["amount"]
		);

		$this->assertSame(
			60.0,
			$result["principal_paid"]
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
			340.0,
			$result["remaining"]
		);

		$this->assertSame(
			408.0,
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
				'updateSaleBalances'
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
				'updateSaleBalances'
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


	public function testDeletesLatestFixedPaymentAndRestoresSaleBalances(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findPaymentForDelete'
			)
			->with(
				77,
				5
			)
			->willReturn([
				"payment_id" => 77,
				"sales_id" => 50,
				"amount" => 100,
				"interest" => 9.09,
				"no_installments" => 2
			]);

		$repository
			->expects($this->once())
			->method(
				'findSaleForPaymentDelete'
			)
			->with(
				50,
				5
			)
			->willReturn([
				"sales_id" => 50,
				"remaining" => 309.09,
				"due" => 340,
				"interest_type" => 1,
				"interest" => 10,
				"installments_month" => 4
			]);

		$repository
			->expects($this->once())
			->method(
				'findLatestPaymentForSale'
			)
			->with(
				50,
				5
			)
			->willReturn([
				"payment_id" => 77,
				"no_installments" => 2
			]);

		$repository
			->expects($this->once())
			->method(
				'deleteInterestEarningByPaymentId'
			)
			->with(77);

		$repository
			->expects($this->once())
			->method(
				'deletePayment'
			)
			->with(
				77,
				5
			);

		$repository
			->expects($this->once())
			->method(
				'updateSaleBalances'
			)
			->with(
				50,
				5,
				400.0,
				440.0
			);

		$service =
			new PaymentService(
				$repository
			);

		$result =
				$service->deletePayment(
					10,
					5,
					77
				);

		$this->assertSame(
			77,
			$result["payment_id"]
		);

		$this->assertSame(
			50,
			$result["sale_id"]
		);

		$this->assertSame(
			400.0,
			$result["restored_remaining"]
		);

		$this->assertSame(
			440.0,
			$result["restored_due"]
		);
	}


	public function testDeletesLatestReducingBalancePaymentAndRestoresSaleBalances(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findPaymentForDelete'
			)
			->with(
				77,
				5
			)
			->willReturn([
				"payment_id" => 77,
				"sales_id" => 50,
				"amount" => 100,
				"interest" => 40,
				"no_installments" => 1
			]);

		$repository
			->expects($this->once())
			->method(
				'findSaleForPaymentDelete'
			)
			->with(
				50,
				5
			)
			->willReturn([
				"sales_id" => 50,
				"remaining" => 340,
				"due" => 408,
				"interest_type" => 2,
				"interest" => 10,
				"installments_month" => 4
			]);

		$repository
			->expects($this->once())
			->method(
				'findLatestPaymentForSale'
			)
			->with(
				50,
				5
			)
			->willReturn([
				"payment_id" => 77,
				"no_installments" => 1
			]);

		$repository
			->expects($this->once())
			->method(
				'deleteInterestEarningByPaymentId'
			)
			->with(77);

		$repository
			->expects($this->once())
			->method(
				'deletePayment'
			)
			->with(
				77,
				5
			);

		$repository
			->expects($this->once())
			->method(
				'updateSaleBalances'
			)
			->with(
				50,
				5,
				400.0,
				500.0
			);

		$service =
			new PaymentService(
				$repository
			);

		$result =
				$service->deletePayment(
					10,
					5,
					77
				);

		$this->assertSame(
			400.0,
			$result["restored_remaining"]
		);

		$this->assertSame(
			500.0,
			$result["restored_due"]
		);
	}


	public function testDeletePaymentRejectsNonLatestPayment(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->method(
				'findPaymentForDelete'
			)
			->willReturn([
				"payment_id" => 77,
				"sales_id" => 50,
				"amount" => 100,
				"interest" => 9.09,
				"no_installments" => 2
			]);

		$repository
			->method(
				'findSaleForPaymentDelete'
			)
			->willReturn([
				"sales_id" => 50,
				"remaining" => 200,
				"due" => 220,
				"interest_type" => 1,
				"interest" => 10,
				"installments_month" => 4
			]);

		$repository
			->expects($this->once())
			->method(
				'findLatestPaymentForSale'
			)
			->with(
				50,
				5
			)
			->willReturn([
				"payment_id" => 78,
				"no_installments" => 3
			]);

		$repository
			->expects($this->never())
			->method(
				'deleteInterestEarningByPaymentId'
			);

		$repository
			->expects($this->never())
			->method(
				'deletePayment'
			);

		$repository
			->expects($this->never())
			->method(
				'updateSaleBalances'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
				Exception::class
			);

		$this->expectExceptionMessage(
				"Only the latest payment can be deleted."
			);

		$service->deletePayment(
				10,
				5,
				77
			);
	}


	public function testDeletePaymentThrowsWhenPaymentDoesNotExist(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->once())
			->method(
				'findPaymentForDelete'
			)
			->with(
				77,
				5
			)
			->willReturn(null);

		$repository
			->expects($this->never())
			->method(
				'findSaleForPaymentDelete'
			);

		$repository
			->expects($this->never())
			->method(
				'deletePayment'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
				Exception::class
			);

		$this->expectExceptionMessage(
				"Payment record not found."
			);

		$service->deletePayment(
				10,
				5,
				77
			);
	}


	public function testDeletePaymentRejectsInvalidUserId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findPaymentForDelete'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
				InvalidArgumentException::class
			);

		$this->expectExceptionMessage(
				"User session not found."
			);

		$service->deletePayment(
				0,
				5,
				77
			);
	}


	public function testDeletePaymentRejectsInvalidCompanyId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findPaymentForDelete'
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

		$service->deletePayment(
				10,
				0,
				77
			);
	}


	public function testDeletePaymentRejectsInvalidPaymentId(): void
	{
		$repository =
			$this->createMock(
				PaymentRepository::class
			);

		$repository
			->expects($this->never())
			->method(
				'findPaymentForDelete'
			);

		$service =
			new PaymentService(
				$repository
			);

		$this->expectException(
				InvalidArgumentException::class
			);

		$this->expectExceptionMessage(
				"Missing or invalid payment ID."
			);

		$service->deletePayment(
				10,
				5,
				0
			);
	}
}