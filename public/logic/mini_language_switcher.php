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
else if (isset($_GET['lang']) && in_array(strtolower($_GET['lang']), $supported, true)) {
    $lang = strtolower($_GET['lang']);
}
// 2️⃣ SI NO: detectar idioma del navegador
else {
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
    $browserLang = strtolower($browserLang);

	$lang = in_array($browserLang, $supported, true) ? $browserLang : 'en';
}

// Textos por idioma
$i18n = [
	'en' => [
		'title'       => 'Inventory management software for small businesses | AllStockControl',
		'meta_description' => 'Cloud-based inventory management software for small businesses. Track stock in real time, manage multiple locations, and control inventory movements from any device. Try AllStockControl free.',
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
		'excel_eyebrow' => 'Simple inventory management software',

		'excel_h2' => 'Still managing inventory with spreadsheets?',

		'excel_p1' => 'Spreadsheets can work for basic inventory tracking, but as your business grows, they often lead to outdated stock levels, manual errors, and time-consuming inventory updates.',

		'excel_p2' => 'AllStockControl is a simple inventory management system for small businesses that helps you replace Excel and manual stock tracking with real-time inventory control from any device.',

		'excel_cta' => 'Start managing your inventory',

		'cta_no_card' => 'No credit card required',
		'cta_cancel_anytime' => 'Cancel anytime',

		'excel_before_title' => 'Inventory with Excel',

		'excel_problem_1_title' => 'Outdated stock levels',
		'excel_problem_1_desc' => 'Spreadsheet inventory can quickly become outdated when stock moves throughout the day.',

		'excel_problem_2_title' => 'Manual inventory errors',
		'excel_problem_2_desc' => 'Manual stock entries and exits increase the risk of inventory errors.',

		'excel_problem_3_title' => 'Hard to scale',
		'excel_problem_3_desc' => 'Spreadsheets become harder to manage as your products, users, and locations grow.',

		'excel_li_1' => 'Know your real stock levels in real time',
		'excel_solution_1_desc' => 'Keep inventory levels accurate and updated as stock moves.',

		'excel_li_2' => 'Track stock entries and exits',
		'excel_solution_2_desc' => 'Track inventory movements and reduce manual stock errors.',

		'excel_li_3' => 'Manage inventory across users and locations',
		'excel_solution_3_desc' => 'Keep inventory organized across multiple users and business locations.',

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
		'smallbox_products_limit' => 'Products limit',

		'smallbox_name' => 'Name',

		//small box buttons
		'edit_my_data' => 'Update info',
		'subscription' => 'Subscription',
		'add_members' => 'Add members',
		'manage_companies' => 'Manage companies',
		'complete_company_profile' => 'Complete company profile',

		// content titles
		'user_list' => 'User List',
		'products_list' => 'Products List',
		'storage_list' => 'Storage List',
		'customers_list' => 'Customers List',
		'sales_list' => 'Sales List',
		'payments_list' => 'Payments List',
		'notifications' => 'Notifications',

		// Forms
		'edit_profile_title' => 'Edit my profile',
		'upgrade_your_plan' => 'Upgrade your plan',
		'select_a_subscription_pack' => 'Select a subscription pack',
		'add_edit_company_title' => 'Add or Edit Company or Affiliate',
		'add_member_title' => 'Add a new member to your company',
		'edit_member_title' => 'Edit member information',
		'company_info' => 'Company info',
		'add_product_title' => 'Add a new product',
		'product_form_simple_intro' => 'Add a new product to your inventory. Fill in the required fields and click "Save" to add the product to your stock list.',
		'show_advanced_options' => 'Show advanced options',
		'hide_advanced_options' => 'Hide advanced options',
		'add_category_or_subcategory' => 'Add a new category or subcategory',
		'product_options' => 'Product options',
		'edit_product_title' => 'Edit Product',
		'delete_product_title' => 'Delete Product',
		'storage_options' => 'Storage options',
		'add_or_edit_slot' => 'Add or edit slot',
		'add_or_edit_storage' => 'Add or edit storage',
		'slot_options' => 'Slot options',
		'add_customer_title' => 'Add a new customer',
		'customer_data' => 'Customer data',
		'customer_reference' => 'Customer reference',
		'customer_options' => 'Customer options',
		'edit_customer_title' => 'Edit Customer',
		'delete_customer_title' => 'Delete Customer',
		'create_sale_title' => 'Create a new sale',
		'method_of_payment' => 'Method of payment',
		'sale_options' => 'Sale options',
		'edit_sale_title' => 'Edit Sale',
		'delete_sale_title' => 'Delete Sale',
		'crete_payment_title' => 'Create a new payment',
		'payment_options' => 'Payment options',

		'estimated_cost' => 'Estimated cost',
		'select_extra_service' => 'Select extra service',
		'confirm_delete_product' => 'Are you sure you want to delete this product?',
		'confirm_delete_customer' => 'Are you sure you want to delete this customer?',
		'confirm_delete_sale' => 'Are you sure you want to delete this sale and all associated data?',
		'confirm_delete_payment' => 'Are you sure you want to delete this payment?',

		'form_drop_image' => 'Drop image here or click to select',
		'form_name' => 'Name',
		'form_surname' => 'Surname',
		'form_birthday' => 'Birthdate',
		'form_country_code' => 'Country Code',
		'form_user_role' => 'User Role',
		'select_document_type' => 'Select document type',

		'company_name' => 'Company Name',
		'organization_no' => 'Organization No.',
		'company_address' => 'Company Address',
		'company_country_code' => 'Country Code',
		'company_phone' => 'Company Phone',

		'slot_name' => 'Slot name',
		'current_capacity' => 'Current capacity',
		'max_capacity' => 'Max capacity',

		'customer_type' => 'Customer type',
		'document_type' => 'Document type',
		'document_no' => 'Document No.',
		'references_1' => 'Reference 1',
		'references_1_phone' => 'Reference 1 Phone',
		'references_2' => 'Reference 2',
		'references_2_phone' => 'Reference 2 Phone',

		'person_who_pays' => 'Person who pays',

		'product_search' => 'Search products',
		'storage_or_product_no' => 'Storage/Product No.',
		'search_slot' => 'Search slot',
		'search_customer' => 'Search customer',
		'search_sale' => 'Search sale',
		'search_payment' => 'Search payment',
		'search_messages' => 'Search Messages',
		'select_a_notification' => 'Select a notification',

		'enter_name_or_document' => 'Enter name or document no.',
		'enter_product_name' => 'Enter product name',
		'mark_category' => 'Mark / Category',
		'model' => 'Model',
		'sub_model' => 'Sub/Model',
		'message' => 'Message',

		// Onboarding Guide
		'get_your_inventory_ready' => 'Get your inventory ready',

		'track_your_progress' => 'Track your progress',
		'company_setup' => 'Complete your company profile',
		'create_first_product' => 'Create your first product',
		'add_first_customer' => 'Add your first customer',
		'record_your_first_sale' => 'Record your first sale',

		'first_company_reward_eyebrow' => 'Company profile completed.',
		'first_company_reward_title' => 'Your company is ready!',
		'first_company_reward_description' => 'Great! Your company profile is now set up. You\'re one step closer to getting your inventory ready.',

		'track_desc' => 'Here you can see the steps needed to set up your inventory.',
		'first_product_desc' => 'Start by adding a product to your inventory. It takes less than a minute.',
		'first_customer_desc' => 'Create your first customer profile to manage and track your sales more easily.',
		'first_sale_desc' => 'Record your first sale and see how your inventory updates in real time.',

		'first_client_reward_eyebrow' => 'First customer added.',
		'first_client_reward_title' => 'Your first customer is ready!',
		'first_client_reward_description' => 'Great! You\'ve added your first customer. You can now use their profile when recording sales.',
		'view_my_customer' => 'View my customer',
		'create_another_customer' => 'Create another customer',

		'first_sale_reward_eyebrow' => 'First sale recorded.',
		'first_sale_reward_title' => 'You made your first sale!',
		'first_sale_reward_description' => 'Great! You\'ve recorded your first sale and your inventory has been updated. Your setup is now complete.',
		'view_my_sale' => 'View my sale',
		'go_to_dashboard' => 'Go to dashboard',

		'next' => 'Next',
		'back' => 'Back',
		'done' => 'Done',

		// Onboarding Guide - Tooltips
		'welcome_to_allstockcontrol' => 'Welcome to AllStockControl!',
		'start_your_inventory' => 'Start your inventory journey',
		'welcome_first_product_description' => 'Let\'s get started by creating your first product. This will help you understand how to manage your inventory effectively.',
		'create_first_product_help' => 'Click here to create your first product and start managing your inventory.',
		'create_my_first_product' => 'Create my first product',
		'explore_dashboard_first' => 'Explore the dashboard first',

		'first_product_reward_eyebrow' => 'First product created.',
		'first_product_reward_title' => 'Your first milestone is complete!',
		'first_product_reward_description' => 'Great job! You\'ve completed your first inventory milestone. Here\'s a small reward to celebrate.',
		'view_my_product' => 'View my product',
		'create_another_product' => 'Create another product',

		// Payment
		'non_payment_message' => 'This subscription is currently inactive for non-payment!',
		'non_payment_message_reactivate' => 'Your subscription has been deactivated due to non-payment. If you wish to continue using the AllStockControl stock control system, please check payment details and reactivate your subscription.',

		'your_free_trial_has_expired' => 'Your 30-day trial package has expired!',
		'trial_expired_desc' => 'Your 30-day trial package has expired. If you wish to continue using the AllStockControl stock control system, please choose one of our subscription packages that best suits your needs.',
		'trial_expired_warning' => 'If you do not upgrade your package, all data saved up to this point will be deleted within 14 days.',

		// Products
		'single_unit' => 'Single unit',
		'multi_pack' => 'Multi-pack',
		'product_name' => 'Product name',
		'hs_code' => 'Tariff fraction (HS Code)',
		'type' => 'Type',
		'qty' => 'Qty.',
		'stock' => 'Stock',
		'export' => 'Export',
		'units' => 'Units',
		'weight_unit' => 'weight / unit',
		'total_weight' => 'Total weight',
		'year' => 'Year',
		'currency' => 'Currency',
		'price' => 'Price',
		'purpose' => 'Purpose',

		'uncategorized' => 'Uncategorized',
		'no_model' => 'No model assigned',
		'no_submodel' => 'No sub/model assigned',

		// Buttons
		'cancel' => 'Cancel',
		'update' => 'Update',
		'upgrade' => 'Upgrade',
		'create_affiliate' => 'Create Affiliate',
		'ok' => 'Ok',
		'confirm' => 'Confirm',
		'create' => 'Create',
		'delete_account' => 'Delete Account',
		'create_product' => 'Create Product',
		'create_category' => 'Create Category',
		'new_mark' => 'New Mark',
		'new_model' => 'New Model',
		'new_submodel' => 'New Sub/Model',
		'request_product' => 'Request product',
		'edit_product' => 'Edit Product',
		'delete_product' => 'Delete Product',
		'storage_menu' => 'Storage Menu',
		'manage_slots' => 'Manage Slots',
		'manage_storage' => 'Manage Storage',
		'create_slot' => 'Create Slot',
		'add_storage' => 'Add Storage',
		'select_slot' => 'Select a slot',
		'save_changes' => 'Save changes',
		'delete_slot' => 'Delete Slot',
		'create_customer' => 'Create Customer',
		'edit_customer' => 'Edit Customer',
		'delete_customer' => 'Delete Customer',
		'create_sale' => 'Create Sale',
		'reactivate_subscription' => 'Reactivate Subscription',
		'upgrade_package' => 'Upgrade your Package',
		'edit_sale' => 'Edit Sale',
		'more_information' => 'More Information',
		'delete_sale' => 'Delete Sale',
		'make_payment' => 'Make a Payment',
		'edit_payment' => 'Edit Payment',
		'delete_payment' => 'Delete Payment',
		'send' => 'Send',

		// Global arrays
		'package_descriptions' => [
			'try_pack' => 'Try our inventory management software for free for 30 days. No credit card required.',
			'starter_pack' => 'Ideal for freelancers and very small businesses starting with inventory control.',
			'basic_pack' => 'Perfect for small shops that need better control of products and stock movements.',
			'business_pack' => 'Designed for growing businesses managing inventory across multiple locations.',
			'growth_pack' => 'For businesses scaling operations with multiple locations and teams.',
			'scale_pack' => 'Built for large operations that require performance, control, and scalability.',
			'enterprise_pack' => 'Custom inventory solution for large organizations. Contact us for a tailored plan.'
		],

		'document_types' => [
			'national_id_cedula' => 'National ID / Cedula',
			'passport' => 'Passport',
			'driver_license' => 'Driver License',
			'social_security_card' => 'Social Security Card',
			'tax_id' => 'Tax ID',
			'other' => 'Other',
		],

		'general_status' => [
			'active' => 'Active',
			'inactive' => 'Inactive'
		],

		'customer_types' => [
			'regular' => 'Regular',
			'vip' => 'VIP',
			'wholesale' => 'Wholesale',
			'retail' => 'Retail',
			'online' => 'Online',
			'other' => 'Other',
		],

		// messages
		'no_marks_found' => 'No marks found',
		'no_models_found' => 'No models found for this brand',
		'no_submodels_found' => 'No sub/models found for this model',
		'no_results_yet' => 'No results yet',
		'product_found' => 'Product found',
		'no_product_found' => 'No product found',
		'no_slots_found' => 'No slots found.',
		'slot_info' => 'Slot info',

		// Global
		'company' => 'Company',
		'product' => 'Product',
		'customer' => 'Customer',
		'order' => 'Order',
		'phone' => 'Phone',
		'loading' => 'Loading...',
		'branches' => 'Branches',
		'memmbers' => 'Memmbers',
		'description' => 'Description',
		'address' => 'Address',
		'birthdate' => 'Birthdate',
		'quantity' => 'Quantity',
		'min_quantity' => 'Min quantity',
		'password' => 'Password',
		'status' => 'Status',
		'close' => 'Close',
		'continue' => 'Continue',

		'initial' => 'Initial',
		'delivery_date' => 'Delivery date',
		'remaining' => 'Remaining',
		'interest' => 'Interest',
		'installments_month' => 'Inst./month',
		'payment_date' => 'Payment date',
		'due' => 'Due',
		'price_sum' => 'Price sum',
		'total_interest' => 'Total interest',
		'percent' => 'Percent',
		'amount' => 'Amount',
		'payment_no' => 'Payment No.',

		'from' => 'From',
		'to' => 'To',
		'date' => 'Date',

		'handled' => 'Handled',
	],
	'es' => [
		'title'       => 'Sistema de control de stock e inventario para pequeñas empresas | AllStockControl',
		'meta_description' => 'Sistema de control de stock e inventario en la nube para pequeñas empresas. Controla stock en tiempo real, entradas, salidas y sucursales desde cualquier dispositivo. Pruébalo gratis.',
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
		'excel_eyebrow' => 'Software de gestión de inventario',

		'excel_h2' => '¿Sigues controlando tu inventario con Excel?',

		'excel_p1' => 'Las hojas de cálculo pueden funcionar para un control de inventario básico, pero a medida que tu empresa crece suelen generar niveles de stock desactualizados, errores manuales y más tiempo dedicado a actualizar el inventario.',

		'excel_p2' => 'AllStockControl es un sistema de gestión de inventario para pequeñas empresas que te ayuda a sustituir Excel y el control manual por un inventario en tiempo real, accesible desde cualquier dispositivo.',

		'excel_cta' => 'Empieza a gestionar tu inventario',

		'cta_no_card' => 'Sin tarjeta de crédito',
		'cta_cancel_anytime' => 'Cancela cuando quieras',

		'excel_before_title' => 'Inventario con Excel',

		'excel_problem_1_title' => 'Stock desactualizado',
		'excel_problem_1_desc' => 'El inventario en hojas de cálculo puede quedar desactualizado rápidamente cuando se producen movimientos de stock.',

		'excel_problem_2_title' => 'Errores manuales de inventario',
		'excel_problem_2_desc' => 'Registrar manualmente las entradas y salidas de stock aumenta el riesgo de errores.',

		'excel_problem_3_title' => 'Difícil de escalar',
		'excel_problem_3_desc' => 'Las hojas de cálculo se vuelven más difíciles de gestionar cuando aumentan tus productos, usuarios y sucursales.',

		'excel_li_1' => 'Conoce tu stock real en tiempo real',
		'excel_solution_1_desc' => 'Mantén los niveles de inventario actualizados y precisos a medida que se mueve el stock.',

		'excel_li_2' => 'Controla entradas y salidas de stock',
		'excel_solution_2_desc' => 'Registra los movimientos de inventario y reduce los errores manuales.',

		'excel_li_3' => 'Gestiona el inventario entre usuarios y sucursales',
		'excel_solution_3_desc' => 'Mantén organizado el inventario entre múltiples usuarios y ubicaciones.',


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
		'smallbox_products_limit' => 'Límite de prod',

		'smallbox_name' => 'Nombre',

		//small box buttons
		'edit_my_data' => 'Actualizar información',
		'subscription' => 'Suscripción',
		'add_members' => 'Agregar miembros',
		'manage_companies' => 'Administrar empresas',
		'complete_company_profile' => 'Completar perfil de empresa',

		// content titles
		'user_list' => 'Lista de usuarios',
		'products_list' => 'Lista de productos',
		'storage_list' => 'Lista de almacenamiento',
		'customers_list' => 'Lista de clientes',
		'sales_list' => 'Lista de ventas',
		'payments_list' => 'Lista de pagos',
		'notifications' => 'Notificaciones',

		// Forms
		'edit_profile_title' => 'Editar mi perfil',
		'upgrade_your_plan' => 'Actualizar tu plan',
		'select_a_subscription_pack' => 'Selecciona un pack de suscripción',
		'add_edit_company_title' => 'Agregar o editar empresa o afiliado',
		'add_member_title' => 'Agregar un nuevo miembro a tu empresa',
		'edit_member_title' => 'Editar información del miembro',
		'company_info' => 'Información de la empresa',
		'add_product_title' => 'Agregar un nuevo producto',
		'product_form_simple_intro' => 'Agrega un nuevo producto a tu inventario. Completa los campos requeridos y haz clic en "Guardar" para agregar el producto a tu lista de stock.',
		'show_advanced_options' => 'Mostrar opciones avanzadas',
		'hide_advanced_options' => 'Ocultar opciones avanzadas',
		'add_category_or_subcategory' => 'Agregar una nueva categoría o subcategoría',
		'product_options' => 'Opciones del producto',
		'edit_product_title' => 'Editar producto',
		'delete_product_title' => 'Eliminar producto',
		'storage_options' => 'Opciones de almacenamiento',
		'add_or_edit_slot' => 'Agregar o editar slot',
		'add_or_edit_storage' => 'Agregar o editar almacenamiento',
		'slot_options' => 'Opciones del slot',
		'add_customer_title' => 'Agregar un nuevo cliente',
		'customer_data' => 'Datos del cliente',
		'customer_reference' => 'Referencia del cliente',
		'customer_options' => 'Opciones del cliente',
		'edit_customer_title' => 'Editar cliente',
		'delete_customer_title' => 'Eliminar cliente',
		'create_sale_title' => 'Crear una nueva venta',
		'method_of_payment' => 'Método de pago',
		'sale_options' => 'Opciones de la venta',
		'edit_sale_title' => 'Editar venta',
		'delete_sale_title' => 'Eliminar venta',
		'crete_payment_title' => 'Crear un nuevo pago',
		'payment_options' => 'Opciones en pago',

		'estimated_cost' => 'Costo estimado',
		'select_extra_service' => 'Seleccionar servicio adicional',
		'confirm_delete_product' => '¿Estás seguro de que quieres eliminar este producto?',
		'confirm_delete_customer' => '¿Estás seguro de que quieres eliminar este cliente?',
		'confirm_delete_sale' => '¿Estás seguro de que quieres eliminar esta venta y todos los datos asociados?',
		'confirm_delete_payment' => '¿Estás seguro de que quieres eliminar este pago?',

		'form_drop_image' => 'Suelta la imagen aquí o haz clic para seleccionar',
		'form_name' => 'Nombre',
		'form_surname' => 'Apellido',
		'form_birthday' => 'Fecha de nacimiento',
		'form_country_code' => 'Código de país',
		'form_user_role' => 'Rol del usuario',
		'select_document_type' => 'Selecciona un tipo de documento',

		'company_name' => 'Nombre de la empresa',
		'organization_no' => 'Número de organización',
		'company_address' => 'Dirección de la empresa',
		'company_country_code' => 'Código de país',
		'company_phone' => 'Teléfono de la empresa',

		'slot_name' => 'Nombre del slot',
		'current_capacity' => 'Capacidad actual',
		'max_capacity' => 'Capacidad máxima',

		'customer_type' => 'Tipo de cliente',
		'document_type' => 'Tipo de documento',
		'document_no' => 'Número de documento',
		'references_1' => 'Referencia 1',
		'references_1_phone' => 'Teléfono de referencia 1',
		'references_2' => 'Referencia 2',
		'references_2_phone' => 'Teléfono de referencia 2',

		'person_who_pays' => 'Persona que paga',

		'product_search' => 'Buscar productos',
		'storage_or_product_no' => 'Número de almacenamiento/producto',
		'search_slot' => 'Buscar slot',
		'search_customer' => 'Buscar cliente',
		'search_sale' => 'Buscar venta',
		'search_payment' => 'Buscar pago',
		'search_messages' => 'Buscar Mensages',
		'select_a_notification' => 'Seleccione una notificacion',

		'enter_name_or_document' => 'Ingresa nombre o número de documento',
		'enter_product_name' => 'Ingresa nombre del producto',
		'mark_category' => 'Marcar / Categoría',
		'model' => 'Modelo',
		'sub_model' => 'Sub/Modelo',
		'message' => 'Mensaje',

		// Onboarding Guide
		'get_your_inventory_ready' => 'Prepara tu inventario',

		'track_your_progress' => 'Sigue tu progreso',
		'company_setup' => 'Completa tu perfil de empresa',
		'create_first_product' => 'Crea tu primer producto',
		'add_first_customer' => 'Agrega tu primer cliente',
		'record_your_first_sale' => 'Registra tu primera venta',

		'first_company_reward_eyebrow' => 'Perfil de empresa completado.',
		'first_company_reward_title' => '¡Tu empresa está lista!',
		'first_company_reward_description' => '¡Excelente! El perfil de tu empresa ya está configurado. Estás un paso más cerca de tener tu inventario listo.',

		'track_desc' => 'Aquí puedes ver los pasos necesarios para configurar tu inventario.',
		'first_product_desc' => 'Comienza agregando un producto a tu inventario. Toma menos de un minuto.',
		'first_customer_desc' => 'Crea el perfil de tu primer cliente para gestionar y dar seguimiento a tus ventas más fácilmente.',
		'first_sale_desc' => 'Registra tu primera venta y observa cómo tu inventario se actualiza en tiempo real.',

		'first_client_reward_eyebrow' => 'Primer cliente agregado.',
		'first_client_reward_title' => '¡Tu primer cliente está listo!',
		'first_client_reward_description' => '¡Excelente! Has agregado tu primer cliente. Ahora puedes usar su perfil al registrar ventas.',
		'view_my_customer' => 'Ver mi cliente',
		'create_another_customer' => 'Crear otro cliente',

		'first_sale_reward_eyebrow' => 'Primera venta registrada.',
		'first_sale_reward_title' => '¡Realizaste tu primera venta!',
		'first_sale_reward_description' => '¡Excelente! Has registrado tu primera venta y tu inventario se ha actualizado. La configuración inicial ya está completa.',
		'view_my_sale' => 'Ver mi venta',
		'go_to_dashboard' => 'Ir al panel',

		'next' => 'Siguiente',
		'back' => 'Atrás',
		'done' => 'Hecho',

		// Onboarding Guide - Tooltips
		'welcome_to_allstockcontrol' => '¡Bienvenido a AllStockControl!',
		'start_your_inventory' => '¡Comienza tu viaje de inventario!',
		'welcome_first_product_description' => 'Comencemos creando tu primer producto. Esto te ayudará a entender cómo gestionar tu inventario de manera efectiva.',
		'create_first_product_help' => 'Haz clic aquí para crear tu primer producto y comenzar a gestionar tu inventario.',
		'create_my_first_product' => 'Crear mi primer producto',
		'explore_dashboard_first' => 'Explorar el tablero primero',

		'first_product_reward_eyebrow' => 'Primer producto creado.',
		'first_product_reward_title' => '¡Tu primer hito está completo!',
		'first_product_reward_description' => '¡Buen trabajo! Has completado tu primer hito de inventario. Aquí tienes una pequeña recompensa para celebrar.',
		'view_my_product' => 'Ver mi producto',
		'create_another_product' => 'Crear otro producto',

		// Payment
		'non_payment_message' => '¡Esta suscripción está actualmente inactiva por falta de pago!',
		'non_payment_message_reactivate' => 'Tu suscripción ha sido desactivada debido a la falta de pago. Si deseas continuar utilizando el sistema de control de stock de AllStockControl, por favor revisa los detalles de pago y reactiva tu suscripción.',

		'your_free_trial_has_expired' => '¡Tu paquete de prueba gratuita de 30 días ha expirado!',
		'trial_expired_desc' => 'Tu paquete de prueba gratuita de 30 días ha expirado. Si deseas continuar utilizando el sistema de control de stock de AllStockControl, por favor elige uno de nuestros paquetes de suscripción que mejor se adapte a tus necesidades.',
		'trial_expired_warning' => 'Si no actualizas tu paquete, todos los datos guardados hasta este punto serán eliminados dentro de 14 días.',

		// Products
		'single_unit' => 'Unidad simple',
		'multi_pack' => 'Multi-pack',
		'product_name' => 'Nombre del producto',
		'hs_code' => 'Fracción arancelaria (HS Code)',
		'type' => 'Tipo',
		'qty' => 'Cant.',
		'stock' => 'Stock',
		'export' => 'Exportar',
		'units' => 'Unidades',
		'weight_unit' => 'peso / unidad',
		'total_weight' => 'Peso total',
		'year' => 'Año',
		'currency' => 'Moneda',
		'price' => 'Precio',
		'purpose' => 'Propósito',

		'uncategorized' => 'Sin categoría',
		'no_model' => 'Sin modelo asignado',
		'no_submodel' => 'Sin sub/modelo asignado',

		// Buttons
		'cancel' => 'Cancelar',
		'update' => 'Actualizar',
		'upgrade' => 'Mejorar plan',
		'create_affiliate' => 'Crear afiliado',
		'ok' => 'Aceptar',
		'confirm' => 'Confirmar',
		'create' => 'Crear',
		'delete_account' => 'Eliminar cuenta',
		'create_product' => 'Crear producto',
		'create_category' => 'Crear categoría',
		'new_mark' => 'Nueva marca',
		'new_model' => 'Nuevo modelo',
		'new_submodel' => 'Nuevo sub/modelo',
		'request_product' => 'Solicitar producto',
		'edit_product' => 'Editar producto',
		'delete_product' => 'Eliminar producto',
		'storage_menu' => 'Menú de almacenamiento',
		'manage_slots' => 'Gestionar slots',
		'manage_storage' => 'Gestionar almacenamiento',
		'create_slot' => 'Crear slot',
		'add_storage' => 'Agregar almacenamiento',
		'select_slot' => 'Selecciona un slot',
		'save_changes' => 'Guardar cambios',
		'delete_slot' => 'Eliminar slot',
		'create_customer' => 'Crear cliente',
		'edit_customer' => 'Editar cliente',
		'delete_customer' => 'Eliminar cliente',
		'create_sale' => 'Crear venta',
		'reactivate_subscription' => 'Reactivar suscripción',
		'upgrade_package' => 'Actualizar tu paquete',
		'edit_sale' => 'Editar venta',
		'more_information' => 'Más información',
		'delete_sale' => 'Eliminar venta',
		'make_payment' => 'Realizar un pago',
		'edit_payment' => 'Editar pago',
		'delete_payment' => 'Eliminar pago',
		'send' => 'Enviar',

		// Global arrays
		'package_descriptions' => [
			'try_pack' => 'Prueba nuestro software de gestión de inventario gratis por 30 días. No se requiere tarjeta de crédito.',
			'starter_pack' => 'Ideal para freelancers y negocios muy pequeños que comienzan con el control de inventario.',
			'basic_pack' => 'Perfecto para pequeñas tiendas que necesitan un mejor control de productos y movimientos de stock.',
			'business_pack' => 'Diseñado para negocios en crecimiento que gestionan inventario en múltiples ubicaciones.',
			'growth_pack' => 'Para negocios que escalan operaciones con múltiples ubicaciones y equipos.',
			'scale_pack' => 'Construido para operaciones grandes que requieren rendimiento, control y escalabilidad.',
			'enterprise_pack' => 'Solución de inventario personalizada para grandes organizaciones. Contáctanos para un plan a medida.'
		],

		'document_types' => [
			'national_id_cedula' => 'Identificación nacional / Cédula',
			'passport' => 'Pasaporte',
			'driver_license' => 'Licencia de conducir',
			'social_security_card' => 'Tarjeta de seguridad social',
			'tax_id' => 'Identificación fiscal',
			'other' => 'Otro',
		],

		'general_status' => [
			'active' => 'Activo',
			'inactive' => 'Inactivo'
		],

		'customer_types' => [
			'regular' => 'Regular',
			'vip' => 'VIP',
			'wholesale' => 'Mayorista',
			'retail' => 'Minorista',
			'online' => 'En línea',
			'other' => 'Otro',
		],

		// messages
		'no_marks_found' => 'No se encontraron marcas',
		'no_models_found' => 'No se encontraron modelos para esta marca',
		'no_submodels_found' => 'No se encontraron sub/modelos para este modelo',
		'no_results_yet' => 'Aún no hay resultados',
		'product_found' => 'Producto encontrado',
		'no_product_found' => 'No se encontró ningún producto',
		'no_slots_found' => 'No se encontraron slots.',
		'slot_info' => 'Información del slot',

		// Global
		'company' => 'Compañia',
		'product' => 'Producto',
		'customer' => 'Cliente',
		'order' => 'Orden',
		'phone' => 'Teléfono',
		'loading' => 'Cargando...',
		'branches' => 'Sucursales',
		'memmbers' => 'Miembros',
		'description' => 'Descripción',
		'address' => 'Dirección',
		'birthdate' => 'Fecha de nacimiento',
		'quantity' => 'Cantidad',
		'min_quantity' => 'Cantidad mínima',
		'password' => 'Contraseña',
		'status' => 'Estado',
		'close' => 'Cerrar',
		'continue' => 'Continuar',

		'initial' => 'Inicial',
		'delivery_date' => 'Fecha de entrega',
		'remaining' => 'Restante',
		'interest' => 'Interés',
		'installments_month' => 'Cuotas/mes',
		'payment_date' => 'Fecha de pago',
		'due' => 'Restante',
		'price_sum' => 'Suma de precios',
		'total_interest' => 'Interés total',
		'percent' => 'Porcentaje',
		'amount' => 'Monto',
		'payment_no' => 'Pago No.',

		'from' => 'Desde',
		'to' => 'Hasta',
		'date' => 'Fecha',

		'handled' => 'Manejado',
	],
	'sv' => [
		'title'       => 'Lagerprogram och lagerkontroll för småföretag | AllStockControl',
		'meta_description' => 'Enkelt lagerprogram för småföretag. Följ lagersaldo i realtid, hantera flera platser och få full lagerkontroll. Testa gratis – inget kreditkort krävs.',
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
		'excel_eyebrow' => 'Enkelt lagerprogram för småföretag',

		'excel_h2' => 'Använder du fortfarande Excel för att hålla koll på lagret?',

		'excel_p1' => 'Kalkylblad kan fungera för enkel lagerkontroll, men när företaget växer leder de ofta till felaktigt lagersaldo, manuella misstag och onödigt extraarbete.',

		'excel_p2' => 'AllStockControl är ett enkelt lagerhanteringssystem för småföretag som hjälper dig att gå från Excel och manuell lagerhantering till lagerkontroll i realtid, tillgänglig från valfri enhet.',

		'excel_cta' => 'Börja hantera ditt lager enklare',

		'cta_no_card' => 'Inget kreditkort krävs',
		'cta_cancel_anytime' => 'Avsluta när du vill',

		'excel_before_title' => 'Lagerhantering med Excel',

		'excel_problem_1_title' => 'Felaktigt lagersaldo',
		'excel_problem_1_desc' => 'Lagersaldot i kalkylblad kan snabbt bli inaktuellt när varor rör sig in och ut ur lagret.',

		'excel_problem_2_title' => 'Manuella lagerfel',
		'excel_problem_2_desc' => 'Manuell registrering av in- och utleveranser ökar risken för fel i lagret.',

		'excel_problem_3_title' => 'Svårt att skala',
		'excel_problem_3_desc' => 'Kalkylblad blir svårare att hantera när antalet produkter, användare och platser växer.',

		'excel_li_1' => 'Se ditt verkliga lagersaldo i realtid',
		'excel_solution_1_desc' => 'Håll lagersaldot uppdaterat när varor rör sig in och ut ur lagret.',

		'excel_li_2' => 'Hantera in- och utleveranser',
		'excel_solution_2_desc' => 'Registrera lagerrörelser och minska manuella fel.',

		'excel_li_3' => 'Hantera lager mellan användare och platser',
		'excel_solution_3_desc' => 'Håll lagret organiserat mellan flera användare och platser.',


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

		'feature_stock_title' => 'Undvik dålig lagerkontroll',
		'feature_stock_desc'  => 'Registrera inleveranser, utleveranser och justeringar i realtid för att minska felaktiga lagersaldon, missade försäljningar och onödiga inköp.',

		'feature_transfers_title' => 'Överföringar mellan filialer',
		'feature_transfers_desc'  => 'Flytta lager mellan platser med full spårbarhet, utan borttappade varor eller oklarheter.',

		'feature_min_stock_title' => 'Miniminivåer och lagerlarm',
		'feature_min_stock_desc'  => 'Sätt miniminivåer och få varningar innan varor tar slut. Ett enkelt sätt att undvika dålig lagerkontroll, förluster och förseningar.',

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
		'smallbox_products_limit' => 'Produktgräns',

		'smallbox_name' => 'Namn',

		//small box buttons
		'edit_my_data' => 'Uppdatera information',
		'subscription' => 'Prenumeration',
		'add_members' => 'Lägg till medlemmar',
		'manage_companies' => 'Hantera företag',
		'complete_company_profile' => 'Slutför företagsprofil',

		// content titles
		'user_list' => 'Användarlista',
		'products_list' => 'Produktlista',
		'storage_list' => 'Lagerlista',
		'customers_list' => 'Kundlista',
		'sales_list' => 'Försäljningslista',
		'payments_list' => 'Betalningslista',
		'notifications' => 'Notiser',

		// Forms
		'edit_profile_title' => 'Redigera min profil',
		'upgrade_your_plan' => 'Uppgradera ditt plan',
		'select_a_subscription_pack' => 'Välj ett prenumerationspaket',
		'add_edit_company_title' => 'Lägg till eller redigera företag eller filial',
		'add_member_title' => 'Lägg till en ny medlem i ditt företag',
		'edit_member_title' => 'Redigera medlemsinformation',
		'company_info' => 'Företagsinformation',
		'add_product_title' => 'Lägg till en ny produkt',
		'product_form_simple_intro' => 'Lägg till en ny produkt i ditt lager. Fyll i de obligatoriska fälten och klicka på "Spara" för att lägga till produkten i din lagersaldo.',
		'show_advanced_options' => 'Visa avancerade alternativ',
		'hide_advanced_options' => 'Dölj avancerade alternativ',
		'add_category_or_subcategory' => 'Lägg till en ny kategori eller underkategori',
		'product_options' => 'Produktalternativ',
		'edit_product_title' => 'Redigera produkt',
		'delete_product_title' => 'Radera produkt',
		'storage_options' => 'Lageralternativ',
		'add_or_edit_slot' => 'Lägg till eller redigera slot',
		'add_or_edit_storage' => 'Lägg till eller redigera lager',
		'slot_options' => 'Slot-alternativ',
		'add_customer_title' => 'Lägg till en ny kund',
		'customer_data' => 'Kunddata',
		'customer_reference' => 'Kundreferens',
		'customer_options' => 'Kundalternativ',
		'edit_customer_title' => 'Redigera kund',
		'delete_customer_title' => 'Radera kund',
		'create_sale_title' => 'Skapa en ny försäljning',
		'method_of_payment' => 'Betalningsmetod',
		'sale_options' => 'Försäljningsalternativ',
		'edit_sale_title' => 'Redigera försäljning',
		'delete_sale_title' => 'Radera försäljning',
		'crete_payment_title' => 'Skapa en ny betalning',
		'payment_options' => 'Betalningsalternativ',

		'estimated_cost' => 'Beräknad kostnad',
		'select_extra_service' => 'Välj extra tjänst',
		'confirm_delete_product' => 'Är du säker på att du vill radera den här produkten?',
		'confirm_delete_customer' => 'Är du säker på att du vill radera den här kunden?',
		'confirm_delete_sale' => 'Är du säker på att du vill radera den här försäljningen och all associerad data?',
		'confirm_delete_payment' => 'Är du säker på att du vill radera den här betalningen?',

		'form_drop_image' => 'Släpp bilden här eller klicka för att välja',
		'form_name' => 'Namn',
		'form_surname' => 'Efternamn',
		'form_birthday' => 'Födelsedatum',
		'form_country_code' => 'Landskod',
		'form_user_role' => 'Användarroll',
		'select_document_type' => 'Välj dokumenttyp',

		'company_name' => 'Företagsnamn',
		'organization_no' => 'Organisationsnummer',
		'company_address' => 'Företagsadress',
		'company_country_code' => 'Landskod',
		'company_phone' => 'Företagstelefon',

		'slot_name' => 'Slot namn',
		'current_capacity' => 'Nuvarande kapacitet',
		'max_capacity' => 'Max kapacitet',

		'customer_type' => 'Kundtyp',
		'document_type' => 'Dokumenttyp',
		'document_no' => 'Dokumentnummer',
		'references_1' => 'Referens 1',
		'references_1_phone' => 'Referens 1 telefon',
		'references_2' => 'Referens 2',
		'references_2_phone' => 'Referens 2 telefon',

		'person_who_pays' => 'Person som betalar',

		'product_search' => 'Sök produkter',
		'storage_or_product_no' => 'Lager-/produktnummer',
		'search_slot' => 'Sök slot',
		'search_customer' => 'Sök kund',
		'search_sale' => 'Sök försäljning',
		'search_payment' => 'Sök betalning',
		'search_messages' => 'Sök Meddelanden',
		'select_a_notification' => 'Välj en avisering',

		'enter_name_or_document' => 'Ange namn eller dokumentnummer',
		'enter_product_name' => 'Ange produktnamn',
		'mark_category' => 'Märk / Kategori',
		'model' => 'Modell',
		'sub_model' => 'Sub/Modell',
		'message' => 'Meddelande',

		// Onboarding Guide
		'get_your_inventory_ready' => 'Förbered ditt lager',

		'track_your_progress' => 'Följ din utveckling',
		'company_setup' => 'Slutför din företagsprofil',
		'create_first_product' => 'Skapa din första produkt',
		'add_first_customer' => 'Lägg till din första kund',
		'record_your_first_sale' => 'Registrera din första försäljning',

		'first_company_reward_eyebrow' => 'Företagsprofilen är klar.',
		'first_company_reward_title' => 'Ditt företag är redo!',
		'first_company_reward_description' => 'Toppen! Din företagsprofil är nu konfigurerad. Du är ett steg närmare att ha ditt lager redo.',

		'track_desc' => 'Här kan du se stegen som behövs för att konfigurera ditt lager.',
		'first_product_desc' => 'Börja med att lägga till en produkt i ditt lager. Det tar mindre än en minut.',
		'first_customer_desc' => 'Skapa din första kundprofil för att enklare hantera och följa dina försäljningar.',
		'first_sale_desc' => 'Registrera din första försäljning och se hur ditt lager uppdateras i realtid.',

		'first_client_reward_eyebrow' => 'Första kunden tillagd.',
		'first_client_reward_title' => 'Din första kund är klar!',
		'first_client_reward_description' => 'Toppen! Du har lagt till din första kund. Nu kan du använda kundprofilen när du registrerar försäljningar.',
		'view_my_customer' => 'Visa min kund',
		'create_another_customer' => 'Skapa en ny kund',

		'first_sale_reward_eyebrow' => 'Första försäljningen registrerad.',
		'first_sale_reward_title' => 'Du har gjort din första försäljning!',
		'first_sale_reward_description' => 'Toppen! Du har registrerat din första försäljning och ditt lager har uppdaterats. Den första konfigurationen är nu klar.',
		'view_my_sale' => 'Visa min försäljning',
		'go_to_dashboard' => 'Gå till översikten',

		'next' => 'Nästa',
		'back' => 'Tillbaka',
		'done' => 'Klart',

		// Onboarding Guide - Tooltips
		'welcome_to_allstockcontrol' => 'Välkommen till AllStockControl!',
		'start_your_inventory' => 'Börja ditt lageräventyr!',
		'welcome_first_product_description' => 'Låt oss börja med att skapa din första produkt. Detta hjälper dig att förstå hur du effektivt hanterar ditt lager.',
		'create_first_product_help' => 'Klicka här för att skapa din första produkt och börja hantera ditt lager.',
		'create_my_first_product' => 'Skapa min första produkt',
		'explore_dashboard_first' => 'Utforska instrumentpanelen först',

		'first_product_reward_eyebrow' => 'Första produkten skapad!',
		'first_product_reward_title' => 'Din första framgång!',
		'first_product_reward_description' => 'Bra jobbat! Du har slutfört ditt första lagerhinder. Här är en liten belöning för att fira.',
		'view_my_product' => 'Visa min produkt',
		'create_another_product' => 'Skapa en annan produkt',

		// Payment
		'non_payment_message' => 'Denna prenumeration är för närvarande inaktiv på grund av utebliven betalning!',
		'non_payment_message_reactivate' => 'Din prenumeration har inaktiverats på grund av utebliven betalning. Om du vill fortsätta använda AllStockControls lagerhanteringssystem, vänligen granska dina betalningsuppgifter och återaktivera din prenumeration.',

		'your_free_trial_has_expired' => 'Din 30-dagars gratis provperiod har gått ut!',
		'trial_expired_desc' => 'Din 30-dagars gratis provperiod har gått ut. Om du vill fortsätta använda AllStockControls lagerhanteringssystem, vänligen välj ett av våra prenumerationspaket som bäst passar dina behov.',
		'trial_expired_warning' => 'Om du inte uppgraderar ditt paket kommer all data som sparats hittills att raderas inom 14 dagar.',

		// Products
		'single_unit' => 'Enkel enhet',
		'multi_pack' => 'Multi-pack',
		'product_name' => 'Produktnamn',
		'hs_code' => 'HS-kod',
		'type' => 'Typ',
		'qty' => 'Ant.',
		'stock' => 'Lager',
		'export' => 'Exportera',
		'units' => 'Enheter',
		'weight_unit' => 'vikt / enhet',
		'total_weight' => 'Total vikt',
		'year' => 'År',
		'price' => 'Pris',
		'purpose' => 'Syfte',

		'uncategorized' => 'Okategoriserad',
		'no_model' => 'Ingen modell tilldelad',
		'no_submodel' => 'Ingen sub/modell tilldelad',

		// Buttons
		'cancel' => 'Avbryt',
		'update' => 'Uppdatera',
		'upgrade' => 'Uppgradera',
		'create_affiliate' => 'Skapa filial',
		'ok' => 'Ok',
		'confirm' => 'Bekräfta',
		'create' => 'Skapa',
		'delete_account' => 'Radera konto',
		'create_product' => 'Skapa produkt',
		'create_category' => 'Skapa kategori',
		'new_mark' => 'Ny mark',
		'new_model' => 'Ny modell',
		'new_submodel' => 'Ny sub/modell',
		'request_product' => 'Begär produkt',
		'edit_product' => 'Redigera produkt',
		'delete_product' => 'Radera produkt',
		'storage_menu' => 'Lager meny',
		'manage_slots' => 'Hantera slots',
		'manage_storage' => 'Hantera lager',
		'create_slot' => 'Skapa slot',
		'add_storage' => 'Lägg till lager',
		'select_slot' => 'Välj en slot',
		'save_changes' => 'Spara ändringar',
		'delete_slot' => 'Radera slot',
		'create_customer' => 'Skapa kund',
		'edit_customer' => 'Redigera kund',
		'delete_customer' => 'Radera kund',
		'create_sale' => 'Skapa försäljning',
		'reactivate_subscription' => 'Reaktivera prenumeration',
		'upgrade_package' => 'Uppgradera ditt paket',
		'edit_sale' => 'Redigera försäljning',
		'more_information' => 'Mer information',
		'delete_sale' => 'Radera försäljning',
		'make_payment' => 'Gör en betalning',
		'edit_payment' => 'Redigera betalning',
		'delete_payment' => 'Radera betalning',
		'send' => 'Skicka',

		// Global arrays
		'package_descriptions' => [
			'try_pack' => 'Prova vårt lagerhanteringsprogram gratis i 30 dagar. Inget kreditkort krävs.',
			'starter_pack' => 'Perfekt för frilansare och mycket små företag som börjar med lagerkontroll.',
			'basic_pack' => 'Idealisk för små butiker som behöver bättre kontroll över produkter och lagertransaktioner.',
			'business_pack' => 'Designad för växande företag som hanterar lager på flera platser.',
			'growth_pack' => 'För företag som skalar upp verksamheten med flera platser och team.',
			'scale_pack' => 'Byggt för stora operationer som kräver prestanda, kontroll och skalbarhet.',
			'enterprise_pack' => 'Skräddarsydd lagerlösning för stora organisationer. Kontakta oss för en anpassad plan.'
		],

		'document_types' => [
			'national_id_cedula' => 'National ID / Cédula',
			'passport' => 'Pass',
			'driver_license' => 'Körkort',
			'social_security_card' => 'Socialförsäkringskort',
			'tax_id' => 'Skatte-ID',
			'other' => 'Annat',
		],

		'general_status' => [
			'active' => 'Aktiv',
			'inactive' => 'Inaktiv'
		],

		'customer_types' => [
			'regular' => 'Vanlig',
			'vip' => 'VIP',
			'wholesale' => 'Grossist',
			'retail' => 'Detaljhandel',
			'online' => 'Online',
			'other' => 'Annat',
		],

		// messages
		'no_marks_found' => 'Inga märken hittades',
		'no_models_found' => 'Inga modeller hittades för detta märke',
		'no_submodels_found' => 'Inga sub/modeller hittades för denna modell',
		'no_results_yet' => 'Inga resultat än',
		'product_found' => 'Produkt hittad',
		'no_product_found' => 'Ingen produkt hittades',
		'no_slots_found' => 'Inga slots hittades.',
		'slot_info' => 'Slot information',

		// Global
		'company' => 'Företag',
		'product' => 'Produkt',
		'customer' => 'Kund',
		'order' => 'Order',
		'phone' => 'Telefon',
		'loading' => 'Laddar...',
		'branches' => 'Filialer',
		'memmbers' => 'Medlemmar',
		'description' => 'Beskrivning',
		'address' => 'Adress',
		'birthdate' => 'Födelsedatum',
		'quantity' => 'Kvantitet',
		'min_quantity' => 'Minimikvantitet',
		'currency' => 'Valuta',
		'password' => 'Lösenord',
		'status' => 'Status',
		'close' => 'Stäng',
		'continue' => 'Fortsätt',

		'initial' => 'Initial',
		'delivery_date' => 'Leveransdatum',
		'remaining' => 'Kvar',
		'interest' => 'Ränta',
		'installments_month' => 'Installment/månad',
		'payment_date' => 'Betalningsdatum',
		'due' => 'Kvar',
		'price_sum' => 'Prissumma',
		'total_interest' => 'Total ränta',
		'percent' => 'Procent',
		'amount' => 'Belopp',
		'payment_no' => 'Betalningsnummer',

		'from' => 'Från',
		'to' => 'Till',
		'date' => 'Datum',

		'handled' => 'Hanterad',
	],
];

$t = $i18n[$lang] ?? $i18n['en'];

$GLOBALS['lang'] = $lang;
$GLOBALS['t'] = $t;
$GLOBALS['i18n'] = $i18n;

// Helper para construir URLs con ?lang=…
if (!function_exists('url_with_lang')) {
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
}

if (!function_exists('localized_url')) {
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
}

if (!function_exists('tr')) {
	function tr(string $key, string $fallback = ''): string {
		if (!isset($GLOBALS['t']) || !is_array($GLOBALS['t'])) {
			$lang = $GLOBALS['lang'] ?? 'en';
			$i18n = $GLOBALS['i18n'] ?? [];

			if (isset($i18n[$lang]) && is_array($i18n[$lang])) {
				$GLOBALS['t'] = $i18n[$lang];
			}
		}

		return $GLOBALS['t'][$key] ?? $fallback;
	}
}

if (!function_exists('tr_nested')) {
	function tr_nested(string $group, string $key, string $fallback = ''): string {
		$t = $GLOBALS['t'] ?? [];

		$value = $t[$group][$key] ?? $fallback;

		return is_string($value) ? $value : $fallback;
	}
}
?>