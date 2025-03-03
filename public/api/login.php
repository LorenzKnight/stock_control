<?php
session_start();
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = ["success" => false, "message" => "Solicitud inválida"];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido");
    }

    if (empty($_POST["login_email"]) || empty($_POST["login_password"])) {
        throw new Exception("Correo y contraseña son obligatorios.");
    }

    $email = pg_escape_string(trim($_POST["login_email"]));
    $password = pg_escape_string(trim($_POST["login_password"]));

    $whereClause = ["email" => $email, "password" => $password];
    $options = ["fetch_first" => true];

    $userResponse = select_from("users", ["user_id", "email", "rank"], $whereClause, $options);
    $userData = json_decode($userResponse, true);

    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("Credenciales incorrectas.");
    }

    $user = $userData["data"];

    $_SESSION["mp_UserId"] = $user["user_id"];
    $_SESSION["mp_Mail"] = $user["email"];
    $_SESSION["mp_Nivel"] = $user["rank"];

    $response = [
        "success" => true,
        "message" => "Inicio de sesión exitoso.",
        "redirect_url" => "../profile.php"
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Responder con JSON
echo json_encode($response);
exit;
?>