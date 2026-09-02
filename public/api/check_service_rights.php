<?php
use App\ServiceRights\ServiceRightRepository;
use App\ServiceRights\ServiceRightService;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success" => false,
    "message" => "Access denied",
    "data" => [
        "can_access" => false
    ]
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed.");
    }

    $authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;

    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

    $serviceName = trim($_GET["service_name"] ?? "");

    $repository = new ServiceRightRepository();
	$service = new ServiceRightService($repository);

    $canAccess = $service->canAccessService(
		(int)$userId,
		$serviceName
	);

    if ($canAccess) {
        $response = [
            "success" => true,
            "message" => "Access granted for service: $serviceName",
            "data" => [
                "can_access" => true
            ]
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Access denied for service: $serviceName",
            "data" => [
                "can_access" => false
            ]
        ];
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;