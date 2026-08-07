<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request"
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed.");
    }

    $authUser = requireAuth();
    $userId = intval($authUser["user_id"] ?? 0);

    if ($userId <= 0) {
        throw new Exception(
            "Unauthorized access: invalid or missing token."
        );
    }

    $rewardType = trim($_POST["reward_type"] ?? '');

    /*
     * Lista controlada:
     * nunca usamos directamente un nombre de columna enviado
     * desde el frontend.
     */
    $rewardColumns = [
        "first_company" => "company_reward_seen",
        "first_product" => "product_reward_seen",
        "first_client"  => "client_reward_seen",
        "first_sale"    => "sale_reward_seen"
    ];

    if (
        $rewardType === '' ||
        !array_key_exists($rewardType, $rewardColumns)
    ) {
        throw new Exception("Invalid reward type.");
    }

    $rewardColumn = $rewardColumns[$rewardType];

    $onboardingData = json_decode(
        select_from(
            "user_onboarding",
            [
                "user_id",
                $rewardColumn
            ],
            [
                "user_id" => $userId
            ],
            [
                "fetch_first" => true
            ]
        ),
        true
    );

    if (!is_array($onboardingData)) {
        throw new Exception(
            "Invalid onboarding response."
        );
    }

    /*
     * Si el registro existe, marcamos la recompensa como vista.
     */
    if (
        !empty($onboardingData["success"]) &&
        !empty($onboardingData["data"])
    ) {
        $alreadySeen =
            $onboardingData["data"][$rewardColumn] === true ||
            $onboardingData["data"][$rewardColumn] === "t" ||
            $onboardingData["data"][$rewardColumn] === 1 ||
            $onboardingData["data"][$rewardColumn] === "1";

        /*
         * La operación es idempotente:
         * si ya estaba vista, respondemos correctamente
         * sin necesitar otro UPDATE.
         */
        if ($alreadySeen) {
            $response = [
                "success" => true,
                "message" => "Reward was already marked as seen.",
                "reward_type" => $rewardType
            ];

            echo json_encode($response);
            exit;
        }

        $updateData = json_decode(
            update_table(
                "user_onboarding",
                [
                    $rewardColumn => true,
                    "updated_at" => date("Y-m-d H:i:s")
                ],
                [
                    "user_id" => $userId
                ]
            ),
            true
        );

        if (
            !is_array($updateData) ||
            empty($updateData["success"])
        ) {
            throw new Exception(
                $updateData["message"] ??
                "Could not mark the reward as seen."
            );
        }

    } elseif (
        ($onboardingData["message"] ?? "") ===
        "No records found"
    ) {
        /*
         * Caso de recuperación:
         * si todavía no existe user_onboarding,
         * lo creamos con la recompensa ya vista.
         */
        $insertData = json_decode(
            insert_into(
                "user_onboarding",
                [
                    "user_id" => $userId,
                    $rewardColumn => true,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s")
                ]
            ),
            true
        );

        if (
            !is_array($insertData) ||
            empty($insertData["success"])
        ) {
            throw new Exception(
                $insertData["message"] ??
                "Could not create the onboarding state."
            );
        }

    } else {
        throw new Exception(
            $onboardingData["message"] ??
            "Could not read the onboarding state."
        );
    }

    $response = [
        "success" => true,
        "message" => "Reward marked as seen successfully.",
        "reward_type" => $rewardType
    ];

} catch (Throwable $error) {
    $response = [
        "success" => false,
        "message" => $error->getMessage()
    ];
}

echo json_encode($response);
exit;