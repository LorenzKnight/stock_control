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
		'description' => 'Cloud-based inventory management software for small businesses. Track stock in real time, manage multiple locations, and avoid spreadsheets. Try AllStockControl free.',
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
		'home_cta'			 => 'Try inventory software for free',
		'cta_note'			 => 'No credit card required · Cancel anytime',
		'signup_message'	 => 'Create your free account and start managing your company inventory easily and securely.',

		// descriptions container
		'desc_1_title' => 'Cloud-based inventory control from anywhere',
		'desc_1_text'  => 'AllStockControl is cloud-based inventory management software built for small businesses. Manage your stock in real time from your laptop, tablet, or phone—without spreadsheets or fixed locations.',

		'desc_2_title' => 'Keep your inventory organized and under control',
		'desc_2_text'  => 'Organize every product with clarity and accuracy. AllStockControl lets you track inventory entries, exits, and movements in real time—reducing errors, losses, and stock confusion.',

		'desc_3_title' => 'Track stock exits and deliveries accurately',
		'desc_3_text'  => 'Register every inventory exit before shipping or delivery. AllStockControl helps prevent order mistakes, keeps stock accurate, and ensures every delivery goes out correctly.',

		'desc_4_title' => 'Make confident decisions with full inventory control',
		'desc_4_text'  => 'With clear, up-to-date inventory data, AllStockControl helps you make confident business decisions. Reduce losses, avoid mistakes, and stay in control with ease.',

		// features section
		'features_title' => 'Inventory management features',

		'feature_plan_title' => 'Scalable plans for growing teams',
		'feature_plan_desc'  => 'Choose inventory management plans based on team size, number of branches, and product volume—so your inventory system grows with your business.',

		'feature_multibranch_title' => 'Multi-location inventory management',
		'feature_multibranch_desc'  => 'Manage inventory by branch or warehouse and keep accurate stock levels for every location in real time.',

		'feature_roles_title' => 'User roles and access control',
		'feature_roles_desc'  => 'Assign roles and permissions to admins and team members to keep inventory operations secure, organized, and controlled.',

		'feature_catalog_title' => 'Organized product catalog',
		'feature_catalog_desc'  => 'Manage products with images, descriptions, categories, and subcategories for faster and clearer inventory control.',

		'feature_search_title' => 'Fast search and smart filtering',
		'feature_search_desc'  => 'Find products instantly using fast search and smart filters—save time during daily inventory operations.',

		'feature_stock_title' => 'Real-time stock movements',
		'feature_stock_desc'  => 'Track inventory inputs, outputs, and adjustments in real time to maintain accurate stock levels at all times.',

		'feature_transfers_title' => 'Inter-branch inventory transfers',
		'feature_transfers_desc'  => 'Transfer stock between locations with full traceability—no missing items, no confusion, and total control.',

		'feature_min_stock_title' => 'Low-stock alerts and minimum thresholds',
		'feature_min_stock_desc'  => 'Set minimum stock levels and receive alerts before products run out—reduce losses and avoid delays.',

		'feature_cloud_title' => 'Cloud-based inventory management system',
		'feature_cloud_desc'  => 'Access your inventory from anywhere with a secure cloud-based system—on laptop, tablet, or phone.',

		'feature_responsive_title' => 'Responsive and easy-to-use interface',
		'feature_responsive_desc'  => 'A clean, fast, and intuitive interface designed for desktop and mobile—easy for your entire team to use.',

		// pricing section
		'pricing_title_main' => 'Flexible inventory management plans for small businesses',
		'pricing_subtitle'   => 'AllStockControl helps small businesses manage inventory in a simple, clear, and efficient way. Track stock movements, organize products, and generate reports from anywhere. Choose a flexible monthly plan with no long-term commitment and cancel anytime.',
		'pricing_employees_title' => 'Choose the number of employees',
	],
	'es' => [
		'title'       => 'Software de control de inventario para pequeñas empresas | AllStockControl',
		'description' => 'Software de control de inventario en la nube para pequeñas empresas. Controla tu stock en tiempo real, sin Excel y desde cualquier dispositivo. Pruébalo gratis.',
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
		'home_main_h1'		 => 'Software de control de inventario para pequeñas empresas',
		'home_main_subtitle' => 'Gestiona tu stock en tiempo real, sin Excel y sin sistemas complicados. Controla tu inventario desde cualquier dispositivo.',
		'home_cta'			 => 'Probar el software de inventario gratis',
		'cta_note'			 => 'No se requiere tarjeta de crédito · Cancela cuando quieras',
		'signup_message'	 => 'Crea tu cuenta gratis y empieza a controlar el inventario de tu empresa de forma simple y segura.',
	
		// descriptions container
		'desc_1_title' => 'Control de inventario en la nube, desde cualquier lugar',
		'desc_1_text'  => 'AllStockControl es un software de control de inventario en la nube diseñado para pequeñas empresas. Gestiona tu stock en tiempo real desde tu computadora, tablet o celular, sin depender de Excel ni de un lugar físico.',

		'desc_2_title' => 'Mantén tu inventario organizado y bajo control',
		'desc_2_text'  => 'Organiza cada producto con precisión y claridad. AllStockControl te permite registrar entradas, salidas y movimientos de inventario en tiempo real, reduciendo errores, pérdidas y desorden en tu stock.',
		
		'desc_3_title' => 'Controla salidas de stock y entregas sin errores',
		'desc_3_text'  => 'Registra cada salida de inventario antes de despachar o entregar pedidos. AllStockControl ayuda a evitar errores en envíos, mantiene el stock actualizado y asegura entregas correctas.',

		'desc_4_title' => 'Toma decisiones con información clara y confiable',
		'desc_4_text'  => 'Con datos de inventario siempre actualizados, AllStockControl te permite tomar decisiones seguras. Reduce pérdidas, evita errores y mantén el control total de tu negocio.',

		// features section
		'features_title' => 'Funciones del software de control de inventario',

		'feature_plan_title' => 'Planes escalables según el tamaño de tu equipo',
		'feature_plan_desc'  => 'Elige planes de control de inventario según la cantidad de usuarios, sucursales y productos. Tu sistema de inventario crece junto a tu empresa.',

		'feature_multibranch_title' => 'Inventario por sucursal o almacén',
		'feature_multibranch_desc'  => 'Gestiona y separa el stock por ubicación y mantén niveles de inventario precisos en cada sucursal o almacén en tiempo real.',

		'feature_roles_title' => 'Usuarios, roles y permisos',
		'feature_roles_desc'  => 'Asigna roles y permisos a administradores y empleados para operar el inventario con seguridad, orden y control.',

		'feature_catalog_title' => 'Catálogo de productos organizado',
		'feature_catalog_desc'  => 'Gestiona productos con imágenes, descripciones, categorías y subcategorías para un control de inventario más rápido y claro.',

		'feature_search_title' => 'Búsqueda y filtros rápidos',
		'feature_search_desc'  => 'Encuentra productos al instante con búsqueda rápida y filtros inteligentes, ahorrando tiempo en la operación diaria.',

		'feature_stock_title' => 'Movimientos de stock en tiempo real',
		'feature_stock_desc'  => 'Registra entradas, salidas y ajustes de inventario en tiempo real para mantener el stock siempre actualizado.',

		'feature_transfers_title' => 'Transferencias entre sucursales',
		'feature_transfers_desc'  => 'Mueve mercancía entre ubicaciones con trazabilidad completa, sin pérdidas ni confusión en el control del stock.',

		'feature_min_stock_title' => 'Stock mínimo y alertas automáticas',
		'feature_min_stock_desc'  => 'Define niveles mínimos de stock y recibe alertas antes de quedarte sin productos, evitando pérdidas y retrasos.',

		'feature_cloud_title' => 'Software de control de inventario en la nube',
		'feature_cloud_desc'  => 'Accede a tu inventario desde cualquier lugar con un sistema seguro en la nube, desde tu laptop, tablet o celular.',

		'feature_responsive_title' => 'Interfaz responsive y fácil de usar',
		'feature_responsive_desc'  => 'Interfaz rápida, clara y optimizada para computadoras y dispositivos móviles, fácil de usar para todo tu equipo.',

		// pricing section
		'pricing_title_main' => 'Planes flexibles de control de inventario para pequeñas empresas',
		'pricing_subtitle'   => 'AllStockControl ayuda a pequeñas empresas a gestionar su inventario de forma simple, clara y eficiente. Controla entradas y salidas de stock, organiza tus productos y genera reportes desde cualquier lugar. Elige un plan mensual flexible, sin contratos largos y con cancelación en cualquier momento.',
		'pricing_employees_title' => 'Selecciona la cantidad de empleados',
	],
	'sv' => [
		'title'       => 'Lagerhanteringssystem för småföretag | AllStockControl',
		'description' => 'Molnbaserat lagerhanteringssystem för småföretag. Följ lagersaldo i realtid, hantera flera platser och slipp kalkylblad. Testa gratis.',
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
		'home_main_h1'		 => 'Lagerhanteringssystem för småföretag',
		'home_main_subtitle' => 'Hantera ditt lager i realtid utan kalkylblad eller krångliga system. Full kontroll från valfri enhet.',
		'home_cta'			 => 'Testa lagerhanteringssystemet gratis',
		'cta_note'			 => 'Inget kreditkort krävs · Avsluta när som helst',
		'signup_message'	 => 'Skapa ett gratis konto och börja kontrollera företagets lager enkelt och säkert.',

		// descriptions container
		'desc_1_title' => 'Molnbaserad lagerkontroll – var du än är',
		'desc_1_text'  => 'AllStockControl är ett molnbaserat lagerhanteringssystem för småföretag. Hantera ditt lager i realtid från dator, surfplatta eller mobil – utan kalkylblad eller fasta platser.',

		'desc_2_title' => 'Håll lagret organiserat och under full kontroll',
		'desc_2_text'  => 'Organisera varje produkt med tydlighet och precision. AllStockControl gör det enkelt att registrera in- och utleveranser samt lagerförflyttningar i realtid.',

		'desc_3_title' => 'Kontrollera utleveranser och leveranser utan fel',
		'desc_3_text'  => 'Registrera varje varuutgång innan leverans. AllStockControl hjälper dig att undvika fel i order, hålla lagret uppdaterat och säkerställa korrekta leveranser.',

		'desc_4_title' => 'Fatta trygga beslut med full lagerkontroll',
		'desc_4_text'  => 'Med uppdaterad lagerinformation i realtid ger AllStockControl dig trygghet i beslutsfattandet. Undvik fel, minska förluster och behåll full kontroll.',

		// features section
		'features_title' => 'Funktioner för lagerhantering',

		'feature_plan_title' => 'Skalbara planer för växande team',
		'feature_plan_desc'  => 'Välj lagerhanteringsplaner baserat på antal användare, filialer och produkter. Systemet växer i takt med ditt företag.',

		'feature_multibranch_title' => 'Lager per filial eller lagerplats',
		'feature_multibranch_desc'  => 'Hantera och separera lagersaldo per plats och håll korrekta lagernivåer i varje filial i realtid.',

		'feature_roles_title' => 'Användarroller och behörigheter',
		'feature_roles_desc'  => 'Tilldela roller och behörigheter till administratörer och teammedlemmar för säker, strukturerad och kontrollerad lagerhantering.',

		'feature_catalog_title' => 'Organiserad produktkatalog',
		'feature_catalog_desc'  => 'Organisera produkter med bilder, beskrivningar, kategorier och underkategorier för snabbare och tydligare lagerkontroll.',

		'feature_search_title' => 'Snabb sökning och smart filtrering',
		'feature_search_desc'  => 'Hitta produkter direkt med snabb sökning och smarta filter och spara tid i det dagliga arbetet.',

		'feature_stock_title' => 'Lagertransaktioner i realtid',
		'feature_stock_desc'  => 'Registrera inleveranser, utleveranser och justeringar i realtid för att alltid ha korrekta lagernivåer.',

		'feature_transfers_title' => 'Överföringar mellan filialer',
		'feature_transfers_desc'  => 'Flytta lager mellan platser med full spårbarhet, utan borttappade varor eller oklarheter.',

		'feature_min_stock_title' => 'Miniminivåer och lagerlarm',
		'feature_min_stock_desc'  => 'Sätt miniminivåer och få varningar innan varor tar slut, för att minska förluster och förseningar.',

		'feature_cloud_title' => 'Molnbaserat lagerhanteringssystem',
		'feature_cloud_desc'  => 'Kom åt ditt lager var som helst via ett säkert molnbaserat system, från dator, surfplatta eller mobil.',

		'feature_responsive_title' => 'Responsivt och lättanvänt gränssnitt',
		'feature_responsive_desc'  => 'Ett snabbt, tydligt och mobilvänligt gränssnitt anpassat för både desktop och mobil, enkelt för hela teamet.',
	
		// pricing section
		'pricing_title_main' => 'Flexibla lagerhanteringsplaner för småföretag',
		'pricing_subtitle'   => 'AllStockControl hjälper småföretag att hantera sitt lager på ett enkelt, tydligt och effektivt sätt. Följ lagertransaktioner, organisera produkter och skapa rapporter var du än är. Välj ett flexibelt månadsabonnemang utan bindningstid.',
		'pricing_employees_title' => 'Välj antal anställda',
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