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


	public function getPayments(
		int $companyId,
		string $search = '',
		?int $paymentId = null
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"User company not found."
			);
		}

		$search = trim($search);

		if (
			$paymentId !== null &&
			$paymentId <= 0
		) {
			throw new \InvalidArgumentException(
				"Invalid payment ID."
			);
		}

		$payments =
			$this->repository
				->findPayments(
					$companyId,
					$search,
					$paymentId
				);

		if (empty($payments)) {
			throw new \Exception(
				"No payments available."
			);
		}

		$paymentsData = [];

		foreach ($payments as $payment) {
			$customerId =
				(int)(
					$payment["customer_id"]
						?? 0
				);

			$customer = [];

			if ($customerId > 0) {
				$customer =
					$this->repository
						->findCustomerInfoById(
							$customerId,
							$companyId
						)
					?? [];
			}

			$customerName =
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
				);

			$documentTypeId =
				$customer[
					"customer_document_type"
				] ?? null;

			$paymentMethodId =
				$payment[
					"payment_method"
				] ?? null;

			$amount =
				round(
					(float)(
						$payment["amount"]
							?? 0
					),
					2
				);

			$interest =
				round(
					(float)(
						$payment["interest"]
							?? 0
					),
					2
				);

			$paymentsData[] = [
				"payment_id" =>
					(int)(
						$payment["payment_id"]
							?? 0
					),

				"ord_no" =>
					$payment["ord_no"]
						?? '',

				"payment_no" =>
					$payment["payment_no"]
						?? '',

				"sales_id" =>
					$payment["sales_id"]
						?? null,

				"customer_id" =>
					$customerId,

				"full_name" =>
					$customerName,

				"document_type" =>
					\GlobalArrays::$documentTypes[
						$documentTypeId
					] ?? "Unknown",

				"document_no" =>
					$customer[
						"customer_document_no"
					] ?? '',

				"currency" =>
					$payment["currency"]
						?? '',

				"payment_method" =>
					\GlobalArrays::$paymentMethods[
						$paymentMethodId
					] ?? "Unknown",

				"amount" =>
					number_format(
						$amount,
						2,
						'.',
						''
					),

				"interest" =>
					number_format(
						$interest,
						2,
						'.',
						''
					),

				"principal_paid" =>
					number_format(
						$amount - $interest,
						2,
						'.',
						''
					),

				"installments_month" =>
					(int)(
						$payment[
							"installments_month"
						] ?? 0
					),

				"no_installments" =>
					(int)(
						$payment[
							"no_installments"
						] ?? 0
					),

				"payment_date" =>
					\format_date(
						$payment[
							"payment_date"
						] ?? null
					),

				"due" =>
					number_format(
						(float)(
							$payment["due"]
								?? 0
						),
						2,
						'.',
						''
					),

				"status" =>
					$payment["status"]
						?? null,

				"created_by" =>
					$payment["created_by"]
						?? null,

				"created_at" =>
					\format_date(
						$payment[
							"created_at"
						] ?? null
					)
			];
		}

		return $paymentsData;
	}


	public function getOrderSuggestions(
		int $companyId,
		string $search
	): array {
		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"User company not found."
			);
		}

		$search =
			trim(
				$search
			);

		if ($search === '') {
			throw new \InvalidArgumentException(
				"No search term provided"
			);
		}

		$orders =
			$this->repository
				->findOrderSuggestions(
					$companyId,
					$search
				);

		if (empty($orders)) {
			throw new \Exception(
				"No matching orders"
			);
		}

		$orderSuggestions = [];

		foreach ($orders as $order) {
			$ordNo =
				(int)(
					$order["ord_no"]
						?? 0
				);

			$customerId =
				(int)(
					$order["customer_id"]
						?? 0
				);

			if (
				$ordNo <= 0 ||
				$customerId <= 0
			) {
				continue;
			}

			$customer =
				$this->repository
					->findCustomerInfoById(
						$customerId,
						$companyId
					);

			$fullName = '';

			if ($customer !== null) {
				$fullName =
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
					);
			}

			$orderSuggestions[] = [
				"ord_no" =>
					$ordNo,

				"customer_id" =>
					$customerId,

				"full_name" =>
					$fullName
			];
		}

		if (empty($orderSuggestions)) {
			throw new \Exception(
				"No matching orders"
			);
		}

		return $orderSuggestions;
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

		$saleId =
			(int)(
				$sale["sales_id"]
					?? 0
			);

		if ($saleId <= 0) {
			throw new \RuntimeException(
				"Invalid sale ID."
			);
		}

		$nextInstallment =
			$this->repository
				->getNextInstallmentNumber(
					$saleId
				);

		$installmentsMonth =
			(int)(
				$sale["installments_month"]
					?? 0
			);

		$remainingInstallments =
			max(
				$installmentsMonth -
					($nextInstallment - 1),
				1
			);

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

			"next_installment" =>
				$nextInstallment,

			"remaining_installments" =>
				$remainingInstallments,

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


	public function deletePayment(
		int $userId,
		int $companyId,
		int $paymentId
	): array {
		if ($userId <= 0) {
			throw new \InvalidArgumentException(
				"User session not found."
			);
		}

		if ($companyId <= 0) {
			throw new \InvalidArgumentException(
				"User company not found."
			);
		}

		if ($paymentId <= 0) {
			throw new \InvalidArgumentException(
				"Missing or invalid payment ID."
			);
		}

		/*
		* Buscamos el pago dentro de la empresa.
		* El endpoint debe haber iniciado
		* una transacción antes de llegar aquí.
		*/
		$payment =
			$this->repository
				->findPaymentForDelete(
					$paymentId,
					$companyId
				);

		if ($payment === null) {
			throw new \Exception(
				"Payment record not found."
			);
		}

		$saleId =
			(int)(
				$payment["sales_id"]
					?? 0
			);

		if ($saleId <= 0) {
			throw new \RuntimeException(
				"Invalid payment sale."
			);
		}

		/*
		* Bloqueamos y recuperamos la venta
		* a la que pertenece el pago.
		*/
		$sale =
			$this->repository
				->findSaleForPaymentDelete(
					$saleId,
					$companyId
				);

		if ($sale === null) {
			throw new \Exception(
				"Sale record not found."
			);
		}

		/*
		* Solo permitimos eliminar el último
		* pago registrado para esta venta.
		*
		* Eliminar un pago intermedio dejaría
		* inconsistentes los cálculos posteriores.
		*/
		$latestPayment =
			$this->repository
				->findLatestPaymentForSale(
					$saleId,
					$companyId
				);

		if (
			$latestPayment === null ||
			(int)(
				$latestPayment["payment_id"]
					?? 0
			) !== $paymentId
		) {
			throw new \Exception(
				"Only the latest payment can be deleted."
			);
		}

		$amount =
			round(
				(float)(
					$payment["amount"]
						?? 0
				),
				2
			);

		$interest =
			round(
				(float)(
					$payment["interest"]
						?? 0
				),
				2
			);

		if ($amount <= 0) {
			throw new \RuntimeException(
				"Invalid payment amount."
			);
		}

		$principalPaid =
			round(
				$amount - $interest,
				2
			);

		if ($principalPaid <= 0) {
			throw new \RuntimeException(
				"Invalid payment principal."
			);
		}

		$currentRemaining =
			round(
				(float)(
					$sale["remaining"]
						?? 0
				),
				2
			);

		$currentDue =
			round(
				(float)(
					$sale["due"]
						?? 0
				),
				2
			);

		if (
			$currentRemaining < 0 ||
			$currentDue < 0
		) {
			throw new \RuntimeException(
				"Invalid sale balances."
			);
		}

		/*
		* Restauramos el capital que había
		* antes de registrar este pago.
		*/
		$previousRemaining =
			round(
				$currentRemaining +
					$principalPaid,
				2
			);

		$interestType =
			(int)(
				$sale["interest_type"]
					?? 0
			);

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

		/*
		* FIXED
		*
		* Al crear el pago:
		*
		* due = openingDue - amount
		*
		* Por lo tanto al eliminarlo:
		*
		* openingDue = currentDue + amount
		*/
		if ($interestType === 1) {
			$previousDue =
				round(
					$currentDue +
						$amount,
					2
				);
		}

		/*
		* REDUCING BALANCE
		*
		* No basta con sumar el pago al due,
		* porque los intereses futuros fueron
		* recalculados después de cada pago.
		*
		* Restauramos el capital anterior y
		* volvemos a calcular los intereses que
		* existían antes de la cuota eliminada.
		*/
		elseif ($interestType === 2) {
			$installmentsMonth =
				(int)(
					$sale[
						"installments_month"
					] ?? 0
				);

			$installmentNumber =
				(int)(
					$payment[
						"no_installments"
					] ?? 0
				);

			if ($installmentsMonth <= 0) {
				throw new \RuntimeException(
					"Invalid sale installment plan."
				);
			}

			if ($installmentNumber <= 0) {
				throw new \RuntimeException(
					"Invalid payment installment number."
				);
			}

			$remainingInstallmentsBefore =
				max(
					$installmentsMonth -
						($installmentNumber - 1),
					0
				);

			$previousInterest =
				$this->calculateReducingInterest(
					$previousRemaining,
					$interestRate,
					$remainingInstallmentsBefore
				);

			$previousDue =
				round(
					$previousRemaining +
						$previousInterest,
					2
				);
		} else {
			throw new \RuntimeException(
				"Invalid sale interest type."
			);
		}

		/*
		* Eliminamos primero el registro financiero
		* asociado y luego el pago.
		*
		* Todo esto quedará protegido por la
		* transacción del endpoint.
		*/
		$this->repository
			->deleteInterestEarningByPaymentId(
				$paymentId
			);

		$this->repository
			->deletePayment(
				$paymentId,
				$companyId
			);

		/*
		* Finalmente restauramos los balances
		* anteriores de la venta.
		*/
		$this->repository
			->updateSaleBalances(
				$saleId,
				$companyId,
				$previousRemaining,
				$previousDue
			);

		return [
			"payment_id" =>
				$paymentId,

			"sale_id" =>
				$saleId,

			"restored_remaining" =>
				$previousRemaining,

			"restored_due" =>
				$previousDue
		];
	}
}