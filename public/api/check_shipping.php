<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    // 🔒 Autenticación con token JWT
    $authUser = requireAuth();
    $userId = $authUser["user_id"];
    $companyId = $authUser["company_id"] ?? null;

    // 🔍 Validar shipping_id recibido
    $shippingId = intval($_POST["shipping_id"] ?? 0);
    if ($shippingId <= 0) throw new Exception("Invalid shipping ID.");

    // 📍 Coordenadas opcionales
    $latitude = isset($_POST["latitude"]) ? floatval($_POST["latitude"]) : null;
    $longitude = isset($_POST["longitude"]) ? floatval($_POST["longitude"]) : null;

    $tokenData = json_decode(select_from(
        "user_tokens",
        ["location"],
        [
            "user_id" => $userId,
            "status"  => "active"
        ],
        [
            "order_by" => "created_at",
            "order_direction" => "DESC",
            "fetch_first" => true
        ]
    ), true);

    $checkpointName = "Scanned at checkpoint";
    if ($tokenData["success"] && !empty($tokenData["data"]["location"])) {
        $checkpointName = $tokenData["data"]["location"];
    }

    // 🔎 Obtener estado actual del envío
    $shippingInfo = json_decode(select_from("shippings", ["status"], ["shippings_id" => $shippingId], ["fetch_first" => true]), true);
    if (!$shippingInfo["success"] || empty($shippingInfo["data"])) {
        throw new Exception("Shipping not found.");
    }
    $currentStatus = intval($shippingInfo["data"]["status"]);

    if ($currentStatus >= 3) {
        throw new Exception("Shipping already delivered.");
    }

    $testMode = isset($_POST["test_mode"]) && $_POST["test_mode"] === "check_only";

    if ($testMode) {
        // 🧭 Verificar si este usuario ya escaneó este envío
        $exists = json_decode(select_from(
            "shipping_tracking",
            ["tracking_id"],
            ["shipping_id" => $shippingId, "scanned_by" => $userId],
            ["fetch_first" => true]
        ), true);

        if ($exists["success"] && !empty($exists["data"])) {
            throw new Exception("Already checked by this user.");
        }

        echo json_encode(["success" => true, "message" => "User can check this shipping."]);
        exit;
    }

    // 🧩 Insertar nuevo checkpoint
    $insert = insert_into("shipping_tracking", [
        "shipping_id"       => $shippingId,
        "checkpoint_name"   => $checkpointName,
        "status"            => $currentStatus,
        "scanned_by"        => $userId,
        "latitude"          => $latitude,
        "longitude"         => $longitude,
        "created_at"        => date("Y-m-d H:i:s")
    ]);

    $insertResult = json_decode($insert, true);
    if (!$insertResult["success"]) {
        throw new Exception("Tracking record failed to insert.");
    }

    // 🚚 Actualizar estado del envío si está pendiente
    if ($currentStatus < 2) {
		// ✅ Actualizar estado
		update_table(
			"shippings",
			["status" => 2],
			["shippings_id" => $shippingId]
		);

		// ✅ Validar company_id desde el token
		if (empty($companyId)) {
			throw new Exception("Company ID not found for user.");
		}

		// 🔎 1️⃣ Usuarios de la empresa con rank <= 4
		$rankUsersQuery = select_from(
			"users",
			["user_id"],
			[
				"company_id" => $companyId,
				"RAW" => "\"rank\" <= 4"
			]
		);

		$rankUsers = json_decode($rankUsersQuery, true)["data"] ?? [];

		// 🔎 2️⃣ Usuarios con permiso explícito
		$rightsUsersQuery = select_from(
			"service_rights",
			["user_id"],
			[
				"service_name" => "shipping_status_notice",
				"can_access"   => 1
			]
		);

		$rightsUsers = json_decode($rightsUsersQuery, true)["data"] ?? [];

		// 🔗 3️⃣ Unificar user_ids (sin duplicados)
		$allowedUserIds = array_unique(array_merge(
			array_column($rankUsers, "user_id"),
			array_column($rightsUsers, "user_id")
		));

		// 🔔 4️⃣ Enviar push SOLO a usuarios autorizados
		if (!empty($allowedUserIds)) {
			sendShippingStatusPush($shippingId, 2, $allowedUserIds, $userId);
		}
	}

    // 📝 Registrar actividad
    log_activity($userId, "check_shipping", "Shipping checked at $checkpointName", "shippings", $shippingId);

    $response = [
        "success"    => true,
        "message"    => "Shipping marked as checked successfully!",
        "checkpoint" => $checkpointName
    ];
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;