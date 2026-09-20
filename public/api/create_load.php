<?php
use App\Shippings\LoadRepository;
use App\Shippings\LoadService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Load could not be created",
    "img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

$transactionStarted = false;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

    $authUser = requireAuth();
    $userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) throw new Exception("User session not found.");
    if ($companyId <= 0) throw new Exception("Company ID is required.");

    if (!check_user_permission($userId, 'sales_handler')) {
        throw new Exception("Access denied. You do not have permission to create loads.");
    }

	// if (!check_user_permission($userId, 'sales_handler')) {
	// 	throw new Exception("Access denied. You do not have permission to create loads.");
	// }

	$input = json_decode(file_get_contents('php://input'), true);

	if (!is_array($input) || empty($input)) {
		throw new Exception("No data received.");
	}

	$repository = new LoadRepository();
	$service = new LoadService($repository);

	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start load creation transaction.");
	}

	$transactionStarted = true;

	$load = $service->createLoad(
		$userId,
		$companyId,
		$input
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete load creation.");
	}

	$transactionStarted = false;

	$loadId = (int)$load["load_id"];
	$loadNo = (int)$load["load_no"];
	$shippingId = (int)$input["shippings_id"];

    // Log de actividad
    log_activity(
        $userId,
        "create_load",
        "Created new load #{$loadId} for shipping_id {$shippingId}",
        "loads",
        $loadId
    );

    $response = [
        "success" => true,
        "message" => "Load created successfully",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Throwable $e) {
	if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;