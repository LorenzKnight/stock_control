<?php
require_once('../logic/stock_be.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isProductionEnv()) {
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SK_LIVE']);
} else {
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SK_TEST']);
}

$_SESSION["payment_message"] = "The payment was cancelled or not made.";

$supportedLangs = ['en', 'es', 'sv'];
$lang = 'en';

try {
    $sessionId = $_GET['session_id'] ?? null;

    if (!empty($sessionId)) {
        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        $sessionLang = $session->metadata->lang ?? null;

        if (!empty($sessionLang) && in_array($sessionLang, $supportedLangs, true)) {
            $lang = $sessionLang;
        }
    }
} catch (Exception $e) {
    // Si falla Stripe, usamos fallback
    $browserLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
    $lang = in_array($browserLang, $supportedLangs, true) ? $browserLang : 'en';
}

header("Location: /{$lang}/profile");
exit;
?>