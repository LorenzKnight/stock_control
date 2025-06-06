<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Failed to retrieve permissions.",
    "permissions" => []
];

try {
    $permissionsResponse = select_from("permissions", [
        "permission_name"
    ],[],
    ["order_by" => "permission_id", "order_direction" => "ASC"]
    );

    $permissionsData = json_decode($permissionsResponse, true);

    if (!$permissionsData["success"] || empty($permissionsData["data"])) {
        throw new Exception("No permissions found.");
    }

    $permissions = array_map(function($item) {
        return $item["permission_name"];
    }, $permissionsData["data"]);

    $response["success"] = true;
    $response["permissions"] = $permissions;

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>