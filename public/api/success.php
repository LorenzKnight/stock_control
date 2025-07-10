<?php
define('IS_STRIPE_WEBHOOK', true);
require_once('../logic/stock_be.php');

\Stripe\Stripe::setApiKey('sk_test_51RgisC2U3dKi7TbUepLwOh1zeTOYwRz3QNgqRn18kJm5DH8hs6nbv0qiLBbmmpmtS7HjqbTG38TMZnuBkoINEvdc00AQjR1jfP');

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