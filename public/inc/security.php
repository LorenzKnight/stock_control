<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUrl = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Lista de páginas que NO deben redirigir al usuario si no está logeado
$allowedPages = ["/", "/login.php", "/api/login.php", "/api/signup.php"];

if (!isset($_SESSION['sc_UserId'])) {
    if (!in_array($currentUrl, $allowedPages) && !in_array($scriptName, $allowedPages)) {
        header("Location: /");
        exit();
    }
} else {
    // Usuario logeado, puedes hacer debug aquí si lo necesitas
    // echo "<h3 style='color: red; text-align: center;'>Obs. The security module is active!</h3>";
    // echo $_SESSION['sc_UserId'];
}
?>