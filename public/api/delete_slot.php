<?php
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
    $userId = intval($authUser["user_id"] ?? 0);

    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    // // 🔒 Verificar permisos
    // if (!check_user_permission($userId, 'platform_admin')) {
    //     throw new Exception("Access denied. You do not have permission to delete data.");
    // }

    if (empty($_POST["slot_id"])) {
        throw new Exception("Slot ID is required.");
    }

    $slotId = intval($_POST["slot_id"]);

    // 🔍 Obtener company_id del usuario
	$userInfo = select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
	$companyId = json_decode($userInfo, true)["data"]["company_id"] ?? 0;

    if ($companyId <= 0) {
        throw new Exception("Company ID not found for user.");
    }

    // 🔎 Validar que el slot pertenezca a la empresa del usuario
    $slotInfo = json_decode(
		select_from(
			"slot",
			["company_id"],
			["slot_id" => $slotId],
			["fetch_first" => true]
		), 
		true
	);

    $slotCompanyId = $slotInfo["data"]["company_id"] ?? 0;
    if ($slotCompanyId <= 0) {
        throw new Exception("Slot not found.");
    }

    if ($slotCompanyId !== $companyId) {
        throw new Exception("Access denied. This slot does not belong to your company.");
    }

    // 🧹 3️⃣ Borrar el slot principal (solo si pertenece a la misma compañía)
    $deleteSlot = json_decode(delete_from("slot",[
		"slot_id" => $slotId, 
		"company_id" => $companyId
	]), true);

    if (empty($deleteSlot["success"])) {
		throw new Exception("Failed to delete slot ID: $slotId");
	}

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