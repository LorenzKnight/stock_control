<?php
require_once('../logic/stock_be.php');
session_start();

global $sql;
$sql = get_pg_connection();

use Firebase\JWT\JWT;

$token = trim($_GET['token']);

if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
    die("Invalid verification link.");
}

// Buscar token
$tokenResponse = select_from(
    "user_tokens ut
     JOIN users u ON u.user_id = ut.user_id",
    [
        "ut.token_id AS token_id",
        "ut.user_id AS user_id",
        "ut.expires_at AS expires_at",
        "ut.status AS token_status",
        "u.email AS email",
        "u.verified AS verified",
        "u.status AS user_status",
        "u.status_by_admin AS status_by_admin",
        "u.rank AS rank",
        "u.company_id AS company_id"
    ],
    [
        "RAW" => "
            ut.token = '" . pg_escape_string($sql, $token) . "'
            AND ut.refresh_token IS NULL
        "
    ],
    ["fetch_first" => true]
);

$result = json_decode($tokenResponse, true);

if (empty($result["success"]) || empty($result["data"])) {
    die("Invalid token.");
}

$tokenData = $result["data"];

// Validaciones
if ($tokenData["token_status"] !== "active") {
    die("Token not active.");
}

if (strtotime($tokenData["expires_at"]) < time()) {
    update_table("user_tokens", ["status" => "expired"], ["token_id" => $tokenData["token_id"]]);
    die("Token expired.");
}

if ((int)$tokenData["user_status"] === 0) {
    die("Account inactive.");
}

if ((int)$tokenData["status_by_admin"] === 0) {
    die("Account disabled.");
}

if ((int)$tokenData["verified"] !== 1) {
    update_table(
        "users",
        ["verified" => 1],
        ["user_id" => $tokenData["user_id"]]
    );
}

// 🔐 Invalidar token
update_table(
    "user_tokens",
    ["status" => "revoked"],
    ["token_id" => $tokenData["token_id"]]
);

$issuedAt  = time();
$expiresAt = $issuedAt + JWT_EXPIRATION;

$payload = [
    "iss"        => JWT_ISSUER,
    "iat"        => $issuedAt,
    "exp"        => $expiresAt,
    "user_id"    => $tokenData["user_id"],
    "email"      => $tokenData["email"],
    "rank"       => $tokenData["rank"],
    "company_id" => $tokenData["company_id"]
];

$jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

$refreshToken = bin2hex(random_bytes(64));

$deviceType = substr(getDeviceType() ?? '', 0, 100);
$deviceName = substr(getDeviceName() ?? '', 0, 150);

$user_ip = getUserIP();
$user_location = getLocationByIP($user_ip);

// ❌ Revocar tokens activos previos (mismo device)
update_table(
    "user_tokens",
    ["status" => "revoked"],
    [
        "user_id"     => $tokenData["user_id"],
        "device_type" => $deviceType,
        "status"      => "active"
    ]
);

// ✅ Insertar nuevo token de sesión
$insertResponse = insert_into("user_tokens", [
    "user_id"               => $tokenData["user_id"],
    "token"                 => $jwt,
    "refresh_token"         => $refreshToken,
    "status"                => "active",
    "ip_address"            => $user_ip,
    "device_type"           => $deviceType,
    "device_name"           => $deviceName,
    "location"              => $user_location,
    "created_at"            => date('Y-m-d H:i:s'),
    "expires_at"            => date('Y-m-d H:i:s', $expiresAt),
    "refresh_expires_at"    => date('Y-m-d H:i:s', time() + 60*60*24*30)
]);

$insertResult = json_decode($insertResponse, true);

if (!$insertResult["success"]) {
    die("Error creating session token.");
}

// 🔓 Login automático
$_SESSION["sc_UserId"]    = $tokenData["user_id"];
$_SESSION["sc_Mail"]      = $tokenData["email"];
$_SESSION["sc_Nivel"]     = $tokenData["rank"];
$_SESSION["sc_CompanyId"] = $tokenData["company_id"];

// 🚀 Redirigir
header("Location: ../profile.php");
exit;