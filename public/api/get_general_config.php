<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No general configuration found",
	"data"    => []
];

try {

	if ($_SERVER["REQUEST_METHOD"] !== "GET") {
		throw new Exception("Method not allowed.");
	}

	// 🔐 Auth
	$authUser = requireAuth();
	$userId   = $authUser["user_id"] ?? null;

	if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

	$targetCompanyId = $_GET["company_id"] ?? null;

	if (!$targetCompanyId || !is_numeric($targetCompanyId)) {
		throw new Exception("Company ID is required.");
	}

	$targetCompanyId = (int)$targetCompanyId;

	// 👤 Resolver parent efectivo
	$userInfoRaw = select_from(
		"users",
		["parent_user"],
		["user_id" => $userId],
		["fetch_first" => true]
	);

	$userInfo = json_decode($userInfoRaw, true);

	if (empty($userInfo["success"]) || empty($userInfo["data"])) {
		throw new Exception("Unable to validate user.");
	}

	$parentId = empty($userInfo["data"]["parent_user"])
		? (int)$userId
		: (int)$userInfo["data"]["parent_user"];

	// 🔐 Validar que la company pertenece al parent
	$companyCheckRaw = select_from(
		"companies",
		["company_id"],
		[
			"company_id" => $targetCompanyId,
			"user_id"    => $parentId
		],
		["fetch_first" => true]
	);

	$companyCheck = json_decode($companyCheckRaw, true);

	if (empty($companyCheck["success"]) || empty($companyCheck["data"])) {
		throw new Exception("You do not have access to this company.");
	}

	// ⚙️ Obtener configuración general por empresa
	$configRaw = select_from(
		"settings",
		["*"],
		["company_id" => $targetCompanyId],
		["fetch_first" => true]
	);

	$configData = json_decode($configRaw, true);

	$config = [];

	if (!empty($configData["success"]) && !empty($configData["data"])) {
		$config = $configData["data"];
	}

	$response = [
		"success" => true,
		"message" => "General configuration loaded",
		"data"    => $config
	];

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;