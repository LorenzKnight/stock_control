<?php
$allowed_origins = [
    "http://localhost:3000",             // local sin HTTPS
    "https://localhost:3000",            // local con HTTPS 
    "https://192.168.0.107:3000",        // ✅ IP REAL
    "http://192.168.0.107:3000",         // ✅ por si no usas HTTPS
    "https://www.allstockcontrol.com",   // dominio real
    "https://mobile.allstockcontrol.com" // mobile HTTPS
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    // bloquea orígenes no autorizados
    header("Access-Control-Allow-Origin: https://www.allstockcontrol.com");
}

header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-App-Client");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// Manejar preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}