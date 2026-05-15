<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $authUser = requireAuth();
    $userId = $authUser["user_id"] ?? null;

    if (!$userId) {
        throw new Exception("Unauthorized access.");
    }

    $supportedLangs = ['en', 'es', 'sv'];

    $lang = $_POST['lang'] ?? $_GET['lang'] ?? '';
    $lang = strtolower($lang);

    if (!in_array($lang, $supportedLangs, true)) {
        $browserLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
        $lang = in_array($browserLang, $supportedLangs, true) ? $browserLang : 'en';
    }

    if (isProductionEnv()) {
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SK_LIVE']);
        $baseUrl = rtrim($_ENV['APP_URL'], '/');
    } else {
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SK_TEST']);
        $baseUrl = 'http://localhost:8889';
    }

    $returnUrl = $baseUrl . '/' . $lang . '/profile';

    $sub = json_decode(select_from(
        "subscriptions",
        ["stripe_subscription_id"],
        ["user_id" => $userId],
        [
            "order_by" => "subsc_id",
            "order_direction" => "DESC",
            "fetch_first" => true
        ]
    ), true);

    if (empty($sub["success"]) || empty($sub["data"]["stripe_subscription_id"])) {
        throw new Exception("No subscription found.");
    }

    $stripeSubscriptionId = $sub["data"]["stripe_subscription_id"];

    $subscription = \Stripe\Subscription::retrieve($stripeSubscriptionId);
    $customerId = $subscription->customer ?? null;

    if (!$customerId) {
        throw new Exception("Stripe customer not found.");
    }

    $portalSession = \Stripe\BillingPortal\Session::create([
        'customer' => $customerId,
        'return_url' => $returnUrl,
    ]);

    echo json_encode([
        "success" => true,
        "url" => $portalSession->url
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}