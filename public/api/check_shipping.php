<?php
use App\Shippings\ShippingRepository;
use App\Shippings\ShippingService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
];

$transactionStarted = false;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    // 🔒 Autenticación con token JWT
    $authUser = requireAuth();
    $userId = (int)($authUser["user_id"] ?? 0);
    $companyId = (int)($authUser["company_id"] ?? 0);

    // 🔍 Validar shipping_id recibido
    $shippingId = (int)($_POST["shipping_id"] ?? 0);

    // 📍 Coordenadas opcionales
    $latitude = isset($_POST["latitude"]) ? (float)($_POST["latitude"]) : null;
    $longitude = isset($_POST["longitude"]) ? (float)($_POST["longitude"]) : null;

    $checkOnly = isset($_POST["test_mode"]) && $_POST["test_mode"] === "check_only";

    $repository = new ShippingRepository();
	$service = new ShippingService($repository);

    if ($checkOnly) {
		$service->checkShipping(
			$userId,
			$companyId,
			$shippingId,
			$latitude,
			$longitude,
			true
		);

        $response = [
			"success" => true,
			"message" =>
				"User can check this shipping."
		];

		echo json_encode($response);
		exit;
	}

    if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start shipping check transaction.");
	}

	$transactionStarted = true;

	$result = $service->checkShipping(
		$userId,
		$companyId,
		$shippingId,
		$latitude,
		$longitude
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete shipping check.");
	}

	$transactionStarted = false;

	/*
	 * Push después del COMMIT:
	 * una falla externa no debe revertir
	 * un tracking ya guardado correctamente.
	 */
	if (
		!empty($result["status_changed"]) &&
		!empty($result["notification_user_ids"])
	) {
		sendShippingStatusPush(
			$shippingId,
			2,
			$result[
				"notification_user_ids"
			],
			$userId
		);
	}

	$checkpointName = (string)($result["checkpoint"] ?? "Scanned at checkpoint");

    // 📝 Registrar actividad
    log_activity(
		$userId,
		"check_shipping",
		"Shipping checked at {$checkpointName}",
		"shippings",
		$shippingId
	);

    $response = [
        "success"    => true,
        "message"    => "Shipping marked as checked successfully!",
        "checkpoint" => $checkpointName
    ];
} catch (Exception $e) {
	if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;