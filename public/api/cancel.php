<?php
require_once('../logic/stock_be.php');

// Guardar mensaje en sesión
$_SESSION["payment_message"] = "El pago fue cancelado. No se realizó ninguna modificación en tu cuenta.";

// Redirigir al perfil u otra página
header("Location: ../profile.php");
exit;
?>