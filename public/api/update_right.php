<?php
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
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $editorId = $authUser["user_id"] ?? null;
    if (!$editorId) throw new Exception("Unauthorized access.");

    $rightId     = intval($_POST["edit_right_id"] ?? 0);
    $serviceName = trim($_POST["edit_service_name"] ?? "");
    $canAccess   = isset($_POST["edit_can_access"]) && $_POST["edit_can_access"] == 1 ? 1 : 0;

    if ($rightId <= 0) throw new Exception("Invalid or missing right ID.");
    if (empty($serviceName)) throw new Exception("Service name is required.");

    $check = json_decode(select_from(
        "service_rights",
        ["right_id", "user_id", "service_name"],
        ["right_id" => $rightId],
        ["fetch_first" => true]
    ), true);

    if (empty($check["success"]) || empty($check["data"])) {
        throw new Exception("Service right not found or already deleted.");
    }

    $userId = intval($check["data"]["user_id"]);

    $duplicateCheck = json_decode(select_from(
        "service_rights",
        ["right_id"],
        [
            "user_id" => $userId,
            "service_name" => $serviceName
        ]
    ), true);

    if (!empty($duplicateCheck["success"]) && !empty($duplicateCheck["data"])) {
        foreach ($duplicateCheck["data"] as $dup) {
            if (intval($dup["right_id"]) !== $rightId) {
                throw new Exception("This user already has a right with the same service name.");
            }
        }
    }

    $update = update_table("service_rights", [
        "service_name" => $serviceName,
        "can_access"   => $canAccess
    ], [
        "right_id" => $rightId
    ]);

    $updateResult = json_decode($update, true);
    if (empty($updateResult["success"]) || !$updateResult["success"]) {
        throw new Exception("Failed to update user right. Please try again.");
    }

    log_activity(
        $editorId,
        "update user right",
        "Updated user right '{$serviceName}' (ID: {$rightId}) — can_access={$canAccess}",
        "service_rights",
        $rightId
    );

    // 👥 Buscar colaboradores que dependen del usuario principal
    $collaborators = json_decode(select_from(
        "users",
        ["user_id"],
        ["parent_user" => $userId]
    ), true);

    if (!empty($collaborators["success"]) && !empty($collaborators["data"])) {
        foreach ($collaborators["data"] as $col) {
            $collabId = intval($col["user_id"]);
            if ($collabId <= 0) continue;

            // 🔍 Verificar si el colaborador ya tiene ese right
            $collabRight = json_decode(select_from(
                "service_rights",
                ["right_id"],
                [
                    "user_id"      => $collabId,
                    "service_name" => $serviceName
                ],
                ["fetch_first" => true]
            ), true);

            if (!empty($collabRight["success"]) && !empty($collabRight["data"])) {
                // 🛠 Actualizar el derecho existente
                update_table("service_rights", [
                    "service_name" => $serviceName,
                    "can_access" => $canAccess
                ], [
                    "right_id" => intval($collabRight["data"]["right_id"])
                ]);

                log_activity(
                    $editorId,
                    "update collaborator right",
                    "Updated collaborator right '{$serviceName}' (user_id={$collabId}) — can_access={$canAccess}",
                    "service_rights",
                    intval($collabRight["data"]["right_id"])
                );
            } else {
                // ➕ Si no existe, crearlo
                insert_into("service_rights", [
                    "user_id"      => $collabId,
                    "service_name" => $serviceName,
                    "can_access"   => $canAccess,
                    "create_by"    => $editorId,
                    "created_at"   => date("Y-m-d H:i:s")
                ]);

                log_activity(
                    $editorId,
                    "auto-create collaborator right",
                    "Created new right '{$serviceName}' for collaborator (user_id={$collabId}) — can_access={$canAccess}",
                    "service_rights",
                    null
                );
            }
        }
    }

    $response = [
        "success" => true,
        "message" => "User right updated successfully.",
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