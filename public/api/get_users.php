<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success" => false,
    "message" => "Error fetching data",
    "count" => 0,
    "users" => []
];

try {
    if (!isset($_SESSION["sc_UserId"])) {
        throw new Exception("No user is logged in.");
    }

    $userId = $_SESSION["sc_UserId"];

    // Obtener todos los usuarios
    $userResponse = select_from("users", [
        "user_id",
        "name",
        "surname",
        "email",
        "phone",
        "image",
        "rank",
        "status",
        "signup_date"
    ], ["parent_user" => $userId], [
        "order_by" => "user_id",
        "order_direction" => "ASC"
    ]);

    $users = json_decode($userResponse, true);

    if ($users["success"] && !empty($users["data"])) {
        $response["success"] = true;
        $response["message"] = "Users retrieved successfully";
        $response["count"] = $users["count"];
        $response["users"] = $users["data"];
    } else {
        throw new Exception("No users found.");
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Responder con JSON
echo json_encode($response);
exit;
?>