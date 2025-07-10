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

    // 1. Crear producto dinámico
    $product = \Stripe\Product::create([
        'name' => 'AllStockControl License: ' . $selectedPackName,
    ]);

    // 2. Crear precio recurrente mensual
    $price = \Stripe\Price::create([
        'unit_amount' => $unitAmount,
        'currency' => 'usd',
        'recurring' => ['interval' => 'month'],
        'product' => $product->id,
    ]);

    // 3. Buscar si ya existe una suscripción activa
    $subscriptionRecord = json_decode(select_from("subscriptions", ["stripe_subscription_id", "subsc_id"], ["user_id" => $userId], [
        "order_by" => "subsc_id",
		"order_direction" => "DESC",
        "fetch_first" => true
    ]), true);
    
    if ($subscriptionRecord["success"] && !empty($subscriptionRecord["data"]["stripe_subscription_id"])) {
        // Usuario ya tiene suscripción, se cancela y se crea una nueva
		$previousSubscriptionId = $subscriptionRecord["data"]["stripe_subscription_id"];
		$subscId = $subscriptionRecord["data"]["subsc_id"];

		try {
			// Cancela inmediatamente la suscripción anterior
			$subscription = \Stripe\Subscription::retrieve($previousSubscriptionId);
			$subscription->cancel();

			// Crear nueva sesión de checkout para nueva suscripción
			$checkoutSession = \Stripe\Checkout\Session::create([
				'payment_method_types' => ['card'],
				'mode' => 'subscription',
				'line_items' => [[
					'price' => $price->id,
					'quantity' => 1,
				]],
				'success_url' => $myUrl.'api/success.php?session_id={CHECKOUT_SESSION_ID}',
				'cancel_url'  => $myUrl.'api/cancel.php',
				'metadata' => [
					'user_id'        => $userId,
					'package_id'     => $selectedPackId,
					'cost'           => $unitAmount / 100,
					'subsc_id'       => $subscId // importante para actualizar desde el webhook
				]
			]);

			echo json_encode([
				'success' => true,
				"message" => "Subscription updated to new plan via new session.",
				"img_gif" => "../images/sys-img/loading1.gif",
				'sessionId' => $checkoutSession->id
			]);
		} catch (\Exception $e) {
			throw new Exception("Error cancelling previous subscription: " . $e->getMessage());
		}
    } else {
        // No hay suscripción activa: crear nueva
        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $price->id,
                'quantity' => 1,
            ]],
            'success_url' => $myUrl.'api/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $myUrl.'api/cancel.php',
            'metadata' => [
                'user_id'    => $userId,
                'package_id' => $selectedPackId,
                'cost'       => $unitAmount / 100
            ]
        ]);

        echo json_encode([
            'success' => true,
            "message" => "Processing new subscription!",
            "img_gif" => "../images/sys-img/loading1.gif",
            'sessionId' => $checkoutSession->id
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}