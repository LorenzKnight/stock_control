<?php
require_once('../logic/stock_be.php');

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

    if (empty($_SESSION["sc_UserId"])) {
        throw new Exception("No user is logged in.");
    }

    $userId = $_SESSION["sc_UserId"];

    if (empty($_GET["permission"])) {
        throw new Exception("Missing permission parameter.");
    }

    $permissionName = pg_escape_string($_GET["permission"]);

    $hasPermission = check_user_permission($userId, $permissionName);

    $response = [
        "success" => true,
        "has_permission" => $hasPermission,
        "message" => $hasPermission ? "User has permission." : "User does not have permission."
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>