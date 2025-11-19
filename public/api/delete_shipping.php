<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Deletion failed",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    // 🔒 Verificar permisos
    if (!check_user_permission($userId, 'platform_admin')) {
        throw new Exception("Access denied. You do not have permission to delete data.");
    }

    if (empty($_POST["shippings_id"])) {
        throw new Exception("Shipping ID is required.");
    }

    $shippingId = (int)$_POST["shippings_id"];

	$deleteImgResult = delete_image_from_record([
		"table"        => "shippings",
		"id_column"    => "shippings_id",
		"id_value"     => $shippingId,
		"image_column" => "shipping_img",
		"image_folder" => "images/shippings-code"
	]);

    // 🔍 Obtener company_id del usuario
	$userInfo = select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
	$companyId = json_decode($userInfo, true)["data"]["company_id"] ?? null;

    if (!$companyId) {
        throw new Exception("Company ID not found for user.");
    }

    // 🔎 Validar que el shipping pertenezca a la empresa del usuario
    $shippingInfo = json_decode(select_from(
        "shippings",
        ["company_id"],
        ["shippings_id" => $shippingId],
        ["fetch_first" => true]
    ), true);

    $shippingCompanyId = $shippingInfo["data"]["company_id"] ?? null;
    if (!$shippingCompanyId) {
        throw new Exception("Shipping not found.");
    }

    if ((int)$shippingCompanyId !== (int)$companyId) {
        throw new Exception("Access denied. This shipping does not belong to your company.");
    }

    // 🔍 Obtener loads asociados al shipping
    $loadsResult = json_decode(select_from("loads", ["load_id"], [
		"shippings_id" => $shippingId, 
		"company_id" => $companyId
	]), true);

    if ($loadsResult["success"] && !empty($loadsResult["data"])) {
        foreach ($loadsResult["data"] as $load) {
            $loadId = (int)$load["load_id"];

            // 🧹 1️⃣ Borrar los productos cargados dentro de cada load
            $deleteLoadedProducts = json_decode(delete_from("loaded_products", [
				"load_id" => $loadId
			]), true);

            if (!$deleteLoadedProducts["success"]) {
                throw new Exception("Failed to delete loaded products for load ID: $loadId");
            }

            // 🧹 2️⃣ Borrar el load en sí (solo si pertenece a la misma compañía)
            $deleteLoad = json_decode(delete_from("loads", [
				"load_id" => $loadId, 
				"company_id" => $companyId
			]), true);

            if (!$deleteLoad["success"]) {
                throw new Exception("Failed to delete load ID: $loadId");
            }
        }
    }

    // 🧹 3️⃣ Borrar el shipping principal (solo si pertenece a la misma compañía)
    $deleteShipping = json_decode(delete_from("shippings",[
		"shippings_id" => $shippingId, 
		"company_id" => $companyId
	]), true);

    if (!$deleteShipping["success"]) {
        throw new Exception("Failed to delete shipping ID: $shippingId");
    }

    // 🧾 Registrar la acción
    log_activity(
        $userId,
        "delete shipping",
        "Shipping ID $shippingId and all associated loads and loaded products deleted for company $companyId.",
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

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;