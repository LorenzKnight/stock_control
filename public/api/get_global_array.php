<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid or missing key",
    "data" => []
];

try {
    if (!isset($_GET['key']) || empty($_GET['key'])) {
        throw new Exception("No array key provided.");
    }

    $key = $_GET['key'];

    // 🔒 Seguridad: lista blanca de claves permitidas
    $allowedKeys = ['ranks', 'vehicleTypes', 'documentTypes', 'customerTypes', 'customerStatus', 'maritalStatus', 'paymentTerms']; // Agrega más si lo necesitas

    if (!in_array($key, $allowedKeys)) {
        throw new Exception("Requested array is not allowed.");
    }

    // Obtener el array usando la clase GlobalArrays
    $data = GlobalArrays::$$key;

    $response["success"] = true;
    $response["message"] = "Array '$key' loaded successfully.";
    $response["data"] = $data;

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>