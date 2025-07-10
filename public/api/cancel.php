<?php
require_once('../logic/stock_be.php');

$sessionId = $_GET['session_id'] ?? null;

$_SESSION["payment_message"] = "The payment was cancelled or not made.";

if ($sessionId) {
    log_activity(
        $_SESSION['sc_UserId'] ?? null,
        "stripe_cancelled",
        "The user canceled the payment process. Session ID: $sessionId",
        "stripe_sessions",
        $_SESSION['sc_UserId'] ?? null
    );
}

header("Location: ../profile.php");
exit;
?>