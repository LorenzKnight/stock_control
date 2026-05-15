<?php
define('IS_STRIPE_WEBHOOK', true);
require_once('../logic/stock_be.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isProductionEnv()) {
    $STRIPE_SK = $_ENV['STRIPE_SK_LIVE'];
} else {
    // Configuration test
    $STRIPE_SK = $_ENV['STRIPE_SK_TEST'];
}

\Stripe\Stripe::setApiKey($STRIPE_SK);

$supportedLangs = ['en', 'es', 'sv'];
$lang = 'en';

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    $_SESSION["payment_message"] = "Payment session ID is missing.";

    $browserLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
    $lang = in_array($browserLang, $supportedLangs, true) ? $browserLang : 'en';

    header("Location: /{$lang}/profile");
    exit;
}

try {
    $session = \Stripe\Checkout\Session::retrieve([
        'id'     => $sessionId,
        'expand' => ['subscription', 'customer']
    ]);

    $sessionLang = $session->metadata->lang ?? null;
    $sessionLang = is_string($sessionLang) ? strtolower($sessionLang) : null;

    if (!empty($sessionLang) && in_array($sessionLang, $supportedLangs, true)) {
        $lang = $sessionLang;
    } else {
        $browserLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
        $lang = in_array($browserLang, $supportedLangs, true) ? $browserLang : 'en';
    }

    $isPaid = ($session->payment_status === 'paid');
    $isComplete = ($session->status === 'complete');

    if ($isPaid || $isComplete) {
        $_SESSION["payment_message"] = "Thank you! Your subscription is being processed.";
    } else {
        $_SESSION["payment_message"] = "Your payment was not completed successfully.";
    }

} catch (Exception $e) {
    $_SESSION["payment_message"] = "Error verifying your payment. Please try again later.";

    $browserLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
    $lang = in_array($browserLang, $supportedLangs, true) ? $browserLang : 'en';
}

header("Location: /{$lang}/profile");
exit;
?>