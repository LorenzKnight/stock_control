<?php
use App\Shippings\ShippingRepository;
use App\Shippings\ShippingService;
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
    "message" => "Deletion failed",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

$transactionStarted = false;

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $userId = (int)($authUser["user_id"] ?? 0);
    $companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    if ($companyId <= 0) {
		throw new Exception("Company ID not found for user.");
	}

    // 🔒 Verificar permisos
    if (!check_user_permission($userId, 'platform_admin')) {
        throw new Exception("Access denied. You do not have permission to delete data.");
    }

    if (empty($_POST["shippings_id"])) {
        throw new Exception("Shipping ID is required.");
    }

    $shippingId = (int)$_POST["shippings_id"] ?? 0;

    if ($shippingId <= 0) {
		throw new Exception("Shipping ID is required.");
	}

    $shippingRepository = new ShippingRepository();
	$shippingService = new ShippingService($shippingRepository);

	$loadRepository = new LoadRepository();
	$loadService = new LoadService($loadRepository);

    if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start shipping deletion transaction.");
	}

    $transactionStarted = true;

    $loadService->deleteLoadsForShipping(
		$companyId,
		$shippingId
	);

	$qrImageName = $shippingService->deleteShipping(
		$shippingId,
		$companyId
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete shipping deletion.");
	}

	$transactionStarted = false;

	/*
	 * El archivo se borra después del COMMIT.
	 * Así nunca perdemos el QR si la operación
	 * de base de datos termina haciendo ROLLBACK.
	 */
	if (
		$qrImageName !== null &&
		$qrImageName !== '' &&
		$qrImageName !== '.gitkeep'
	) {
		$baseDirectory = realpath(__DIR__ . "/../images/shippings-code");

		if ($baseDirectory !== false) {
			$targetPath = $baseDirectory . DIRECTORY_SEPARATOR . basename($qrImageName);

			$realTarget = realpath($targetPath);

			if (
				$realTarget !== false &&
				strpos($realTarget, $baseDirectory) === 0 &&
				is_file($realTarget)
			) {
				if (!@unlink($realTarget)) {
					error_log("Could not delete shipping QR: " . $realTarget);
				}
			}
		}
	}

    // 🧾 Registrar la acción
    log_activity(
        $userId,
        "delete shipping",
        "Shipping ID {$shippingId} and all associated loads and loaded products deleted for company {$companyId}.",
        "shippings",
        $shippingId
    );

    // ✅ Éxito
    $response = [
        "success" => true,
        "message" => "Shipping and all related data deleted successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
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