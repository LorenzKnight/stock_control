<?php
require_once('../logic/stock_be.php');

$_SESSION["payment_message"] = "The payment was cancelled or not made.";

header("Location: ../profile.php");
exit;
?>