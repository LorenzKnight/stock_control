<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

try {
    $authUser = requireAuth();
    $userId = intval($_GET["user_id"] ?? $authUser["user_id"]);
    $shippingId = intval($_GET["shipping_id"] ?? 0);

    if ($shippingId <= 0) throw new Exception("Invalid shipping ID.");

    $check = json_decode(select_from(
        "shipping_tracking",
        ["tracking_id"],
        ["shipping_id" => $shippingId, "scanned_by" => $userId],
        ["fetch_first" => true]
    ), true);

    echo json_encode([
        "exists" => $check["success"] && !empty($check["data"])
    ]);
} catch (Exception $e) {
    echo json_encode(["exists" => false, "error" => $e->getMessage()]);
}
exit;