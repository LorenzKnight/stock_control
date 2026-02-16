<?php
require_once('../logic/stock_be.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $isProd = isProductionEnv();

    $pk = $isProd ? ($_ENV['STRIPE_PK_LIVE'] ?? '') : ($_ENV['STRIPE_PK_TEST'] ?? '');

    if (!$pk) {
        throw new Exception("Stripe public key not configured.");
    }

    echo json_encode([
        "success" => true,
        "mode" => $isProd ? "live" : "test",
        "stripe_pk" => $pk
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}