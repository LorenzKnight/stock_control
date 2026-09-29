<?php

namespace App\Payments;

class PaymentRepository
{
	public function findPayments(
		int $companyId,
		string $search = '',
		?int $paymentId = null
	): array {
		$where = [
			"company_id" =>
				$companyId
		];

		if ($search !== '') {
			$where["OR"] = [
				"CAST(ord_no AS TEXT) ILIKE" =>
					"%{$search}%",

				"CAST(payment_no AS TEXT) ILIKE" =>
					"%{$search}%",

				"CAST(payment_date AS TEXT) ILIKE" =>
					"%{$search}%"
			];
		} elseif (
			$paymentId !== null &&
			$paymentId > 0
		) {
			$where["payment_id"] =
				$paymentId;
		}

		$result =
			\select_from(
				"payments",
				[
					"payment_id",
					"ord_no",
					"payment_no",
					"sales_id",
					"customer_id",
					"currency",
					"payment_method",
					"amount",
					"interest",
					"installments_month",
					"no_installments",
					"payment_date",
					"due",
					"status",
					"created_by",
					"created_at"
				],
				$where,
				[
					"order_by" =>
						"created_at",

					"order_direction" =>
						"DESC",

					"return_type" =>
						"array"
				]
			);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			isset($result["data"]) &&
			is_array($result["data"])
		) {
			return array_values(
				$result["data"]
			);
		}

		if (
			($result["message"] ?? '') ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not load payments."
		);
	}

	public function findOrderSuggestions(
		int $companyId,
		string $search
	): array {
		$result =
			\select_from(
				"sales",
				[
					"ord_no",
					"customer_id"
				],
				[
					"company_id" =>
						$companyId,

					"CAST(ord_no AS TEXT) ILIKE" =>
						"%{$search}%"
				],
				[
					"limit" => 10,

					"order_by" =>
						"ord_no",

					"order_direction" =>
						"DESC",

					"return_type" =>
						"array"
				]
			);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			isset($result["data"]) &&
			is_array($result["data"])
		) {
			return array_values(
				$result["data"]
			);
		}

		if (
			($result["message"] ?? '') ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return [];
		}

		throw new \RuntimeException(
			"Could not load order suggestions."
		);
	}

	public function findSaleByOrderNumber(
		int $ordNo,
		int $companyId
	): ?array {
		$result = \select_from(
			"sales",
			[
				"sales_id",
				"customer_id",
				"price_sum",
                "remaining",
	            "interest_type",
				"interest",
				"installments_month",
				"due",
				"currency"
			],
			[
				"ord_no" => $ordNo,
				"company_id" => $companyId
			],
			[
				"fetch_first" => true,
                "for_update" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? "") ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read payment sale."
		);
	}


	public function getNextPaymentNumber(
		int $companyId
	): int {
		return \get_next_increment_value(
			"payments",
			"payment_no",
			$companyId,
			20000000
		);
	}


	public function getNextInstallmentNumber(
        int $saleId
    ): int {
        $result = \select_from(
            "payments",
            [
                "payment_id"
            ],
            [
                "sales_id" => $saleId
            ],
            [
                "return_type" => "array"
            ]
        );

        if (!is_array($result)) {
            throw new \RuntimeException(
                "PaymentRepository expected an array response."
            );
        }

        if (!empty($result["success"])) {
            $count = isset($result["count"])
				? (int)$result["count"]
				: (
					is_array($result["data"] ?? null)
						? count($result["data"])
						: 0
				);

            return $count + 1;
        }

        if (($result["message"] ?? "") === "No records found") {
            return 1;
        }

        throw new \RuntimeException(
            "Could not determine the next installment number."
        );
    }


	public function createPayment(
		array $data
	): int {
		$result = \insert_into(
			"payments",
			$data,
			[
				"id" => "payment_id",
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"]) ||
			empty($result["id"])
		) {
			throw new \RuntimeException(
				"Failed to insert payment record."
			);
		}

		return (int)$result["id"];
	}


	public function createInterestEarning(
		array $data
	): void {
		$result = \insert_into(
			"interest_earnings",
			$data,
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Failed to insert interest record."
			);
		}
	}


	public function updateSaleBalances(
		int $saleId,
		int $companyId,
		float $remaining,
		float $due
	): void {
		$result = \update_table(
			"sales",
			[
				"remaining" =>
					$remaining,

				"due" =>
					$due
			],
			[
				"sales_id" =>
					$saleId,

				"company_id" =>
					$companyId
			],
			[
				"return_type" =>
					"array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Failed to update sales balances."
			);
		}
	}


	public function findSaleInfoByOrderNumber(
		int $ordNo,
		int $companyId
	): ?array {
		$result = \select_from(
			"sales",
			[
				"sales_id",
				"ord_no",
				"customer_id",
				"currency",
				"interest_type",
				"interest",
				"installments_month",
				"remaining",
				"due"
			],
			[
				"ord_no" =>
					$ordNo,

				"company_id" =>
					$companyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? "") ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read payment order information."
		);
	}


	public function findCustomerInfoById(
		int $customerId,
		int $companyId
	): ?array {
		$result = \select_from(
			"customers",
			[
				"customer_name",
				"customer_surname",
				"customer_phone",
				"customer_document_type",
				"customer_document_no",
				"customer_email"
			],
			[
				"customer_id" =>
					$customerId,

				"company_id" =>
					$companyId
			],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? "") ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read payment customer information."
		);
	}


	public function findPaymentForDelete(
		int $paymentId,
		int $companyId
	): ?array {
		$result =
			\select_from(
				"payments",
				[
					"payment_id",
					"sales_id",
					"amount",
					"interest",
					"no_installments"
				],
				[
					"payment_id" =>
						$paymentId,

					"company_id" =>
						$companyId
				],
				[
					"fetch_first" => true,
					"for_update" => true,
					"return_type" => "array"
				]
			);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? '') ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read payment."
		);
	}


	public function findLatestPaymentForSale(
		int $saleId,
		int $companyId
	): ?array {
		$result =
			\select_from(
				"payments",
				[
					"payment_id",
					"no_installments"
				],
				[
					"sales_id" =>
						$saleId,

					"company_id" =>
						$companyId
				],
				[
					"order_by" =>
						"payment_id",

					"order_direction" =>
						"DESC",

					"limit" => 1,
					"fetch_first" => true,
					"return_type" => "array"
				]
			);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? '') ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not determine latest payment."
		);
	}


	public function findSaleForPaymentDelete(
		int $saleId,
		int $companyId
	): ?array {
		$result =
			\select_from(
				"sales",
				[
					"sales_id",
					"remaining",
					"due",
					"interest_type",
					"interest",
					"installments_month"
				],
				[
					"sales_id" =>
						$saleId,

					"company_id" =>
						$companyId
				],
				[
					"fetch_first" => true,
					"for_update" => true,
					"return_type" => "array"
				]
			);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (
			!empty($result["success"]) &&
			!empty($result["data"])
		) {
			return $result["data"];
		}

		if (
			($result["message"] ?? '') ===
				"No records found" ||
			(
				!empty($result["success"]) &&
				empty($result["data"])
			)
		) {
			return null;
		}

		throw new \RuntimeException(
			"Could not read payment sale."
		);
	}


	public function deleteInterestEarningByPaymentId(
		int $paymentId
	): void {
		$result =
			\delete_from(
				"interest_earnings",
				[
					"payment_id" =>
						$paymentId
				],
				[
					"return_type" =>
						"array"
				]
			);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"PaymentRepository expected an array response."
			);
		}

		if (empty($result["success"])) {
			throw new \RuntimeException(
				"Failed to delete payment interest record."
			);
		}
	}


	public function deletePayment(
		int $paymentId,
		int $companyId
	): void {
		$result =
			\delete_from(
				"payments",
				[
					"payment_id" =>
						$paymentId,

					"company_id" =>
						$companyId
				],
				[
					"return_type" =>
						"array"
				]
			);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Failed to delete payment."
			);
		}

		if (
			(int)(
				$result["count"]
					?? 0
			) !== 1
		) {
			throw new \RuntimeException(
				"Payment was not deleted."
			);
		}
	}
}