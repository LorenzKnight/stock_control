<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success" => false,
    "message" => "Access denied",
    "has_permission" => false
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed.");
    }

    $authUser = requireAuth();
    $userId = $authUser["user_id"] ?? null;

    if (!$userId) {
        throw new Exception("Unauthorized access. Invalid or missing token.");
    }

    if (empty($_GET["permission"])) {
        throw new Exception("Missing permission parameter.");
    }

    $permissionName = pg_escape_string($sql, $_GET["permission"]);

    $hasPermission = check_user_permission($userId, $permissionName);

    $response = [
        "success" => true,
        "has_permission" => $hasPermission,
        "message" => $hasPermission
            ? "User has permission."
            : "User does not have permission."
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;