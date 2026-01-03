<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Error loading user overview",
    "count"   => 0,
    "data"    => [],
    "meta"    => []
];

try {

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed");
    }

    // 🔐 Auth
    $authUser   = requireAuth();
    $authUserId   = $authUser["user_id"] ?? null;

    if (empty($authUserId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

    // 🎯 Usuario objetivo
    $targetId = intval($_GET["user_id"] ?? 0);
    if (!$targetId) {
        throw new Exception("User ID required");
    }

    // 👤 Usuario
    $userRes = json_decode(select_from(
        "users",
        [
            "user_id",
            "name",
            "surname",
            "image",
            "email",
            "username",
            "rank",
            "status",
            "signup_date"
        ],
        ["user_id" => $targetId],
        ["fetch_first" => true]
    ), true);

    if (!$userRes["success"] || empty($userRes["data"])) {
        throw new Exception("User not found");
    }

    $user = $userRes["data"];
    $user["full_name"] = trim(($user["name"] ?? '') . ' ' . ($user["surname"] ?? ''));

    // 🎭 Rol
    $roleRes = json_decode(select_from(
        "roles",
        ["role_name"],
        ["role_id" => $user["rank"]],
        ["fetch_first" => true]
    ), true);

    $user["rank_text"] = $roleRes["data"]["role_name"] ?? "Unknown";

    // 👥 Colaboradores (usuarios hijos)
    $collabRes = json_decode(select_from(
        "users",
        [
            "user_id",
            "name",
            "surname",
            "image",
            "email",
            "username",
            "rank",
            "status",
            "signup_date"
        ],
        ["parent_user" => $targetId],
        [
            "order_by" => "user_id",
            "order_direction" => "ASC",
            "fetch_all" => true
        ]
    ), true);

    $collaborators = [];

    if (!empty($collabRes["success"]) && !empty($collabRes["data"])) {
        foreach ($collabRes["data"] as $collab) {
            $collab["full_name"] = trim(
                ($collab["name"] ?? '') . ' ' . ($collab["surname"] ?? '')
            );
            $collaborators[] = $collab;
        }
    }

    // 📦 Suscripción
    $subRes = json_decode(select_from(
        "subscriptions",
        [
            "subsc_id",
            "package_id",
            "estimated_cost",
            "subscription_date",
            "expiration_date"
        ],
        ["user_id" => $targetId],
        ["fetch_first" => true]
    ), true);

    $subscription = $subRes["data"] ?? null;

    // 📦 Paquete
    $package = null;
    if ($subscription) {
        $pkgRes = json_decode(select_from(
            "packages",
            ["package_name", "package_price"],
            ["package_id" => $subscription["package_id"]],
            ["fetch_first" => true]
        ), true);

        $package = $pkgRes["data"] ?? null;
    }

    // 🔑 Impersonation token
    $impersonationToken = base64_encode(json_encode([
        "admin_id" => $authUserId,
        "user_id"  => $targetId,
        "time"     => time()
    ]));

    // ✅ RESPUESTA FINAL (CONTRATO ESTÁNDAR)
    $response = [
        "success"        => true,
        "message"        => "User overview loaded",
        "count"          => 1,
        "data"           => [$user],
        "subscription"   => $subscription,
        "package"        => $package,
        "collaborators"  => $collaborators,
        "meta"  => [
            "impersonation"  => [
                "token"      => $impersonationToken,
                "expires_in" => 300
            ]
        ]
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;