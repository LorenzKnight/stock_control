<?php

namespace App\Payments;

class PaymentService
{
	private PaymentRepository $repository;

	public function __construct(
		PaymentRepository $repository
	) {
		$this->repository =
			$repository;
	}


	public function getOrderInfo(
		int $companyId,
		int $ordNo
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"User company not found."
			);
		}

		if ($ordNo <= 0) {
			throw new \InvalidArgumentException(
				"Invalid order number."
			);
		}

		$sale =
			$this->repository
				->findSaleInfoByOrderNumber(
					$ordNo,
					$companyId
				);

		if ($sale === null) {
			throw new \Exception(
				"No matching order found."
			);
		}

		$customerId =
			(int)(
				$sale["customer_id"]
				?? 0
			);

		if ($customerId <= 0) {
			throw new \RuntimeException(
				"Invalid sale customer."
			);
		}

		$customer =
			$this->repository
				->findCustomerInfoById(
					$customerId,
					$companyId
				);

		if ($customer === null) {
			throw new \Exception(
				"Customer not found."
			);
		}

		return [
			"ord_no" =>
				(int)$sale["ord_no"],

			"currency" =>
				(string)(
					$sale["currency"]
					?? ''
				),

			"interest_type" =>
				(int)(
					$sale["interest_type"]
					?? 0
				),

			"interest" =>
				(float)(
					$sale["interest"]
					?? 0
				),

			"installments_month" =>
				(int)(
					$sale["installments_month"]
					?? 0
				),

			"remaining" =>
				(float)(
					$sale["remaining"]
						?? 0
				),

			"due" =>
				(float)(
					$sale["due"]
					?? 0
				),

			"customer_id" =>
				$customerId,

			"customer_name" =>
				trim(
					(string)(
						$customer[
							"customer_name"
						] ?? ''
					) .
					' ' .
					(string)(
						$customer[
							"customer_surname"
						] ?? ''
					)
				),

			"document_type" =>
				$customer[
					"customer_document_type"
				] ?? '',

			"document_no" =>
				$customer[
					"customer_document_no"
				] ?? '',

			"phone" =>
				$customer[
					"customer_phone"
				] ?? '',

			"email" =>
				$customer[
					"customer_email"
				] ?? ''
		];
	}


	public function createPayment(
		int $userId,
		int $companyId,
		array $data
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"Unauthorized access. User not found or invalid token."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"User company not found."
			);
		}

		/*
		 * Campos que el formulario actual
		 * requiere obligatoriamente.
		 */
		$required = [
			"ord_no",
			"amount",
			"customer_email"
		];

		foreach ($required as $field) {
			if (
				!array_key_exists(
					$field,
					$data
				) ||
				$data[$field] === null ||
				(
					is_string(
						$data[$field]
					) &&
					trim(
						$data[$field]
					) === ''
				)
			) {
				throw new \InvalidArgumentException(
					"Missing required field: {$field}"
				);
			}
		}

		$ordNo =
			(int)$data["ord_no"];

		if ($ordNo <= 0) {
			throw new \InvalidArgumentException(
				"Invalid order number."
			);
		}

		$amount =
			round(
				(float)$data["amount"],
				2
			);

		if ($amount <= 0) {
			throw new \InvalidArgumentException(
				"Payment amount must be greater than zero."
			);
		}

		/*
		 * La venta se busca por ord_no +
		 * company_id y queda bloqueada FOR UPDATE.
		 */
		$sale =
			$this->repository
				->findSaleByOrderNumber(
					$ordNo,
					$companyId
				);

		if ($sale === null) {
			throw new \Exception(
				"Order not found."
			);
		}

		$saleId =
			(int)(
				$sale["sales_id"]
				?? 0
			);

		$customerId =
			(int)(
				$sale["customer_id"]
				?? 0
			);

		if (
			$saleId <= 0 ||
			$customerId <= 0
		) {
			throw new \RuntimeException(
				"Invalid sale data."
			);
		}

		$newPaymentNo =
			$this->repository
				->getNextPaymentNumber(
					$companyId
				);

        $installmentsMonth =
			(int)(
				$sale[
					"installments_month"
				] ?? 0
			);

		if ($installmentsMonth <= 0) {
			throw new \RuntimeException(
				"Invalid sale installment plan."
			);
		}

		$noInstallments =
			$this->repository
				->getNextInstallmentNumber(
					$saleId
				);

		$saleRemaining =
			round(
				(float)(
					$sale["remaining"]
						?? 0
				),
				2
			);

		$saleDue =
			round(
				(float)(
					$sale["due"]
						?? 0
				),
				2
			);

		if (
			$saleRemaining <= 0 ||
			$saleDue <= 0
		) {
			throw new \Exception(
				"This sale has no outstanding debt."
			);
		}

		if ($amount > $saleDue) {
			throw new \Exception(
				"The outstanding debt (" .
					number_format(
						$saleDue,
						2
					) .
					" " .
					(string)(
						$sale["currency"]
							?? ''
					) .
					") is less than the amount being paid (" .
					number_format(
						$amount,
						2
					) .
					")."
			);
		}

		$interestType =
			(int)(
				$sale["interest_type"]
				?? 0
			);

		if (
			$interestType !== 1 &&
			$interestType !== 2
		) {
			throw new \RuntimeException(
				"Invalid sale interest type."
			);
		}

		$interestRate =
			(float)(
				$sale["interest"]
				?? 0
			);

		if ($interestRate < 0) {
			throw new \RuntimeException(
				"Invalid sale interest rate."
			);
		}

		$breakdown =
			$this->calculatePaymentBreakdown(
				$amount,
				$saleRemaining,
				$saleDue,
				$interestType,
				$interestRate,
				$installmentsMonth,
				$noInstallments
			);

		$principalPaid =
			$breakdown["principal"];

		$interest =
			$breakdown["interest"];

		$remaining =
			$breakdown["remaining"];

		$due =
			$breakdown["due"];

		$status =
			(int)(
				$data["payment_status"]
				?? 0
			);

		$currency =
			$sale["currency"]
				?? null;

		$now =
			date(
				"Y-m-d H:i:s"
			);

		/*
		 * Primero creamos el pago.
		 */
		$paymentId =
			$this->repository
				->createPayment([
					"ord_no" =>
						$ordNo,

					"payment_no" =>
						$newPaymentNo,

					"sales_id" =>
						$saleId,

					"customer_id" =>
						$customerId,

					"person_who_paid" =>
						$data[
							"person_who_paid"
						] ?? null,

					"payer_document_type" =>
						$data[
							"payer_document_type"
						] ?? null,

					"payer_document_no" =>
						$data[
							"payer_document_no"
						] ?? null,

					"payer_phone" =>
						$data[
							"payer_phone"
						] ?? null,

					"customer_email" =>
						$data[
							"customer_email"
						],

					"currency" =>
						$currency,

					"payment_method" =>
						$data[
							"payment_method"
						] ?? null,

					"amount" =>
						$amount,

					"interest" =>
						$interest,

					"installments_month" =>
						$installmentsMonth,

					"no_installments" =>
						$noInstallments,

					"payment_date" =>
						$now,

					"due" =>
						$due,

					"status" =>
						$status,

					"company_id" =>
						$companyId,

					"created_by" =>
						$userId
				]);

		/*
		 * Conservamos también el comportamiento
		 * actual: interest_earnings se crea aunque
		 * interest sea 0.
		 */
		$this->repository
			->createInterestEarning([
				"sales_id" =>
					$saleId,

				"payment_id" =>
					$paymentId,

				"customer_id" =>
					$customerId,

				"payment_no" =>
					$newPaymentNo,

				"ord_no" =>
					$ordNo,

				"interest" =>
					$interest,

				"installments_month" =>
					$installmentsMonth,

				"no_installments" =>
					$noInstallments,

				"payment_date" =>
					$now,

				"initial_debt" =>
					$saleRemaining,

				"created_by" =>
					$userId,

				"created_at" =>
					$now
			]);

		/*
		 * Finalmente actualizamos el saldo
		 * pendiente de la venta.
		 */
		$this->repository
			->updateSaleBalances(
				$saleId,
				$companyId,
				$remaining,
				$due
			);

		return [
			"payment_id" => $paymentId,
			"payment_no" => $newPaymentNo,
			"sale_id" => $saleId,
			"ord_no" => $ordNo,
			"previous_remaining" => $saleRemaining,
			"previous_due" => $saleDue,
			"amount" => $amount,
			"principal_paid" => $principalPaid,
			"interest_type" => $interestType,
			"interest_rate" => $interestRate,
			"interest" => $interest,
			"remaining" => $remaining,
			"due" => $due,
			"no_installments" => $noInstallments
		];
	}

	private function calculatePaymentBreakdown(
		float $totalAmount,
		float $openingPrincipal,
		float $openingDue,
		int $interestType,
		float $interestRate,
		int $installmentsMonth,
		int $installmentNumber
	): array {
		if ($interestRate < 0) {
			throw new \InvalidArgumentException(
				"Invalid interest rate."
			);
		}

		$rate =
			$interestRate / 100;

		/*
		* FIXED INTEREST
		*
		* amount = pago total recibido.
		*
		* Ese pago contiene:
		* principal + interest.
		*/
		if ($interestType === 1) {
			if ($rate > 0) {
				$principal =
					round(
						$totalAmount /
							(1 + $rate),
						2
					);

				$interest =
					round(
						$totalAmount -
							$principal,
						2
					);
			} else {
				$principal =
					round(
						$totalAmount,
						2
					);

				$interest =
					0.0;
			}

			if ($principal > $openingPrincipal) {
				throw new \Exception(
					"Payment exceeds the outstanding balance."
				);
			}

			$remaining =
				round(
					$openingPrincipal -
						$principal,
					2
				);

			/*
			* En Fixed la deuda total baja
			* exactamente por el pago recibido.
			*/
			$due =
				round(
					$openingDue -
						$totalAmount,
					2
				);
		}

		/*
		* REDUCING BALANCE
		*
		* El interés actual se calcula sobre
		* el capital pendiente antes del pago.
		*
		* El resto del pago amortiza capital.
		*/
		elseif ($interestType === 2) {
			$interest =
				round(
					$openingPrincipal *
						$rate,
					2
				);

			$principal =
				round(
					$totalAmount -
						$interest,
					2
				);

			if ($principal <= 0) {
				throw new \Exception(
					"Payment amount must be greater than the interest due."
				);
			}

			if ($principal > $openingPrincipal) {
				throw new \Exception(
					"Payment exceeds the outstanding balance."
				);
			}

			$remaining =
				round(
					$openingPrincipal -
						$principal,
					2
				);

			/*
			* Después de este pago recalculamos
			* solamente los intereses futuros.
			*/
			$remainingInstallments =
				max(
					$installmentsMonth -
						$installmentNumber,
					0
				);

			$futureInterest =
				$this->calculateReducingInterest(
					$remaining,
					$interestRate,
					$remainingInstallments
				);

			$due =
				round(
					$remaining +
						$futureInterest,
					2
				);
		} else {
			throw new \InvalidArgumentException(
				"Invalid interest type."
			);
		}

		if (abs($remaining) < 0.01) {
			$remaining = 0.0;
		}

		if (abs($due) < 0.01) {
			$due = 0.0;
		}

		return [
			"principal" =>
				$principal,

			"interest" =>
				$interest,

			"remaining" =>
				$remaining,

			"due" =>
				$due
		];
	}


	private function calculateReducingInterest(
		float $principal,
		float $interestRate,
		int $installments
	): float {
		if (
			$principal <= 0 ||
			$interestRate <= 0 ||
			$installments <= 0
		) {
			return 0.0;
		}

		$rate =
			$interestRate / 100;

		$principalPerInstallment =
			$principal /
			$installments;

		$balance =
			$principal;

		$totalInterest =
			0.0;

		for (
			$installment = 1;
			$installment <= $installments;
			$installment++
		) {
			$installmentInterest =
				round(
					$balance *
						$rate,
					2
				);

			$totalInterest +=
				$installmentInterest;

			$balance -=
				$principalPerInstallment;

			if ($balance < 0) {
				$balance = 0.0;
			}
		}

		return round(
			$totalInterest,
			2
		);
	}
}