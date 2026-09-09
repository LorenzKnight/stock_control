<?php

namespace App\Customers;

class CustomerRepository
{
	public function findCompanyIdByUserId(int $userId): ?int
	{
		$result = \select_from(
			"users",
			["company_id"],
			["user_id" => $userId],
			[
				"fetch_first" => true,
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"CustomerRepository expected an array response."
			);
		}

		if (
			empty($result["success"]) ||
			empty($result["data"]) ||
			empty($result["data"]["company_id"])
		) {
			return null;
		}

		return (int)$result["data"]["company_id"];
	}


	public function findCustomers(
		int $companyId,
		string $search = ''
	): array {
		$where = [
			"company_id" => $companyId
		];

		if ($search !== '') {
			$where["OR"] = [
				"customer_name ILIKE" => "%{$search}%",
				"customer_surname ILIKE" => "%{$search}%",
				"customer_document_no ILIKE" => "%{$search}%"
			];
		}

		$result = \select_from(
			"customers",
			[
				"customer_id",
				"customer_name",
				"customer_surname",
				"customer_email",
				"cu_country_code",
				"customer_phone",
				"customer_birthday",
				"customer_type",
				"customer_image",
				"customer_document_type",
				"customer_document_no",
				"customer_address",
				"customer_status",
				"references_1",
				"r1_country_code",
				"references_1_phone",
				"references_2",
				"r2_country_code",
				"references_2_phone"
			],
			$where,
			[
				"order_by" => "created_at",
				"order_direction" => "DESC",
				"return_type" => "array"
			]
		);

		if (!is_array($result)) {
			throw new \RuntimeException(
				"CustomerRepository expected an array response."
			);
		}

		return $result;
	}

    public function create(array $data): int
    {
        $result = \insert_into(
            "customers",
            $data,
            [
                "id" => "customer_id",
                "return_type" => "array"
            ]
        );

        if (
            !is_array($result) ||
            empty($result["success"]) ||
            empty($result["id"])
        ) {
            throw new \RuntimeException(
                "Error saving customer data."
            );
        }

        return (int)$result["id"];
    }


    public function findOnboardingByUserId(
        int $userId
    ): ?array {
        $result = \select_from(
            "user_onboarding",
            [
                "user_id",
                "client",
                "client_reward_seen"
            ],
            [
                "user_id" => $userId
            ],
            [
                "fetch_first" => true,
                "return_type" => "array"
            ]
        );

        if (!is_array($result)) {
            throw new \RuntimeException(
                "CustomerRepository expected an array response."
            );
        }

        if (
            !empty($result["success"]) &&
            !empty($result["data"])
        ) {
            return $result["data"];
        }

        if (
            ($result["message"] ?? "") === "No records found" ||
            (
                !empty($result["success"]) &&
                empty($result["data"])
            )
        ) {
            return null;
        }

        throw new \RuntimeException(
            "Could not read onboarding customer state."
        );
    }


    public function markCustomerOnboardingComplete(
        int $userId
    ): bool {
        $result = \update_table(
            "user_onboarding",
            [
                "client" => true,
                "updated_at" => date("Y-m-d H:i:s")
            ],
            [
                "user_id" => $userId
            ],
            [
                "return_type" => "array"
            ]
        );

        return
            is_array($result) &&
            !empty($result["success"]);
    }


    public function createCustomerOnboarding(
        int $userId
    ): bool {
        $result = \insert_into(
            "user_onboarding",
            [
                "user_id" => $userId,
                "client" => true,
                "client_reward_seen" => false,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ],
            [
                "return_type" => "array"
            ]
        );

        return
            is_array($result) &&
            !empty($result["success"]);
    }
}