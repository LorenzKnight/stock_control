<?php
use App\Slots\SlotRepository;
use App\Slots\SlotService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Deletion failed",
    "img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

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

    // // 🔒 Verificar permisos
    // if (!check_user_permission($userId, 'platform_admin')) {
    //     throw new Exception("Access denied. You do not have permission to delete data.");
    // }

    $slotId = (int)($_POST["slot_id"] ?? 0);

    if ($slotId <= 0) {
        throw new Exception("Slot ID is required.");
    }

    $repository = new SlotRepository();
	$service = new SlotService($repository);

	$service->deleteSlot(
		$companyId,
		$slotId
	);

    // 🧾 Registrar la acción
    log_activity(
        $userId,
        "delete slot",
        "Slot ID $slotId deleted for company $companyId.",
        "slot",
        $slotId
    );

    // ✅ Éxito
    $response = [
        "success" => true,
        "message" => "Slot deleted successfully.",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;