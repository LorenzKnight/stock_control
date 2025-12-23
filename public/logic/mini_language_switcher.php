<?php
// Idiomas soportados y selección por query (?lang=en|es|sv)
$supported = ['en','es','sv'];

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uriLang = substr($uri, 0, 2);

if (in_array($uriLang, $supported, true)) {
    $lang = $uriLang;
}
else if (isset($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
    $lang = strtolower($_GET['lang']);
}
// 2️⃣ SI NO: detectar idioma del navegador
else {
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
    $lang = in_array($browserLang, $supported, true) ? $browserLang : 'en';
}

// Textos por idioma
$i18n = [
	'en' => [
		'title'       => 'Inventory management software for small businesses | AllStockControl',
		'description' => 'Manage your inventory and stock in real time without spreadsheets. AllStockControl is cloud-based inventory software built for small businesses.',
		'features_h2' => 'Key Features',
		'pricing_h2'  => 'Pricing & Plans',
		'content_language' => 'en',

		// front header
		'nav_start'    => 'Start',
		'nav_features' => 'Features',
		'nav_pricing'  => 'Pricing',
		'nav_signup'   => 'Sign up',

		// JS toggle texts ✔ ADD THESE
		'toggle_signup' => 'Sign up',
		'toggle_login'  => 'Log In',

		// banner
		'home_main_h1'		 => 'Inventory management software for small businesses',
		'home_main_subtitle' => 'Manage your stock in real time without spreadsheets or complex systems. Control your inventory from any device.',
		'home_cta'			 => 'Try the inventory software for free',
		'signup_message'	 => 'Create your free account and start managing your company inventory easily and securely.',

		// descriptions container
		'desc_1_title' => 'Manage your inventory from anywhere, on any device',
		'desc_1_text'  => 'AllStockControl is cloud-based inventory management software built for small businesses. Track your stock in real time without spreadsheets or rigid systems, using your laptop, tablet, or phone—wherever you are.',

		'desc_2_title' => 'Keep your merchandise organized and under control',
		'desc_2_text'  => 'Track every product with accuracy and clarity. AllStockControl helps you manage stock entries, exits, and movements in real time, reducing errors, losses, and inventory chaos.',

		'desc_3_title' => 'Track inventory exits and order deliveries',
		'desc_3_text'  => 'Register every stock before shipping or delivery. AllStockControl helps prevent order mistakes, keeps inventory accurate, and ensures every package goes out correctly.',

		'desc_4_title' => 'Make decisions with confidence and full control',
		'desc_4_text'  => 'With clear, up-to-date inventory information, AllStockControl helps you make confident business decisions. Reduce mistakes, avoid losses, and stay in control without stress.',
	],
	'es' => [
		'title'       => 'Software de control de inventario para pequeñas empresas | AllStockControl',
		'description' => 'Controla tu inventario y stock en tiempo real sin Excel. AllStockControl es un software en la nube ideal para pequeñas empresas. Pruébalo gratis.',
		'features_h2' => 'Funciones clave',
		'pricing_h2'  => 'Precios y planes',
		'content_language' => 'es',

		// front header
		'nav_start'    => 'Inicio',
		'nav_features' => 'Funciones',
		'nav_pricing'  => 'Precios',
		'nav_signup'   => 'Regístrate',

		// JS toggle texts ✔ ADD THESE
		'toggle_signup' => 'Regístrate',
		'toggle_login'  => 'Iniciar sesión',

		// banner
		'home_main_h1' => 'Software de control de inventario para pequeñas empresas',
		'home_main_subtitle' => 'Gestiona tu stock en tiempo real, sin Excel y sin sistemas complicados. Controla tu inventario desde cualquier dispositivo.',
		'home_cta'	   => 'Probar gratis el software de inventario',
		'signup_message' => 'Crea tu cuenta gratis y empieza a controlar el inventario de tu empresa de forma simple y segura.',
	
		// descriptions container
		'desc_1_title' => 'Controla tu inventario desde cualquier lugar y dispositivo',
		'desc_1_text'  => 'AllStockControl es un software de control de inventario en la nube diseñado para pequeñas empresas. Gestiona tu stock en tiempo real sin Excel ni sistemas rígidos, desde tu computadora, tablet o celular, estés donde estés.',

		'desc_2_title' => 'Ordena y controla tu mercancía con total precisión',
		'desc_2_text'  => 'Mantén cada producto organizado, identificado y bajo control. AllStockControl te permite registrar entradas, salidas y movimientos de stock en tiempo real, evitando errores, pérdidas y desorden en tu inventario.',

		'desc_3_title' => 'Controla las salidas y entregas de tu inventario',
		'desc_3_text'  => 'Registra cada salida de mercancía con precisión antes de entregar o despachar. AllStockControl te ayuda a evitar errores en pedidos, mantener el stock actualizado y asegurar que cada entrega salga correctamente.',

		'desc_4_title' => 'Toma decisiones con tranquilidad y control total',
		'desc_4_text'  => 'Con información clara y actualizada en todo momento, AllStockControl te permite tomar decisiones seguras sobre tu inventario. Reduce errores, evita pérdidas y mantén el control de tu negocio sin estrés.',
	],
	'sv' => [
		'title'       => 'Lagerhanteringssystem för småföretag | AllStockControl',
		'description' => 'Hantera lager och stock i realtid utan kalkylblad. AllStockControl är ett molnbaserat lagerhanteringssystem för småföretag.',
		'features_h2' => 'Viktigaste funktionerna',
		'pricing_h2'  => 'Priser och paket',
		'content_language' => 'sv',

		// front header
		'nav_start'    => 'Start',
		'nav_features' => 'Funktioner',
		'nav_pricing'  => 'Priser',
		'nav_signup'   => 'Registrera dig',

		// JS toggle texts ✔ ADD THESE
		'toggle_signup' => 'Registrera dig',
		'toggle_login'  => 'Logga in',

		// banner
		'home_main_h1' => 'Lagerhanteringssystem för småföretag',
		'home_main_subtitle' => 'Hantera ditt lager i realtid utan kalkylblad eller krångliga system. Full kontroll från valfri enhet.',
		'home_cta'	   => 'Testa lagerhanteringssystemet gratis',
		'signup_message' => 'Skapa ett gratis konto och börja kontrollera företagets lager enkelt och säkert.',

		// descriptions container
		'desc_1_title' => 'Hantera ditt lager var som helst, på valfri enhet',
		'desc_1_text'  => 'AllStockControl är ett molnbaserat lagerhanteringssystem för småföretag. Följ lagret i realtid utan kalkylblad eller stela system, direkt från dator, surfplatta eller mobil – var du än befinner dig.',

		'desc_2_title' => 'Håll ordning och full kontroll på ditt lager',
		'desc_2_text'  => 'Organisera varje produkt med tydlighet och precision. AllStockControl gör det enkelt att registrera in- och utleveranser samt lagerförflyttningar i realtid, vilket minskar fel och förluster.',

		'desc_3_title' => 'Kontrollera utleveranser och orderflöden',
		'desc_3_text'  => 'Registrera varje varuutgång innan leverans. AllStockControl hjälper dig att undvika fel i beställningar, hålla lagret uppdaterat och säkerställa att varje leverans blir korrekt.',

		'desc_4_title' => 'Fatta beslut med lugn och full kontroll',
		'desc_4_text'  => 'Med tydlig och uppdaterad lagerinformation ger AllStockControl dig trygghet i dina beslut. Minska fel, undvik förluster och behåll kontrollen över ditt företag utan stress.',
	],
];

$t = $i18n[$lang];

// Helper para construir URLs con ?lang=…
function url_with_lang(string $targetLang): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/' . $targetLang . '/';
}
?>