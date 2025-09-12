<?php
class GlobalArrays {
	// public static $vehicleTypes = [
	// 	1  => "Motorcycle",
	// 	2  => "Car",
	// 	3  => "SUV",
	// 	4  => "Pickup Truck",
	// 	5  => "Van",
	// 	6  => "Minibus",
	// 	7  => "Bus",
	// 	8  => "Light Truck",
	// 	9  => "Medium Truck",
	// 	10 => "Heavy Truck",
	// 	11 => "Trailer Truck / Articulated Lorry",
	// 	12 => "Construction Vehicle",
	// 	13 => "Agricultural Vehicle"
	// ];

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