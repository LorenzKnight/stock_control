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