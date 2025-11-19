<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Deletion failed.",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

try {
    // 🔒 Verificar método
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed.");
    }

    // 🔒 Verificar sesión de usuario
    $authUser = requireAuth();
    if (!isset($authUser["user_id"]) || !$authUser["user_id"]) {
        throw new Exception("Authentication failed. User not identified.");
    }

    $userId = $authUser["user_id"];

    // 🔒 Verificar permisos (usa el permiso adecuado de tu sistema)
    if (!check_user_permission($userId, 'platform_admin')) {
        throw new Exception("Access denied. You do not have permission to delete loads.");
    }

    // 📦 Validar parámetro recibido
    $loadId = isset($_POST["load_id"]) ? (int)$_POST["load_id"] : 0;
    if ($loadId <= 0) {
        throw new Exception("Invalid load ID.");
    }

    // 🔍 Obtener información del load
    $loadInfo = json_decode(select_from(
        "loads", 
        ["company_id", "shippings_id"], 
        ["load_id" => $loadId], 
        ["fetch_first" => true]
    ), true);

    if (!$loadInfo["success"] || empty($loadInfo["data"])) {
        throw new Exception("Load not found.");
    }

    $loadData = $loadInfo["data"];
    $companyId = $loadData["company_id"] ?? null;
    $shippingId = $loadData["shippings_id"] ?? null;

    if (!$companyId) {
        throw new Exception("Company ID missing for this load.");
    }

    $userInfo = json_decode(select_from(
        "users",
        ["company_id"],
        ["user_id" => $userId],
        ["fetch_first" => true]
    ), true);

    $userCompanyId = $userInfo["data"]["company_id"] ?? null;

    if ((int)$userCompanyId !== (int)$companyId) {
        throw new Exception("Access denied. This load does not belong to your company.");
    }

    if ($shippingId) {
         $shippingInfo = json_decode(select_from(
            "shippings",
            ["status"],
            ["shippings_id" => $shippingId],
            ["fetch_first" => true]
        ), true);

        $shippingStatus = $shippingInfo["data"]["status"] ?? null;

        if ($shippingStatus && (int)$shippingStatus >= 3) {
            throw new Exception("Cannot delete loads from completed or delivered shippings.");
        }
    }

    // 🧹 1️⃣ Eliminar productos asociados al load
    $deleteProducts = json_decode(delete_from("loaded_products", ["load_id" => $loadId]), true);
    if (!$deleteProducts["success"]) {
        throw new Exception("Failed to delete loaded products for this load.");
    }

    // 🧹 2️⃣ Eliminar el load principal
    $deleteLoad = json_decode(delete_from("loads", ["load_id" => $loadId]), true);
    if (!$deleteLoad["success"]) {
        throw new Exception("Failed to delete load record.");
    }

    // 🧾 Registrar la acción
    log_activity(
        $userId,
        "delete load",
        "Deleted load ID $loadId (company $companyId) and its loaded products.",
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

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;