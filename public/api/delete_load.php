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
    "message" => "Deletion failed.",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

$transactionStarted = false;

try {
    // 🔒 Verificar método
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed.");
    }

    // 🔒 Verificar sesión de usuario
    $authUser = requireAuth();
    $userId = (int)($authUser["user_id"] ?? 0);
    $companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) {
        throw new Exception("Authentication failed. User not identified.");
    }

    if ($companyId <= 0) {
        throw new Exception("Authentication failed. Company not identified.");
    }

    // 🔒 Verificar permisos (usa el permiso adecuado de tu sistema)
    if (!check_user_permission($userId, 'platform_admin')) {
        throw new Exception("Access denied. You do not have permission to delete loads.");
    }

    // 📦 Validar parámetro recibido
    $loadId = (int)($_POST["load_id"] ?? 0);
    if ($loadId <= 0) throw new Exception("Invalid load ID.");

    $repository = new LoadRepository();
	$service = new LoadService($repository);

    if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start load deletion transaction.");
	}

	$transactionStarted = true;

	$service->deleteLoad(
		$companyId,
		$loadId
	);

    if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete load deletion.");
	}

	$transactionStarted = false;

    // 🧾 Registrar la acción
    log_activity(
        $userId,
        "delete load",
        "Deleted load ID {$loadId} and its loaded products for company {$companyId}.",
        "loads",
        $loadId
    );

    // ✅ Respuesta exitosa
    $response = [
        "success" => true,
        "message" => "Load and all related products deleted successfully.",
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