<?php
require_once('../logic/stock_be.php');

// Guardar mensaje en sesión
$_SESSION["payment_message"] = "The payment was cancelled or not made.";

// Redirigir al perfil u otra página
header("Location: ../profile.php");
exit;
?>