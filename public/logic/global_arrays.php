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
}
?>