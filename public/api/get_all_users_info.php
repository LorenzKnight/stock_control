<?php
require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No users found",
	"data" => []
];

try {
	$authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;

	if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

	// 🔍 Parámetro de búsqueda opcional
	$search = $_GET["search"] ?? '';

	// 🔎 Filtro base: solo administradores (sin parent_user)
	$where = [
		"parent_user" => null
	];

	// 🔎 Si hay búsqueda, agregar condiciones OR
	if (!empty($search)) {
		$where["OR"] = [
			"name ILIKE"     => "%{$search}%",
			"surname ILIKE"  => "%{$search}%",
			"email ILIKE"    => "%{$search}%",
			"username ILIKE" => "%{$search}%"
		];
	}

	// 📦 Consultar usuarios
	$usersQuery = select_from("users", [
		"user_id",
		"parent_user",
		"name",
		"surname",
		"email",
		"country_code",
		"phone",
		"username",
		"image",
		"verified",
		"birthday",
		"signup_date",
		"rank",
		"company_id",
		"package_id",
		"status",
		"status_by_admin"
	], $where, [
		"order_by" => "signup_date",
		"order_direction" => "DESC"
	]);

	$parsed = json_decode($usersQuery, true);
	if (!isset($parsed["success"]) || !$parsed["success"] || empty($parsed["data"])) {
		throw new Exception("No users available.");
	}

	// 🧩 Procesar resultados
	foreach ($parsed["data"] as &$user) {
		$user["full_name"]      = trim(($user["name"] ?? '') . ' ' . ($user["surname"] ?? ''));
		$user["status_text"]    = ($user["status"] == 1) ? "Active" : "Inactive";
		$user["verified_text"]  = ($user["verified"] == 1) ? "Verified" : "Unverified";
		$user["image"]          = $user["image"] ?? "";
		$user["signup_date"]    = $user["signup_date"] ? date("Y-m-d", strtotime($user["signup_date"])) : null;

		// Rank name opcional
		if (class_exists('GlobalArrays') && property_exists('GlobalArrays', 'userRanks')) {
			$user["rank_name"] = GlobalArrays::$userRanks[$user["rank"]] ?? "Unknown";
		} else {
			$user["rank_name"] = "Unknown";
		}
	}

	$response = [
		"success" => true,
		"message" => "Users loaded.",
		"data" => $parsed["data"]
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;