<?php

namespace App\Payments;

class PaymentRepository
{
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


	public function updateSaleDue(
		int $saleId,
		int $companyId,
		float $due
	): void {
		$result = \update_table(
			"sales",
			[
				"due" => $due
			],
			[
				"sales_id" => $saleId,
				"company_id" => $companyId
			],
			[
				"return_type" => "array"
			]
		);

		if (
			!is_array($result) ||
			empty($result["success"])
		) {
			throw new \RuntimeException(
				"Failed to update sales record."
			);
		}
	}
}