<?php
define('IS_STRIPE_WEBHOOK', true);
require_once('../logic/stock_be.php');

\Stripe\Stripe::setApiKey('REMOVED_STRIPE_TEST_SECRET');

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    $_SESSION["payment_message"] = "Payment session ID is missing.";
    header("Location: ../profile.php");
    exit;
}

try {
    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    if ($session->status === 'complete' || $session->payment_status === 'paid') {
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