<?php
require_once('../logic/stock_be.php');

header('Content-Type: application/javascript');

if (!empty($_SESSION["payment_message"])) {
	echo "window.paymentMessage = " . json_encode($_SESSION["payment_message"]) . ";\n";
	unset($_SESSION["payment_message"]);
} else {
	echo "window.paymentMessage = null;\n";
}
?>