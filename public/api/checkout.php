<?php

use Stripe\BillingPortal\Configuration;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

if (isProductionEnv()) {
    $STRIPE_SK = $_ENV['STRIPE_SK_LIVE'];
    $myUrl = $_ENV['APP_URL'];
} else {
    // Configuration test
    $STRIPE_SK = $_ENV['STRIPE_SK_TEST'];
    $myUrl = 'http://localhost:8889/';
}

\Stripe\Stripe::setApiKey($STRIPE_SK);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $authUser = requireAuth();
    $userId   = $authUser["user_id"] ?? null;

    if (!$userId) {
        throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    if (empty($_POST['packs'])) {
        throw new Exception("You must select a member package.");
    }

    $supportedLangs = ['en', 'es', 'sv'];

    $lang = $_POST['lang'] ?? $_GET['lang'] ?? '';
    $lang = strtolower($lang);

    if (!in_array($lang, $supportedLangs, true)) {
        $browserLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
        $lang = in_array($browserLang, $supportedLangs, true) ? $browserLang : 'en';
    }

    $selectedPackId = intval($_POST['packs']);
    $extraPack      = $_POST['extra_pack'] ?? null;

    $packRow = json_decode(
        select_from(
            "packages",
            ["package_name", "package_price"],
            ["package_id" => $selectedPackId],
            ["fetch_first" => true]
        ),
        true
    );

    if (!$packRow["success"] || empty($packRow["data"])) {
        throw new Exception("Package data not found.");
    }

    $selectedPackName = $packRow["data"]["package_name"] ?? null;

    if (!$selectedPackName) {
        throw new Exception("Package name not found in DB.");
    }

    $basePrice = null;

	if (isset($packRow["data"]["package_price"]) && is_numeric($packRow["data"]["package_price"])) {
		$basePrice = (float)$packRow["data"]["package_price"];
	}

    if ($basePrice === null) {
        throw new Exception("Package price field not found. Please use one column name (package_price/monthly_price/price) or adjust the code.");
    }

    $extraCost = 0.00;

	if (!empty($extraPack)) {
		$serviceId = intval($extraPack);

		if ($serviceId <= 0) {
			throw new Exception("Invalid extra pack selection.");
		}

		$serviceRow = json_decode(
			select_from(
				"extra_services",
				["service_price", "status", "user_id"],
				["service_id" => $serviceId],
				["fetch_first" => true]
			),
			true
		);

		if (!$serviceRow["success"] || empty($serviceRow["data"])) {
			throw new Exception("Extra service not found.");
		}

		if (isset($serviceRow["data"]["status"]) && (int)$serviceRow["data"]["status"] !== 1) {
			throw new Exception("Selected extra service is not active.");
		}

		$serviceUserId = $serviceRow["data"]["user_id"] ?? null;

		if ($serviceUserId !== null && (int)$serviceUserId !== (int)$userId) {
			throw new Exception("Extra service not allowed for this user.");
		}

		$servicePrice = $serviceRow["data"]["service_price"] ?? null;

		if (!is_numeric($servicePrice)) {
			throw new Exception("Extra service price is invalid.");
		}

		$extraCost = (float)$servicePrice;

		if ($extraCost < 0) {
			throw new Exception("Extra service price is invalid.");
		}
	}

    $finalMonthlyCost = $basePrice + $extraCost;

    if ($finalMonthlyCost <= 0) {
        throw new Exception("Calculated cost is invalid.");
    }

	$clientEstimated = isset($_POST['estimated_cost']) && is_numeric($_POST['estimated_cost'])
		? (float)$_POST['estimated_cost']
		: null;

	if ($clientEstimated !== null) {
		$diff = abs($finalMonthlyCost - $clientEstimated);

		// bloquear si intentan manipular
		if ($diff > 0.05) {
			throw new Exception("Invalid price estimate.");
		}
	}

    $unitAmount = (int) round($finalMonthlyCost * 100);

    $product = \Stripe\Product::create([
        'name' => 'AllStockControl License: ' . $selectedPackName,
        'metadata' => [
            'app_user_id' => (string)$userId,
            'package_id'  => (string)$selectedPackId,
            'extra_pack'  => (string)($extraPack ?? ''),
            'lang'        => $lang,
            'type'        => 'allstockcontrol_subscription'
        ]
    ]);

    $price = \Stripe\Price::create([
        'unit_amount' => $unitAmount,
        'currency'    => 'usd',
        'recurring'   => ['interval' => 'month'],
        'product'     => $product->id,
        'metadata'    => [
            'app_user_id' => (string)$userId,
            'package_id'  => (string)$selectedPackId,
            'extra_pack'  => (string)($extraPack ?? ''),
            'lang'        => $lang,
            'type'        => 'allstockcontrol_subscription_price'
        ]
    ]);

    $subscriptionRecord = json_decode(
        select_from(
            "subscriptions",
            ["stripe_subscription_id", "subsc_id"],
            ["user_id" => $userId],
            [
				"order_by" => "subsc_id",
				"order_direction" => "DESC",
				"fetch_first" => true
    		]
		),
	    true
    );
    
	$hasPrevious = (
        $subscriptionRecord["success"] &&
        !empty($subscriptionRecord["data"]["stripe_subscription_id"])
    );

    $previousSubscriptionId = $hasPrevious ? $subscriptionRecord["data"]["stripe_subscription_id"] : null;
    $subscId = $hasPrevious ? $subscriptionRecord["data"]["subsc_id"] : null;

    $metadata = [
        'user_id'    => $userId,
        'package_id' => $selectedPackId,
        'cost'       => $unitAmount / 100,
        'extra_pack' => $extraPack,
        'lang'       => $lang
    ];

	if ($hasPrevious && !empty($previousSubscriptionId)) {
        $metadata['previous_subscription_id'] = (string)$previousSubscriptionId;

        if (!empty($subscId)) {
            $metadata['subsc_id'] = (string)$subscId;
        }
    }

	// Crear nueva sesión de checkout para nueva suscripción
    $baseUrl = rtrim($myUrl, '/');

	$checkoutSession = \Stripe\Checkout\Session::create([
		'payment_method_types' => ['card'],
		'mode' => 'subscription',
		'line_items' => [[
			'price' => $price->id,
			'quantity' => 1,
		]],
		'success_url' => $baseUrl . '/api/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => $baseUrl . '/api/cancel.php?session_id={CHECKOUT_SESSION_ID}',
        'metadata'    => $metadata
	]);

	echo json_encode([
		'success' => true,
		'message'  => $hasPrevious
            ? "Checkout created. Previous subscription will be cancelled ONLY after successful payment."
            : "Processing new subscription!",
		"img_gif" => "../images/sys-img/loading1.gif",
		'sessionId' => $checkoutSession->id
	]);

	exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
	exit;
}