<?php
if (!defined('SKIP_SESSION') && session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUrl = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Lista de páginas que NO deben redirigir al usuario si no está logeado (paginas pernmitidas)
$allowedPages = ["/", "/login.php", "/api/login.php", "/api/signup.php"];

if (
    !defined('IS_STRIPE_WEBHOOK') &&
    !isset($_SESSION['sc_UserId']) &&
    !in_array($currentUrl, $allowedPages) &&
    !in_array($scriptName, $allowedPages)
) {
    header("Location: /");
    exit();
} else {
    // echo "<h3 style='color: red; text-align: center;'>Obs. The security module is active!</h3>";
    // echo $_SESSION['sc_UserId'];
}
?>