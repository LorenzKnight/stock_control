<?php
class GlobalArrays {

	private static function ensureTranslationsLoaded(): void
	{
		if (!function_exists('tr')) {
			require_once(__DIR__ . '/../logic/mini_language_switcher.php');
		}
	}

	// package descriptions.
	public static $packageDescriptions = [
		1 => "Try Pack Description",
		2 => "Starter Pack Description",
		3 => "Basic Pack Description",
		4 => "Business Pack Description",
		5 => "Growth Pack Description",
		6 => "Scale Pack Description",
		7 => "Enterprise Pack Description"
	];

	public static function packageDescriptions(): array
	{
		self::ensureTranslationsLoaded();

		return [
			1 => tr_nested('package_descriptions', 'try_pack', self::$packageDescriptions[1]),
			2 => tr_nested('package_descriptions', 'starter_pack', self::$packageDescriptions[2]),
			3 => tr_nested('package_descriptions', 'basic_pack', self::$packageDescriptions[3]),
			4 => tr_nested('package_descriptions', 'business_pack', self::$packageDescriptions[4]),
			5 => tr_nested('package_descriptions', 'growth_pack', self::$packageDescriptions[5]),
			6 => tr_nested('package_descriptions', 'scale_pack', self::$packageDescriptions[6]),
			7 => tr_nested('package_descriptions', 'enterprise_pack', self::$packageDescriptions[7])
		];
	}

	// product types.
	public static $productTypes = [
		1 => "New",
		2 => "Used"
	];

	public static $productPurpose = [
		1 => "Stock",
		2 => "Export"
	];

	// document types.
	public static $documentTypes = [
		1 => "National ID / Cedula",
		2 => "Passport",
		3 => "Driver's License",
		4 => "Social Security Number",
		5 => "Tax Identification Number",
		6 => "Other"
	];

	public static function documentTypes(): array
	{
		self::ensureTranslationsLoaded();

		return [
			1 => tr_nested('document_types', 'national_id_cedula', self::$documentTypes[1]),
			2 => tr_nested('document_types', 'passport', self::$documentTypes[2]),
			3 => tr_nested('document_types', 'driver_license', self::$documentTypes[3]),
			4 => tr_nested('document_types', 'social_security_card', self::$documentTypes[4]),
			5 => tr_nested('document_types', 'tax_id', self::$documentTypes[5]),
			6 => tr_nested('document_types', 'other', self::$documentTypes[6]),
		];
	}


	// customer types.
	public static $customerTypes = [
		1 => "regular",
		2 => "vip",
		3 => "wholesale",
		4 => "retail",
		5 => "online",
		6 => "other"
	];

	public static function customerTypes(): array
	{
		self::ensureTranslationsLoaded();

		return [
			1 => tr_nested('customer_types', 'regular', self::$customerTypes[1]),
			2 => tr_nested('customer_types', 'vip', self::$customerTypes[2]),
			3 => tr_nested('customer_types', 'wholesale', self::$customerTypes[3]),
			4 => tr_nested('customer_types', 'retail', self::$customerTypes[4]),
			5 => tr_nested('customer_types', 'online', self::$customerTypes[5]),
			6 => tr_nested('customer_types', 'other', self::$customerTypes[6]),
		];
	}


	// general status.
	public static $generalStatus = [
		1 => "Active",
		0 => "Inactive"
	];

	public static function generalStatus(): array
	{
		self::ensureTranslationsLoaded();

		return [
			1 => tr_nested('general_status', 'active', self::$generalStatus[1]),
			0 => tr_nested('general_status', 'inactive', self::$generalStatus[0])
		];
	}
	

	// marital status.
	public static $maritalStatus = [
		1 => "Single",
		2 => "Married",
		3 => "Divorced",
		4 => "Widowed"
	];

	public static $paymentTerms = [
		6 => "6 Months",
		12 => "12 Months",
		24 => "24 Months",
		36 => "36 Months",
		48 => "48 Months",
		60 => "60 Months"
	];

	public static $countryPhoneCodes = [
		'AR|+54'  => 'Argentina',
		'AU|+61'  => 'Australia',
		'BR|+55'  => 'Brazil',
		'CA|+1'   => 'Canada',
		'CL|+56'  => 'Chile',
		'CN|+86'  => 'China',
		'CO|+57'  => 'Colombia',
		'CR|+506' => 'Costa Rica',
		'CU|+53'  => 'Cuba',
		'DO|+1'   => 'Dominican Republic',
		'FR|+33'  => 'France',
		'DE|+49'  => 'Germany',
		'IN|+91'  => 'India',
		'ID|+62'  => 'Indonesia',
		'IT|+39'  => 'Italy',
		'JP|+81'  => 'Japan',
		'MX|+52'  => 'Mexico',
		'NL|+31'  => 'Netherlands',
		'NZ|+64'  => 'New Zealand',
		'NO|+47'  => 'Norway',
		'PA|+507' => 'Panama',
		'PE|+51'  => 'Peru',
		'PH|+63'  => 'Philippines',
		'PL|+48'  => 'Poland',
		'PT|+351' => 'Portugal',
		'PR|+1'   => 'Puerto Rico',
		'RU|+7'   => 'Russia',
		'SG|+65'  => 'Singapore',
		'ZA|+27'  => 'South Africa',
		'ES|+34'  => 'Spain',
		'SE|+46'  => 'Sweden',
		'CH|+41'  => 'Switzerland',
		'TH|+66'  => 'Thailand',
		'TR|+90'  => 'Turkey',
		'GB|+44'  => 'United Kingdom',
		'US|+1'   => 'United States',
		'VE|+58'  => 'Venezuela',
		'VN|+84'  => 'Vietnam',
	];

	public static $paymentMethods = [
		1 => "Cash",
		2 => "Credit Card",
		3 => "Debit Card",
		4 => "Bank Transfer",
		5 => "Mobile Payment",
		6 => "Cryptocurrency"
	];

	public static $paymentStatus = [
		1 => "Pending",
		2 => "Completed",
		3 => "Failed",
		4 => "Refunded"
	];
	
	public static $saleStatus = [
		1 => "Pending",
		2 => "Completed",
		3 => "Cancelled"
	];

	public static $shippingStatus = [
		0 => "Cancelled",
		1 => "Pending",
		2 => "In transit",
		3 => "Delivered"
	];

	public static $currencies = [
		"USD" => "(USD) United States Dollar",
		"EUR" => "(EUR) Euro",
		// "JPY" => "(JPY) Japanese Yen",
		// "GBP" => "(GBP) British Pound Sterling",
		// "AUD" => "(AUD) Australian Dollar",
		// "CAD" => "(CAD) Canadian Dollar",
		// "CHF" => "(CHF) Swiss Franc",
		// "CNY" => "(CNY) Chinese Yuan",
		"SEK" => "(SEK) Swedish Krona",
		// "NZD" => "(NZD) New Zealand Dollar",
		"MXN" => "(MXN) Mexican Peso",
		// "SGD" => "(SGD) Singapore Dollar",
		// "HKD" => "(HKD) Hong Kong Dollar",
		"NOK" => "(NOK) Norwegian Krone",
		// "KRW" => "(KRW) South Korean Won",
		// "TRY" => "(TRY) Turkish Lira",
		// "RUB" => "(RUB) Russian Ruble",
		// "INR" => "(INR) Indian Rupee",
		// "BRL" => "(BRL) Brazilian Real",
		// "ZAR" => "(ZAR) South African Rand",
		// "DKK" => "(DKK) Danish Krone",
		// "PLN" => "(PLN) Polish Zloty",
		// "TWD" => "(TWD) New Taiwan Dollar",
		// "THB" => "(THB) Thai Baht",
		// "IDR" => "(IDR) Indonesian Rupiah",
		"DOP" => "(DOP) Dominican Peso"
	];

	public static $serviceRights = [
		"shipping_access"	=> "Shipping Access",
		// "dashboard_access"	=> "Access Dashboard",
		// "user_management"	=> "Manage Users",
		// "view_reports"		=> "View Reports",
		// "edit_settings"		=> "Edit Settings",
		// "inventory_control"	=> "Manage Inventory",
		// "process_orders"	=> "Process Orders",
		// "customer_support"	=> "Customer Support",
		// "marketing_tools"	=> "Marketing Tools",
		// "financial_control"	=> "Financial Management"
	];
}
?>