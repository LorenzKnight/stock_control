<?php
use App\ProductTypes\ProductTypeRepository;
use App\ProductTypes\ProductTypeService;

require_once('../inc/cors.php');
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

	$authUser = requireAuth();
    $userId = $authUser["user_id"];
	
	if ($userId <= 0) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

	if (!function_exists('check_user_permission') || !check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	// Leer JSON o x-www-form-urlencoded
	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
	if (!is_array($payload)) $payload = $_POST;

	$name = (string)($payload["name"] ?? '');
	
	$companyId = isset($payload['company_id']) && $payload['company_id'] !== ''
		? (int)$payload['company_id']
		: null;

	$repository = new ProductTypeRepository();
	$service = new ProductTypeService($repository);

	$result = $service->createProductType(
		$userId,
		$name,
		$companyId
	);

	echo json_encode([
		"success" => true,
		"id"      => $result['id'],
		"name"    => $result['name'],
		"message" => $result["already_exists"]
			? "Type already exists"
			: "Type created successfully"
	], JSON_UNESCAPED_UNICODE);

	exit;

} catch (Exception $e) {
	$response["success"] = false;
	$response["message"] = $e->getMessage();
	echo json_encode($response, JSON_UNESCAPED_UNICODE);

	exit;
}