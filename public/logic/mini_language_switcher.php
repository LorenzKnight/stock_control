<?php
// Idiomas soportados y selección por query (?lang=en|es|sv)
$supported = ['en','es','sv'];

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';

$uri = trim($path, '/');
$pathParts = explode('/', $uri);

$uriLang = $pathParts[0] ?? '';

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
		'description' => 'Cloud-based inventory management software for small businesses. Track stock in real time, manage multiple locations, and control inventory movements from any device. Try AllStockControl free.',
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
		'home_main_subtitle' => 'Manage stock, inventory movements, and product availability in real time without spreadsheets or complex systems.',
		'home_cta'			 => 'Try inventory software for free',
		'cta_note'			 => 'No credit card required · Cancel anytime',
		'signup_message'	 => 'Create your free account and start managing your company inventory easily and securely.',

		// catch container
		'excel_h2' => 'Still managing inventory with spreadsheets?',
		'excel_p1' => 'Spreadsheets may work at the beginning, but as your business grows, they often lead to outdated stock numbers, manual errors, and wasted time.',
		'excel_p2' => 'AllStockControl helps small businesses move from Excel and manual tracking to simple, real-time inventory control from any device.',
		'excel_li_1' => 'Know your real stock levels in real time',
		'excel_li_2' => 'Reduce mistakes in stock entries and exits',
		'excel_li_3' => 'Keep inventory organized across users and locations',
		'excel_cta' => 'Start with a simpler inventory system',

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

		// login form
		'login_title'       => 'Start session here',
		'login_email_ph'    => 'Enter your email address',
		'login_email_title' => 'Enter a valid email',
		'login_password_ph' => 'Enter your password',
		'login_submit'      => 'Log in',

		// signup form
		'signup_title'        => 'Create your free account',
		'signup_subtitle'     => 'Start controlling your inventory in minutes',

		'signup_name_label'   => 'Name',
		'signup_name_ph'      => 'Enter your name',
		'signup_name_title'   => 'Enter a valid name',

		'signup_surname_label'=> 'Surname',
		'signup_surname_ph'   => 'Enter your surname',
		'signup_surname_title'=> 'Enter a valid surname',

		'signup_email_label'  => 'Email',
		'signup_email_ph'     => 'Enter your email address',
		'signup_email_title'  => 'Enter a valid email',

		'signup_password_label' => 'Password',
		'signup_password_ph'    => 'Create a secure password',

		'signup_repeat_label' => 'Repeat password',
		'signup_repeat_ph'    => 'Repeat your password',

		'signup_submit'       => 'Create account',
		'signup_have_account' => 'Already have an account?',

		// Pricing features
		'pricing_contact'      => 'Contact us for a custom plan',
		'pricing_per_month'    => 'per month and employees',
		'pricing_includes'     => 'Includes:',
		'pricing_max_members'  => 'Max users',
		'pricing_max_admins'   => 'Max admins',
		'pricing_max_branches' => 'Max branches',
		'pricing_max_products' => 'Max products',
		'pricing_as_agreed'    => 'As agreed',
		'pricing_shipping'     => 'Shipment tracking service',
		'pricing_priority'     => 'Priority support',

		// footer
		'footer_rights'        => 'All rights reserved.',
		'footer_contact_title' => 'Contact us',
		'footer_name'          => 'Your name',
		'footer_email'         => 'Your email',
		'footer_message'       => 'Your message',
		'footer_send'          => 'Send',

		// Privacy & GDPR
		'gdpr_title' 		  => 'GDPR-policy',
		'terms_title' 		  => 'Terms of Use',

		// signup checks
		'signup_terms_prefix' => 'I accept the',
		'signup_terms_link'   => 'terms and conditions',
		'signup_terms_suffix' => 'of use of AllStockControl',
		'signup_privacy_text' => 'for personal data processing. GDPR (EU) and applicable laws apply.',

		// footer seo links
		'footer_seo_title'        => 'Inventory Management Solutions',
		// 'footer_seo_inventory'   => 'Inventory management software',
		'footer_seo_smallbiz'    => 'Inventory software for small businesses',
		'footer_seo_cloud'       => 'Cloud-based inventory system',
		'footer_seo_multilocation'=> 'Multi-location stock control',
		'footer_seo_pricing'     => 'Inventory software pricing',

		// IN LOGs page
		// Header:
		'header_products' => 'Products',
		'header_storage' => 'Storage',
		'header_customers' => 'Customers',
		'header_shipping' => 'Shipping',
		'header_sales' => 'Sales',
		'header_payments' => 'Payments',
		'header_Reports' => 'Reports',
		'header_settings' => 'Settings',
		'header_system_admin' => 'System Administration',
		'header_logout' => 'Log out',

		// profile page
		'profile_greeting' => 'Hello, ',
		'user_fallback_name' => 'User',

		// info box
		'welcome_title' => 'Welcome to',
		'welcome_desc' => 'You now have full access to our stock management platform, giving you complete control over inventory tracking and optimization. Where will efficiency take your business today?',

		//small boxes
		'smallbox_my_info' => 'My info',
		'smallbox_selected_pack' => 'Selected Pack',
		'smallbox_company_data' => 'Company data',
		'smallbox_spot' => 'Spot',

		'smallbox_members' => 'Members',
		'smallbox_branches' => 'Branches',
		'smallbox_products_limit' => 'Products limit',

		'smallbox_name' => 'Name',

		//small box buttons
		'edit_my_data' => 'Update info',
		'subscription' => 'Subscription',
		'add_members' => 'Add members',
		'manage' => 'Manage',

		'user_list' => 'User List',

		// Global
		'phone' => 'Phone',
	],
	'es' => [
		'title'       => 'Sistema de control de stock e inventario para pequeñas empresas | AllStockControl',
		'description' => 'Sistema de control de stock e inventario en la nube para pequeñas empresas. Controla stock en tiempo real, entradas, salidas y sucursales desde cualquier dispositivo. Pruébalo gratis.',
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
		'home_main_h1'		 => 'Sistema de control de stock e inventario para pequeñas empresas',
		'home_main_subtitle' => 'Gestiona tu stock e inventario en tiempo real, sin Excel y sin sistemas complicados. Controla entradas, salidas y movimientos desde cualquier dispositivo.',
		'home_cta'			 => 'Probar el software de inventario gratis',
		'cta_note'			 => 'No se requiere tarjeta de crédito · Cancela cuando quieras',
		'signup_message'	 => 'Crea tu cuenta gratis y empieza a controlar el inventario de tu empresa de forma simple y segura.',
	
		// catch container
		'excel_h2' => '¿Sigues controlando tu inventario con Excel?',
		'excel_p1' => 'Las hojas de cálculo pueden funcionar al principio, pero a medida que tu empresa crece suelen generar stock desactualizado, errores manuales y pérdida de tiempo.',
		'excel_p2' => 'AllStockControl ayuda a pequeñas empresas a pasar de Excel y del control manual a un sistema de inventario simple y en tiempo real, accesible desde cualquier dispositivo.',
		'excel_li_1' => 'Conoce tu stock real en tiempo real',
		'excel_li_2' => 'Reduce errores en entradas y salidas de inventario',
		'excel_li_3' => 'Mantén el control del inventario entre usuarios y sucursales',
		'excel_cta' => 'Empieza con un sistema de inventario más simple',

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

		// login form
		'login_title'       => 'Inicia sesión aquí',
		'login_email_ph'    => 'Introduce tu correo electrónico',
		'login_email_title' => 'Introduce un correo válido',
		'login_password_ph' => 'Introduce tu contraseña',
		'login_submit'      => 'Iniciar sesión',

		// signup form
		'signup_title'        => 'Crea tu cuenta gratis',
		'signup_subtitle'     => 'Empieza a controlar tu inventario en minutos',

		'signup_name_label'   => 'Nombre',
		'signup_name_ph'      => 'Introduce tu nombre',
		'signup_name_title'   => 'Introduce un nombre válido',

		'signup_surname_label'=> 'Apellido',
		'signup_surname_ph'   => 'Introduce tu apellido',
		'signup_surname_title'=> 'Introduce un apellido válido',

		'signup_email_label'  => 'Correo electrónico',
		'signup_email_ph'     => 'Introduce tu correo electrónico',
		'signup_email_title'  => 'Introduce un correo válido',

		'signup_password_label' => 'Contraseña',
		'signup_password_ph'    => 'Crea una contraseña segura',

		'signup_repeat_label' => 'Repetir contraseña',
		'signup_repeat_ph'    => 'Repite tu contraseña',

		'signup_submit'       => 'Crear cuenta',
		'signup_have_account' => '¿Ya tienes una cuenta?',

		// Pricing features
		'pricing_contact'        => 'Contáctanos para un plan a medida',
		'pricing_per_month'      => 'por mes y empleados',
		'pricing_includes'       => 'Incluye:',
		'pricing_max_members'    => 'Máx. usuarios',
		'pricing_max_admins'     => 'Máx. administradores',
		'pricing_max_branches'   => 'Máx. sucursales',
		'pricing_max_products'   => 'Máx. productos',
		'pricing_as_agreed'      => 'Según acuerdo',
		'pricing_shipping'       => 'Servicio de seguimiento de envíos',
		'pricing_priority'       => 'Soporte prioritario',

		// footer
		'footer_rights'        => 'Todos los derechos reservados.',
		'footer_contact_title' => 'Contáctanos',
		'footer_name'          => 'Tu nombre',
		'footer_email'         => 'Tu correo electrónico',
		'footer_message'       => 'Tu mensaje',
		'footer_send'          => 'Enviar',

		// Privacy & GDPR
		'gdpr_title' 		  => 'Política del RGPD / DPA',
		'terms_title' 		  => 'Términos de uso',

		// signup checks
		'signup_terms_prefix' => 'Acepto las',
		'signup_terms_link'   => 'condiciones y términos',
		'signup_terms_suffix' => 'de uso de AllStockControl',
		'signup_privacy_text' => 'para el tratamiento de datos personales. Se aplica el RGPD (UE) y las leyes aplicables.',

		// footer seo links
		'footer_seo_title'        => 'Soluciones de control de inventario',
		// 'footer_seo_inventory'   => 'Software de control de inventario',
		'footer_seo_smallbiz'    => 'Software de inventario para pequeñas empresas',
		'footer_seo_cloud'       => 'Sistema de inventario en la nube',
		'footer_seo_multilocation'=> 'Control de stock multi-sucursal',
		'footer_seo_pricing'     => 'Precios del software de inventario',

		// IN LOGs page
		// Header:
		'header_products' => 'Productos',
		'header_storage' => 'Almacenamiento',
		'header_customers' => 'Clientes',
		'header_shipping' => 'Envíos',
		'header_sales' => 'Ventas',
		'header_payments' => 'Pagos',
		'header_Reports' => 'Informes',
		'header_settings' => 'Configuración',
		'header_system_admin' => 'Administración del sistema',
		'header_logout' => 'Cerrar sesión',

		// profile page
		'profile_greeting' => 'Hola, ',
		'user_fallback_name' => 'Usuario',

		// info box
		'welcome_title' => 'Bienvenido a',
		'welcome_desc' => 'Ahora tienes acceso completo a nuestra plataforma de control de inventario, dándote el control total sobre el seguimiento y la optimización de tu stock. ¿A dónde llevará la eficiencia tu negocio hoy?',

		//small boxes
		'smallbox_my_info' => 'Mi información',
		'smallbox_selected_pack' => 'Pack seleccionado',
		'smallbox_company_data' => 'Datos de la empresa',
		'smallbox_spot' => 'Spot',

		'smallbox_members' => 'Miembros',
		'smallbox_branches' => 'Sucursales',
		'smallbox_products_limit' => 'Límite de productos',

		'smallbox_name' => 'Nombre',

		//small box buttons
		'edit_my_data' => 'Actualizar información',
		'subscription' => 'Suscripción',
		'add_members' => 'Agregar miembros',
		'manage' => 'Gestionar',

		'user_list' => 'Lista de usuarios',

		// Global
		'phone' => 'Teléfono',
	],
	'sv' => [
		'title'       => 'Lagerprogram och lagerkontroll för småföretag | AllStockControl',
		'description' => 'Molnbaserat lagerprogram för småföretag. Följ lagersaldo i realtid, hantera flera platser och få bättre lagerkontroll utan kalkylblad. Testa gratis.',
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
		'home_main_h1'		 => 'Lagerprogram och lagerkontroll för småföretag',
		'home_main_subtitle' => 'Hantera lager, lagersaldo och lagertransaktioner i realtid utan kalkylblad eller krångliga system. Full kontroll från valfri enhet.',
		'home_cta'			 => 'Testa lagerhanteringssystemet gratis',
		'cta_note'			 => 'Inget kreditkort krävs · Avsluta när som helst',
		'signup_message'	 => 'Skapa ett gratis konto och börja kontrollera företagets lager enkelt och säkert.',

		// catch container
		'excel_h2' => 'Använder du fortfarande Excel för att hålla koll på lagret?',
		'excel_p1' => 'Kalkylblad kan fungera i början, men när företaget växer leder de ofta till felaktigt lagersaldo, manuella misstag och onödigt extraarbete.',
		'excel_p2' => 'AllStockControl hjälper småföretag att gå från Excel och manuell hantering till ett enkelt lagerhanteringssystem i realtid, tillgängligt från valfri enhet.',
		'excel_li_1' => 'Se ditt verkliga lagersaldo i realtid',
		'excel_li_2' => 'Minska fel i in- och utleveranser',
		'excel_li_3' => 'Behåll kontrollen över lagret mellan användare och platser',
		'excel_cta' => 'Börja med ett enklare lagerhanteringssystem',

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

		// login form
		'login_title'       => 'Logga in här',
		'login_email_ph'    => 'Ange din e-postadress',
		'login_email_title' => 'Ange en giltig e-post',
		'login_password_ph' => 'Ange ditt lösenord',
		'login_submit'      => 'Logga in',

		// signup form
		'signup_title'        => 'Skapa ett gratis konto',
		'signup_subtitle'     => 'Börja kontrollera ditt lager på några minuter',

		'signup_name_label'   => 'Förnamn',
		'signup_name_ph'      => 'Ange ditt namn',
		'signup_name_title'   => 'Ange ett giltigt namn',

		'signup_surname_label'=> 'Efternamn',
		'signup_surname_ph'   => 'Ange ditt efternamn',
		'signup_surname_title'=> 'Ange ett giltigt efternamn',

		'signup_email_label'  => 'E-post',
		'signup_email_ph'     => 'Ange din e-postadress',
		'signup_email_title'  => 'Ange en giltig e-post',

		'signup_password_label' => 'Lösenord',
		'signup_password_ph'    => 'Skapa ett säkert lösenord',

		'signup_repeat_label' => 'Upprepa lösenord',
		'signup_repeat_ph'    => 'Upprepa ditt lösenord',

		'signup_submit'       => 'Skapa konto',
		'signup_have_account' => 'Har du redan ett konto?',

		// Pricing features
		'pricing_contact'      => 'Kontakta oss för ett skräddarsytt paket',
		'pricing_per_month'    => 'per månad och anställda',
		'pricing_includes'     => 'Ingår:',
		'pricing_max_members'  => 'Max användare',
		'pricing_max_admins'   => 'Max administratörer',
		'pricing_max_branches' => 'Max filialer',
		'pricing_max_products' => 'Max produkter',
		'pricing_as_agreed'    => 'Enligt överenskommelse',
		'pricing_shipping'     => 'Spårning av leveranser',
		'pricing_priority'     => 'Prioriterad support',

		// footer
		'footer_rights'        => 'Alla rättigheter förbehållna.',
		'footer_contact_title' => 'Kontakta oss',
		'footer_name'          => 'Ditt namn',
		'footer_email'         => 'Din e-postadress',
		'footer_message'       => 'Ditt meddelande',
		'footer_send'          => 'Skicka',

		// Privacy & GDPR 
		'gdpr_title' 		  => 'Integritetspolicy / GDPR',
		'terms_title' 		  => 'Användarvillkor',

		// signup checks
		'signup_terms_prefix' => 'Jag accepterar',
		'signup_terms_link'   => 'användarvillkoren',
		'signup_terms_suffix' => 'för användning av AllStockControl',
		'signup_privacy_text' => 'för behandling av personuppgifter. GDPR (EU) och tillämpliga lagar gäller.',

		// footer seo links
		'footer_seo_title'        => 'Lagerhanteringslösningar',
		// 'footer_seo_inventory'   => 'Lagerhanteringssystem',
		'footer_seo_smallbiz'    => 'Lagerhantering för småföretag',
		'footer_seo_cloud'       => 'Molnbaserat lagerhanteringssystem',
		'footer_seo_multilocation'=> 'Lagerhantering för flera platser',
		'footer_seo_pricing'     => 'Priser för lagerhanteringssystem',

		// IN LOGs page
		// Header:
		'header_products' => 'Produkter',
		'header_storage' => 'Lager',
		'header_customers' => 'Kunder',
		'header_shipping' => 'Leveranser',
		'header_sales' => 'Försäljning',
		'header_payments' => 'Betalningar',
		'header_Reports' => 'Rapporter',
		'header_settings' => 'Inställningar',
		'header_system_admin' => 'Systemadministration',
		'header_logout' => 'Logga ut',

		// PROFILE PAGE
		'profile_greeting' => 'Hej, ',
		'user_fallback_name' => 'Användare',

		// info box
		'welcome_title' => 'Välkommen till',
		'welcome_desc' => 'Du har nu full tillgång till vår plattform för lagerhantering, vilket ger dig total kontroll över spårning och optimering av ditt lager. Vart tar effektiviteten ditt företag idag?',

		//small boxes
		'smallbox_my_info' => 'Min information',
		'smallbox_selected_pack' => 'Valt paket',
		'smallbox_company_data' => 'Företagsdata',
		'smallbox_spot' => 'Spot',

		'smallbox_members' => 'Medlemmar',
		'smallbox_branches' => 'Filialer',
		'smallbox_products_limit' => 'Produktgräns',

		'smallbox_name' => 'Namn',

		//small box buttons
		'edit_my_data' => 'Uppdatera information',
		'subscription' => 'Prenumeration',
		'add_members' => 'Lägg till medlemmar',
		'manage' => 'Hantera',

		'user_list' => 'Användarlista',

		// Global
		'phone' => 'Telefon',
	],
];

$t = $i18n[$lang] ?? $i18n['en'];

// Helper para construir URLs con ?lang=…
function url_with_lang(string $targetLang): string {
	$supported = ['en', 'es', 'sv'];

	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

	$rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
	$rawPath = is_string($rawPath) ? $rawPath : '/';

	$parts = explode('/', trim($rawPath, '/'));

	// Quitar idioma actual si existe
	if (in_array($parts[0] ?? '', $supported, true)) {
		array_shift($parts);
	}

	// Si la ruta viene como profile.php, convertirla en profile
	if (!empty($parts)) {
		$lastIndex = count($parts) - 1;
		$parts[$lastIndex] = preg_replace('/\.php$/', '', $parts[$lastIndex]);
	}

	$rest = implode('/', $parts);

	return $scheme . '://' . $host . '/' . $targetLang . ($rest !== '' ? '/' . $rest : '');
}

function localized_url(string $page, ?string $targetLang = null): string {
	global $lang;

	$supported = ['en', 'es', 'sv'];

	$targetLang = $targetLang ?? $lang;

	if (!in_array($targetLang, $supported, true)) {
		$targetLang = 'en';
	}

	$page = trim($page, '/');
	$page = preg_replace('/\.php$/', '', $page);

	return '/' . $targetLang . '/' . $page;
}
?>