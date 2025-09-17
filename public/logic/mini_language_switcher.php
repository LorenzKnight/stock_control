<?php
// Idiomas soportados y selección por query (?lang=en|es|sv)
$supported = ['en','es','sv'];
$lang = strtolower($_GET['lang'] ?? 'en');
if (!in_array($lang, $supported, true)) { $lang = 'en'; }

// Textos por idioma
$i18n = [
	'en' => [
		'title'       => 'Inventory Management Software | AllStockControl',
		'description' => 'Real-time stock control with multi-location, users and alerts. Track products, transfers and minimum levels. Try AllStockControl for free.',
		'h1'          => 'Real-time inventory control for growing teams',
		'features_h2' => 'Key Features',
		'pricing_h2'  => 'Pricing & Plans',
		'content_language' => 'en',
	],
	'es' => [
		'title'       => 'Software de control de inventario | AllStockControl',
		'description' => 'Control de stock en tiempo real con multi-sucursal, usuarios y alertas. Registra productos, transferencias y mínimos. Pruébalo gratis.',
		'h1'          => 'Control de inventario en tiempo real para equipos en crecimiento',
		'features_h2' => 'Funciones clave',
		'pricing_h2'  => 'Precios y planes',
		'content_language' => 'es',
	],
	'sv' => [
		'title'       => 'Lagerhanteringssystem | AllStockControl',
		'description' => 'Lager i realtid med flera filialer, användare och aviseringar. Spåra produkter, överföringar och miniminivåer. Prova gratis.',
		'h1'          => 'Lagerkontroll i realtid för växande team',
		'features_h2' => 'Viktigaste funktionerna',
		'pricing_h2'  => 'Priser och paket',
		'content_language' => 'sv',
	],
];

$t = $i18n[$lang];

// Helper para construir URLs con ?lang=…
function url_with_lang(string $targetLang): string {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$path   = strtok($_SERVER['REQUEST_URI'] ?? '/', '?'); // ruta sin query
	$qs     = $_GET;  // copia query actual
	$qs['lang'] = $targetLang;
	return $scheme.'://'.$host.$path.'?'.http_build_query($qs);
}
?>