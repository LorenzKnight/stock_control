<?php
session_start();
require_once('../logic/stock_be.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    if (empty($_POST["login_email"]) || empty($_POST["login_password"])) {
        throw new Exception("Email and password are required.");
    }

    $email = pg_escape_string($sql, trim($_POST["login_email"]));
    $password = pg_escape_string($sql, trim($_POST["login_password"]));

    $whereClause = ["email" => $email];
    $options = ["fetch_first" => true];

    $userResponse = select_from("users", ["user_id", "email", "rank", "password"], $whereClause, $options);
    $userData = json_decode($userResponse, true);

    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("Incorrect credentials.");
    }

    $user = $userData["data"];
    $hashedPassword = $user["password"];

    if (!password_verify($password, $hashedPassword)) {
        throw new Exception("Contraseña incorrecta.");
    }

    $issuedAt = time();
    $expiresAt = $issuedAt + JWT_EXPIRATION; // por defecto: 24h

    $payload = [
        "iss" => JWT_ISSUER,
        "iat" => $issuedAt,
        "exp" => $expiresAt,
        "user_id" => $user["user_id"],
        "email" => $user["email"],
        "rank" => $user["rank"]
    ];

    $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
    $expiresAtISO = date('Y-m-d H:i:s', $expiresAt);

    update_table(
        "user_tokens",
        ["status" => "revoked"],
        ["user_id" => $user["user_id"], "status" => "active"]
    );

    // 🌍 Obtener IP
    function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }

    // 📱 Detectar dispositivo (muy simple, puedes mejorarlo después con una librería)
    function getDeviceType() {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (strpos($agent, 'mobile') !== false) return 'Mobile';
        if (strpos($agent, 'tablet') !== false) return 'Tablet';
        if (strpos($agent, 'windows') !== false) return 'Windows PC';
        if (strpos($agent, 'mac') !== false) return 'Mac';
        if (strpos($agent, 'linux') !== false) return 'Linux';
        return 'Unknown';
    }

    // 📍 Obtener ubicación aproximada (por IP)
    function getLocationByIP($ip) {
        try {
            $data = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,regionName,city");
            if ($data) {
                $json = json_decode($data, true);
                if (!empty($json['country'])) {
                    return "{$json['city']}, {$json['regionName']}, {$json['country']}";
                }
            }
        } catch (Exception $e) {
            // No hacemos nada si falla
        }
        return 'Unknown';
    }

    $user_ip = getUserIP();
    $user_device = getDeviceType();
    $user_location = getLocationByIP($user_ip);

    $insertResponse = insert_into("user_tokens", [
		"user_id"       => $user["user_id"],
		"token"         => $jwt,
		"status"        => "active",
		"ip_address"    => $user_ip,
		"device_type"   => $user_device,
		"location"      => $user_location,
		"created_at"    => date('Y-m-d H:i:s'),
		"expires_at"    => $expiresAtISO
    ]);

    $insertData = json_decode($insertResponse, true);

    // Validar resultado
    if (empty($insertData["success"]) || !$insertData["success"]) {
        $errorMsg = $insertData["message"] ?? "Error saving token to database.";
        throw new Exception($errorMsg);
    }

    $_SESSION["sc_UserId"] = $user["user_id"];
    $_SESSION["sc_Mail"] = $user["email"];
    $_SESSION["sc_Nivel"] = $user["rank"];

    $isMobile = isset($_POST["app_login"]) && $_POST["app_login"] === "true";

    $response = [
        "success" => true,
        "message" => "Logging in....",
        "token" => $jwt,
        "img_gif" => "../images/sys-img/loading1.gif",
        // "redirect_url" => "../profile.php"
        "redirect_url" => $isMobile ? "" : "../profile.php"
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

// Responder con JSON
echo json_encode($response);
exit;