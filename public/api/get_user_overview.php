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

    // 🎭 Rol
    $rolesMap = [];

    $rolesRes = json_decode(select_from(
        "roles",
        ["role_id", "role_name"],
        [],
        ["fetch_all" => true]
    ), true);

    if (!empty($rolesRes["success"]) && !empty($rolesRes["data"])) {
        foreach ($rolesRes["data"] as $role) {
            $rolesMap[$role["role_id"]] = $role["role_name"];
        }
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
            "verified",
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
    $user["rank_text"] = $rolesMap[$user["rank"]] ?? "Unknown";

    // 🏢 Affiliates / Companies del usuario
	$affiliateRes = json_decode(select_from(
		"companies",
		[
			"company_id",
			"company_name",
			"company_logo",
			"created_at"
		],
		["user_id" => $targetId], // o owner_user_id
		[
			"order_by" => "company_id",
			"order_direction" => "ASC",
			"fetch_all" => true
		]
	), true);

	$affiliates = [];

	if (!empty($affiliateRes["success"]) && !empty($affiliateRes["data"])) {
		foreach ($affiliateRes["data"] as $company) {
			$affiliates[] = $company;
		}
	}

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
            "verified",
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

            $collab["rank_text"] = $rolesMap[$collab["rank"]] ?? "Unknown";

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
            [
                "package_name",
                "package_image",
                "package_price",
                "members_limit",
                "admins_limit",
                "branch_affiliate_limit",
                "products_limit"
            ],
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
        "meta"  => [
            "subscription"   => $subscription,
            "package"        => $package,
            "affiliate"      => $affiliates,
            "collaborators"  => $collaborators,
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