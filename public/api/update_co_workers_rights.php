<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Unable to update access rights",
    "data" => []
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed.");
    }

    // Auth
    $authUser = requireAuth();
    $userId = $authUser["user_id"] ?? null;

    if (!$userId) {
        throw new Exception("Unauthorized access.");
    }

    // Get POST values
    $targetUserId = $_POST["user_id"] ?? null;
    $shippingAccess = isset($_POST["shipping_access"]) ? intval($_POST["shipping_access"]) : 0;

    if (!$targetUserId || !is_numeric($targetUserId)) {
        throw new Exception("Invalid user ID.");
    }

    // Validate parent_user
    $userInfoRaw = select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]);
    $userInfo = json_decode($userInfoRaw, true);

    if (!$userInfo["success"] || empty($userInfo["data"])) {
        throw new Exception("Unable to validate parent user.");
    }

    $parent = $userInfo["data"]["parent_user"] ?: $userId;

    // Validate user belongs to this parent
    $targetValidationRaw = select_from("users", ["user_id"], [
        "user_id" => $targetUserId,
        "parent_user" => $parent
    ], ["fetch_first" => true]);

    $targetValidation = json_decode($targetValidationRaw, true);

    if (!$targetValidation["success"] || empty($targetValidation["data"])) {
        throw new Exception("This user does not belong to you.");
    }

    $updatedPermissions = [];

    foreach ($_POST as $key => $value) {

        if ($key === "user_id") continue; // skip user_id

        $accessName = $key;
        $canAccess = intval($value) === 1 ? 1 : 0;

        // Check if permission exists
        $existsRaw = select_from(
            "access_rights",
            ["access_name", "can_access"],
            ["user_id" => $targetUserId, "access_name" => $accessName],
            ["fetch_first" => true]
        );

        $exists = json_decode($existsRaw, true);

        if ($exists["success"] && !empty($exists["data"])) {
            // UPDATE
            update_table("access_rights", [
                "can_access" => $canAccess
            ], [
                "user_id" => $targetUserId,
                "access_name" => $accessName
            ]);

        } else {
            // INSERT
            insert_into("access_rights", [
                "user_id" => $targetUserId,
                "access_name" => $accessName,
                "create_by" => $userId,
                "can_access" => $canAccess
            ]);
        }

        $updatedPermissions[$accessName] = $canAccess;
    }

    $response = [
        "success" => true,
        "message" => "Access rights updated successfully.",
        "data" => $updatedPermissions
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;