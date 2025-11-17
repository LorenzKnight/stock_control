<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $deleterId = $authUser["user_id"] ?? null;

    if (empty($deleterId)) {
        throw new Exception("Unauthorized access.");
    }

    $rightId = intval($_POST["right_id"] ?? 0);
    if ($rightId <= 0) {
        throw new Exception("Invalid or missing right ID.");
    }

    $check = json_decode(select_from(
        "service_rights",
        ["right_id", "service_name", "user_id"],
        ["right_id" => $rightId],
        ["fetch_first" => true]
    ), true);

    if (empty($check["success"]) || empty($check["data"])) {
        throw new Exception("Right not found or already deleted.");
    }

    $serviceName = $check["data"]["service_name"] ?? "Unknown";
    $userId = intval($check["data"]["user_id"]);

    $delete = delete_from("service_rights", ["right_id" => $rightId]);
    $deleteResult = json_decode($delete, true);

    if (empty($deleteResult["success"]) || !$deleteResult["success"]) {
        throw new Exception("Failed to delete right. Please try again.");
    }

    log_activity(
        $deleterId,
        "delete user right",
        "Deleted user right '{$serviceName}' (ID: {$rightId})",
        "service_rights",
        $rightId
    );

    $collaborators = json_decode(select_from(
        "users",
        ["user_id"],
        ["parent_user" => $userId]
    ), true);

    if (!empty($collaborators["success"]) && !empty($collaborators["data"])) {
        foreach ($collaborators["data"] as $col) {
            $collabId = intval($col["user_id"]);
            if ($collabId <= 0) continue;

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
                $collabRightId = intval($collabRight["data"]["right_id"]);

                $deleteCollab = delete_from("service_rights", ["right_id" => $collabRightId]);
                $deleteCollabResult = json_decode($deleteCollab, true);

                if (!empty($deleteCollabResult["success"])) {
                    log_activity(
                        $deleterId,
                        "delete collaborator right",
                        "Deleted collaborator right '{$serviceName}' (user_id={$collabId})",
                        "service_rights",
                        $collabRightId
                    );
                }
            }
        }
    }

    $response = [
        "success" => true,
        "message" => "User right deleted successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;