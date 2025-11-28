<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

use Firebase\JWT\JWT;

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid refresh token",
    "data" => []
];

try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $refreshToken = $_POST["refresh_token"] ?? null;

    if (empty($refreshToken)) {
        throw new Exception("NO_REFRESH_TOKEN");
    }

    // 🔍 Buscar refresh token válido
    $tokenQuery = select_from(
        "user_tokens",
        [
            "user_id",
            "company_id",
            "device_type",
            "status"
        ],
        [
            "refresh_token" => $refreshToken,
            "status" => "active",
            "RAW" => "\"refresh_expires_at\" > NOW()"
        ],
        [
            "fetch_first" => true
        ]
    );

    $tokenData = json_decode($tokenQuery, true);

    if (!$tokenData["success"] || empty($tokenData["data"])) {
        throw new Exception("REFRESH_REVOKED");
    }

    $userId    = $tokenData["data"]["user_id"];
    $companyId = $tokenData["data"]["company_id"] ?? null;

    // ✅ Crear nuevo ACCESS TOKEN
    $issuedAt = time();
    $expiresAt = $issuedAt + JWT_EXPIRATION;

    $payload = [
        "iss"        => JWT_ISSUER,
        "iat"        => $issuedAt,
        "exp"        => $expiresAt,
        "user_id"    => $userId,
        "company_id" => $companyId
    ];

    $newJwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

    // 🔄 Actualizar SOLO el access token
    update_table(
        "user_tokens",
        [
            "token" => $newJwt,
            "expires_at" => date('Y-m-d H:i:s', $expiresAt)
        ],
        [
            "refresh_token" => $refreshToken
        ]
    );

    $response = [
        "success" => true,
        "message" => "Token refreshed",
        "data" => [
            "token" => $newJwt
        ]
    ];

} catch (Exception $e) {

    http_response_code(401);

    $response = [
        "success" => false,
        "reason" => $e->getMessage(),
        "data" => []
    ];
}

echo json_encode($response);
exit;