<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "No users found",
	"data" => []
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed.");
    }

    $authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;

	if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

    $targetUserId = $_GET["user_id"] ?? null;

	if (!$targetUserId || !is_numeric($targetUserId)) {
		throw new Exception("Invalid user ID.");
	}

    $userInfoRaw = select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]);
    $userInfo = json_decode($userInfoRaw, true);

    if (!$userInfo["success"] || empty($userInfo["data"])) {
        throw new Exception("Unable to validate parent user.");
    }

    $parent = $userInfo["data"]["parent_user"] ?: $userId;

    $targetValidationRaw = select_from("users", ["user_id"], [
        "user_id" => $targetUserId,
        "parent_user" => $parent
    ], ["fetch_first" => true]);

    $targetValidation = json_decode($targetValidationRaw, true);

    if (!$targetValidation["success"] || empty($targetValidation["data"])) {
        throw new Exception("This user does not belong to you.");
    }

	$accessData = json_decode(select_from("access_rights",
        ["access_name", "can_access"],
        ["user_id" => $targetUserId],
        ["fetch_all" => true]
    ), true);

	if (!$accessData["success"]) {
        throw new Exception("Could not retrieve access rights.");
    }

    if (!isset($accessData["data"]) || !is_array($accessData["data"])) {
		$accessData["data"] = [];
	}

    $accessList = [];
    
    if (!empty($accessData["data"])) {
        foreach ($accessData["data"] as $row) {
            $accessList[$row["access_name"]] = (bool)$row["can_access"];
        }
    }

    $response = [
        "success" => true,
        "message" => "Access rights retrieved successfully",
        "data" => $accessList
    ];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;