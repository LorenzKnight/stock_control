<?php
// ... ya tienes $lang y $t ...
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . ltrim($path, '/');
if ($path !== '/' && substr($path, -1) === '/') $path = rtrim($path, '/');

$parts = explode('/', trim($path, '/'));
if (in_array($parts[0] ?? '', ['en','es','sv'], true)) array_shift($parts);
$slug = $parts[0] ?? 'home';

$metaTitle = $t['title'] ?? 'AllStockControl';
$metaDesc  = $t['description'] ?? '';

if ($slug === 'gdpr') {
  $metaTitle = ($t['gdpr_title'] ?? 'GDPR') . ' | AllStockControl';
  $metaDesc  = 'GDPR policy for AllStockControl.';
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canon  = $scheme . '://' . $host . ($path ?? '/');