<?php
// /public/logic/pages_router.php

// Path limpio
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . ltrim($path, '/');

// quitar slash final excepto "/"
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

$supported = ['en','es','sv'];

// quitar prefijo de idioma si existe (para resolver slug)
$parts = explode('/', trim($path, '/')); // ['es','gdpr']
if (in_array($parts[0] ?? '', $supported, true)) {
    array_shift($parts);
}
$slug = implode('/', $parts); // 'gdpr' o ''

// HOME (/, /es, /en, /sv)
if ($slug === '') {
	include_once(__DIR__ . '/../components/message.php');
	include(__DIR__ . '/../components/front_header.php');
    include(__DIR__ . '/../components/banner_container.php');
    include(__DIR__ . '/../components/descriptions_container.php');
    include(__DIR__ . '/../components/features_container.php');
    include(__DIR__ . '/../components/pricing_container.php');
    return;
}

// Rutas bonitas -> archivo
$routes = [
    'gdpr' => 'gdpr',
    'terms' => 'terms',
];

// resolver archivo de página
$pageKey  = $routes[$slug] ?? $slug;
$pageFile = __DIR__ . '/../pages/' . $pageKey . '.php';

if (!is_file($pageFile)) {
    http_response_code(404);
    $pageFile = __DIR__ . '/../pages/404.php';
}

include_once(__DIR__ . '/../components/message.php');
include(__DIR__ . '/../components/pages_header.php');

// render
include $pageFile;
return;