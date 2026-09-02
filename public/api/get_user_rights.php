<?php
use App\ServiceRights\ServiceRightRepository;
use App\ServiceRights\ServiceRightService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success"   => false,
    "message"   => "No service rights found",
    "count"     => 0,
    "data"      => []
];

try {
    // 🔐 Autenticación obligatoria
    $authUser = requireAuth();
    $authUserId = $authUser["user_id"] ?? null;

    if (empty($authUserId)) {
        throw new Exception("Unauthorized access: invalid or missing token.");
    }

    // 📥 Si se envía un user_id en GET, se usa ese; si no, el del token
    $paramUserId = $_GET["user_id"] ?? $authUserId;
    $rightId = isset($_GET['right_id']) ? (int)$_GET['right_id'] : null;

    $canAccessParam = $_GET["can_access"] ?? '';
	$canAccess = $canAccessParam !== ''
		? (int)$canAccessParam
		: null;

    $repository = new ServiceRightRepository();
	$service = new ServiceRightService($repository);

	$rights = $service->getUserRights(
		$paramUserId,
		$rightId,
		$canAccess
	);

	$response = [
		"success"   => true,
		"message"   => "Service rights loaded successfully.",
		"count"     => count($rights),
		"data"      => $rights
	];

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;