<?php
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

    $requiredFields = ["name", "surname", "birthday", "phone", "email", "password"];
    $data = [];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es obligatorio.");
        }
        $data[$field] = htmlspecialchars(trim($_POST[$field]));
    }

    $insertResponse = insert_into("users", $data, ["id" => "user_id"]);

    $insertResult = json_decode($insertResponse, true);

    if (!$insertResult["success"]) {
        throw new Exception("Error al insertar en la base de datos.");
    }

    $response = [
        "success" => true,
        "message" => "Datos recibidos correctamente",
        // "data" => $data
        "redirect_url" => "success2.php"
    ];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Responder con JSON
echo json_encode($response);
exit;
?>