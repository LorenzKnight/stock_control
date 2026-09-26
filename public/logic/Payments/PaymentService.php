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

		$saleDue =
			round(
				(float)(
					$sale["due"]
					?? 0
				),
				2
			);

		if ($saleDue <= 0) {
			throw new \Exception(
				"This sale has no outstanding debt."
			);
		}

		if ($saleDue < $amount) {
			throw new \Exception(
				"The debt (" .
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

		$due =
			round(
				$saleDue -
					$amount,
				2
			);

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

		$interest =
			$this->calculatePaymentInterest(
				$amount,
				$saleDue,
				$interestType,
				$interestRate
			);

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
					$saleDue,

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
			->updateSaleDue(
				$saleId,
				$companyId,
				$due
			);

		return [
			"payment_id" => $paymentId,
			"payment_no" => $newPaymentNo,
			"sale_id" => $saleId,
			"ord_no" => $ordNo,
			"previous_due" => $saleDue,
			"amount" => $amount,
			"interest_type" => $interestType,
			"interest_rate" => $interestRate,
			"interest" => $interest,
			"due" => $due,
			"no_installments" => $noInstallments
		];
	}

	private function calculatePaymentInterest(
		float $amount,
		float $openingBalance,
		int $interestType,
		float $interestRate
	): float {
		if ($interestRate <= 0) {
			return 0.0;
		}

		$rate =
			$interestRate / 100;

		/*
		* Fixed interest.
		*
		* El interés fijo total pertenece al
		* principal financiado. Cada pago recibe
		* proporcionalmente su parte del interés.
		*/
		if ($interestType === 1) {
			return round(
				$amount *
					$rate,
				2
			);
		}

		/*
		* Reducing Balance.
		*
		* La tasa mensual se aplica al principal
		* pendiente ANTES de registrar el pago.
		*/
		if ($interestType === 2) {
			return round(
				$openingBalance *
					$rate,
				2
			);
		}

		return 0.0;
	}
}