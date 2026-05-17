<?php
require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

$response = [
    "success" => false,
    "message" => "User data not found",
    "data" => []
];

try {
    $authUser = requireAuth();
    $userId = $authUser["user_id"];

    if (empty($userId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }
    
    $userDataResponse = select_from("users", [
        "user_id",
        "parent_user",
        "name",
        "surname",
        "birthday",
        "country_code",
        "phone",
        "email",
        "image",
        "signup_date",
        "company_id",
        "package_id",
        "status",
        "gdpr",
        "terms"
    ], ["user_id" => $userId], ["fetch_first" => true]);

    $userData = json_decode($userDataResponse, true);

    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }

    $userInfo = $userData["data"];

    $altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];
    $planInfo = json_decode(select_from("users", [
        "package_id"
    ], ["user_id" =>  $altUser], ["fetch_first" => true]), true);

    if ($planInfo["success"] && isset($planInfo["data"]["package_id"])) {
        $packageId = $planInfo["data"]["package_id"] ?? null;
    }

    if (!empty($packageId)) {
        $packageInfo = json_decode(select_from("packages", [
            "package_id",
            "package_name",
            "package_image",
            'pack_color',
            "package_price",
            "members_limit",
            "admins_limit",
            "branch_affiliate_limit",
            "products_limit",
            "package_duration"
        ], ["package_id" =>  $packageId], ["fetch_first" => true]), true);

        if ($packageInfo["success"] && isset($packageInfo["data"])) {
            $userInfo["package_info"] = $packageInfo["data"];
        }
    } else {
        $userInfo["package_info"] = null;
    }
   
    // 🔑 Obtener información de tokens del usuario
    $tokensResponse = select_from(
        "user_tokens",
        [
            "token_id",
            "token",
            "status",
            "ip_address",
            "device_type",
            "location",
            "created_at",
            "expires_at"
        ],
        [
            "user_id" => $userId,
            "status"  => "active"
        ],
        ["order_by" => "created_at", "order_direction" => "DESC"]
    );
    $tokensData = json_decode($tokensResponse, true);

    if ($tokensData["success"] && !empty($tokensData["data"])) {
        $userInfo["tokens"] = array_values($tokensData["data"]);
    } else {
        $userInfo["tokens"] = [];
    }

    // Obtener informacion de la guia de onboarding
    $onboardingResponse = select_from(
        "user_onboarding",
        [
            "user_id",
            "company",
            "product",
            "client",
            "sale",
            "onboarding_completed"
        ],
        ["user_id" => $userId],
        ["fetch_first" => true]
    );
    $onboardingData = json_decode($onboardingResponse, true);

    if ($onboardingData["success"] && !empty($onboardingData["data"])) {
        $userInfo["onboarding_progress"] = $onboardingData["data"];
    } else {
        $userInfo["onboarding_progress"] = [
            "company_created" => false,
            "first_product_created" => false,
            "first_client_created" => false,
            "first_sale_created" => false,
            "onboarding_completed" => false
        ];
    }

    $response = [
        "success" => true,
        "message" => "User data retrieved successfully.",
        "data" => $userInfo
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;