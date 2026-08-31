<?php
use App\ExtraServices\ExtraServiceRepository;
use App\ExtraServices\ExtraServiceService;

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
    // 🚫 Solo permitir POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    // 🔒 Autenticación por token JWT
    $authUser = requireAuth();
    $creatorId = $authUser["user_id"] ?? null;

    if (!$creatorId) {
		throw new Exception("Unauthorized access.");
	}

    // 🧩 Recibir datos del formulario
    $userId = (int)($_POST["user_id"] ?? 0);
    $serviceName = trim($_POST["service_name"] ?? "");
    $servicePrice = (float)($_POST["service_price"] ?? 0);
    $status = isset($_POST["service_status"]) && $_POST["service_status"] == 1 ? 1 : 0;

    $repository = new ExtraServiceRepository();
	$service = new ExtraServiceService($repository);

	$serviceId = $service->createExtraService(
		$userId,
		(int)$creatorId,
		$serviceName,
		$servicePrice,
		$status
	);

    // 📝 Registrar actividad (opcional)
    log_activity(
        $creatorId,
        "create_service",
        "Created service: {$serviceName}",
        "extra_services", 
        $serviceId
    );

    $response = [
        "success" => true,
        "message" => "Service created successfully.",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;