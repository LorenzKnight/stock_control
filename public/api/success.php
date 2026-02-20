<?php
define('IS_STRIPE_WEBHOOK', true);
require_once('../logic/stock_be.php');

if (isProductionEnv()) {
    $STRIPE_SK = $_ENV['STRIPE_SK_LIVE'];
} else {
    // Configuration test
    $STRIPE_SK = $_ENV['STRIPE_SK_TEST'];
}

\Stripe\Stripe::setApiKey($STRIPE_SK);

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    $_SESSION["payment_message"] = "Payment session ID is missing.";
    header("Location: ../profile.php");
    exit;
}

try {
    $session = \Stripe\Checkout\Session::retrieve([
        'id'     => $sessionId,
        'expand' => ['subscription', 'customer']
    ]);

    $isPaid = ($session->payment_status === 'paid');
    $isComplete = ($session->status === 'complete');

    if ($isPaid || $isComplete) {
        $_SESSION["payment_message"] = "Thank you! Your subscription is being processed.";
    } else {
        $_SESSION["payment_message"] = "Your payment was not completed successfully.";
    }

} catch (Exception $e) {
    $_SESSION["payment_message"] = "Error verifying your payment. Please try again later.";
}

header("Location: ../profile.php");
exit;
?>