<?php
require_once('../logic/stock_be.php');

\Stripe\Stripe::setApiKey('sk_test_51RgisC2U3dKi7TbUepLwOh1zeTOYwRz3QNgqRn18kJm5DH8hs6nbv0qiLBbmmpmtS7HjqbTG38TMZnuBkoINEvdc00AQjR1jfP');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $userId = $_SESSION['sc_UserId'] ?? null;
    if (!$userId) throw new Exception("User not authenticated.");

    if (empty($_POST['packs'])) {
		throw new Exception("You must select a member package.");
	}

	if (!isset($_POST['estimated_cost']) || !is_numeric($_POST['estimated_cost'])) {
		throw new Exception("Estimated cost is missing or invalid.");
	}

	$selectedPackId = intval($_POST['packs']);

    $selectedPack = json_decode(select_from("packages", ["package_name"], ["package_id" => $selectedPackId], ["fetch_first" => true]), true);
	if (!$selectedPack["success"]) throw new Exception("Package data not found.");

    $selectedPackName = $selectedPack["data"]["package_name"];

	$estimatedCost = floatval($_POST['estimated_cost']);
    $unitAmount = intval($estimatedCost * 100);

    $myUrl = 'http://localhost:8889/';
    // $myUrl = 'http://allstockcontrol.com/';

    $checkoutSession = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'unit_amount' => $unitAmount, // ejemplo: 1999 para $19.99
                'product_data' => [
                    'name' => 'AllStockControl License, Pack: ' . $selectedPackName,
                ],
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url'   => $myUrl.'api/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'    => $myUrl.'api/cancel.php',
        'metadata' => [
            'user_id'    => $userId,
            'package_id' => $selectedPackId,
            'cost'       => $unitAmount / 100  // Opcional, para registrar el precio como referencia
        ]
    ]);

    echo json_encode([
        'success' => true,
        "message" => "Processing subscription!",
		"img_gif" => "../images/sys-img/loading1.gif",
        'sessionId' => $checkoutSession->id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}