<?php
class GlobalArrays {
	public static $ranks = [
		1 => "Administrator (Full Access)",
		2 => "Manager (Manage teams/data)",
		3 => "Supervisor (Oversee operations)",
		4 => "Operator (Limited access)",
		5 => "Viewer (Read-only access)"
	];

	public static $vehicleTypes = [
		1  => "Motorcycle",
		2  => "Car",
		3  => "SUV",
		4  => "Pickup Truck",
		5  => "Van",
		6  => "Minibus",
		7  => "Bus",
		8  => "Light Truck",
		9  => "Medium Truck",
		10 => "Heavy Truck",
		11 => "Trailer Truck / Articulated Lorry",
		12 => "Construction Vehicle",
		13 => "Agricultural Vehicle"
	];

	public static $productTypes = [
		1 => "New",
		2 => "Used"
	];

	public static $documentTypes = [
		1 => "National ID / Cedula",
		2 => "Passport",
		3 => "Driver's License",
		4 => "Social Security Number",
		5 => "Tax Identification Number"
	];

	public static $customerTypes = [
		1 => "Individual",
		2 => "Company"
	];
	
	public static $customerStatus = [
		1 => "Active",
		2 => "Inactive"
	];
	
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
		'+54'     => 'Argentina',
		'+61'     => 'Australia',
		'+55'     => 'Brazil',
		'+1'      => 'Canada',
		'+56'     => 'Chile',
		'+86'     => 'China',
		'+57'     => 'Colombia',
		'+506'    => 'Costa Rica',
		'+53'     => 'Cuba',
		'+1'	  => 'Dominican Republic',
		'+33'     => 'France',
		'+49'     => 'Germany',
		'+91'     => 'India',
		'+62'     => 'Indonesia',
		'+39'     => 'Italy',
		'+81'     => 'Japan',
		'+52'     => 'Mexico',
		'+31'     => 'Netherlands',
		'+64'     => 'New Zealand',
		'+47'     => 'Norway',
		'+507'    => 'Panama',
		'+51'     => 'Peru',
		'+63'     => 'Philippines',
		'+48'     => 'Poland',
		'+351'    => 'Portugal',
		'+1'	  => 'Puerto Rico',
		'+7'      => 'Russia',
		'+65'     => 'Singapore',
		'+27'     => 'South Africa',
		'+34'     => 'Spain',
		'+46'     => 'Sweden',
		'+41'     => 'Switzerland',
		'+66'     => 'Thailand',
		'+90'     => 'Turkey',
		'+44'     => 'United Kingdom',
		'+1'      => 'United States',
		'+58'     => 'Venezuela',
		'+84'     => 'Vietnam'
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
		// "MXN" => "(MXN) Mexican Peso",
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
}
?>