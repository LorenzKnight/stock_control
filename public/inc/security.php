<?php
if (!defined('SKIP_SESSION') && session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (preg_match('#^/api/#i', $_SERVER['REQUEST_URI'] ?? '')) {
    return;
}

// No filtrar los webhooks de Stripe
if (defined('IS_STRIPE_WEBHOOK')) {
    return;
}

// Normalizar path (sin querystring, sin slash final excepto "/")
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

// Deja pasar estáticos
if (preg_match('#^/(css|js|images|img|fonts|uploads|vendor)/#i', $path)) {
    return;
}

// Páginas públicas (sin login), en ambas variantes .php y “bonita”
$allowed = [
    '/', 
    '/login', '/login.php',
    '/signup', '/signup.php',
    '/gdpr',
    '/api/login.php', '/api/signup.php',
    '/api/success.php', '/api/cancel.php',
    // agrega aquí otras públicas si las tienes:
    // '/pricing', '/pricing.php',
    // '/forgot-password', '/forgot-password.php',
];

// Si viene con prefijo de idioma, lo removemos SOLO para evaluar permisos
$pathNoLang = $path;
if (preg_match('#^/(es|en|sv)(/|$)#i', $path)) {
    $pathNoLang = preg_replace('#^/(es|en|sv)(?=/|$)#i', '', $path);
    if ($pathNoLang === '') $pathNoLang = '/';
}

// ...y en vez de usar $path, usa $pathNoLang para allowed-check
$loggedIn = !empty($_SESSION['sc_UserId']);
if (!$loggedIn && !in_array($pathNoLang, $allowed, true)) {
    header('Location: /');
    exit;
}