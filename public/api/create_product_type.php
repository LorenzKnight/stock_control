<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "../images/sys-img/error.gif",
	"redirect_url" => ""
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	if (!function_exists('check_user_permission') || !check_user_permission($userId, 'create_data')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	// Leer JSON o x-www-form-urlencoded
	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
	if (!is_array($payload)) $payload = $_POST;

	$name      = isset($payload['name']) ? trim($payload['name']) : '';
	// Puede venir '' (=> NULL) o numérico
	$companyId = isset($payload['company_id']) && $payload['company_id'] !== '' ? (int)$payload['company_id'] : null;

	if ($name === '') throw new Exception("Type name is required.");
	if (mb_strlen($name) > 100) throw new Exception("Type name too long (max 100).");

	// Unicidad (case-insensitive) por usuario y company_id (null-safe)
	// Usamos select_from con LOWER(col) = valor
	$where = [
		'create_by' => $userId
	];

	if ($companyId === null) {
		$where['company_id'] = null;
	} else {
		$where['company_id'] = $companyId;
	}
	// Comparación case-insensitive exacta: LOWER(product_type_name) = lower($name)
	$where['LOWER(product_type_name)'] = [
		'condition' => '=',
		'value'     => mb_strtolower($name)
	];

	$existsRes = json_decode(select_from(
		"product_type",
		["product_type_id","product_type_name"],
		$where,
		["fetch_first" => true]
	), true);

	if (!empty($existsRes['success']) && !empty($existsRes['data'])) {
		// Ya existe -> devolver success=true con el id para que la UI lo seleccione
		echo json_encode([
			"success" => true,
			"id"      => $existsRes['data']['product_type_id'],
			"name"    => $existsRes['data']['product_type_name'],
			"message" => "Type already exists"
		], JSON_UNESCAPED_UNICODE);
		exit;
	}

	// Insertar
	$insertData = [
		"user_id"           => $userId,
		"product_type_name" => $name,
		"create_by"         => $userId,
		"created_at"        => date("Y-m-d H:i:s")
	];
	// company_id es opcional/nullable
	if ($companyId !== null) $insertData["company_id"] = $companyId;

	$ins = json_decode(insert_into("product_type", $insertData, ["id" => "product_type_id"]), true);
	if (empty($ins['success'])) {
		throw new Exception($ins['message'] ?? "Error inserting product type.");
	}

	echo json_encode([
		"success" => true,
		"id"      => $ins['id'],
		"name"    => $name,
		"message" => "Type created successfully"
	], JSON_UNESCAPED_UNICODE);
	exit;
} catch (Exception $e) {
	$response["success"] = false;
	$response["message"] = $e->getMessage();
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	exit;
}