<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success" => false,
    "message" => "Error fetching data",
    "users" => []
];

try {
    if (!isset($_SESSION["sc_UserId"])) {
        throw new Exception("No user is logged in.");
    }

    $userId = $_SESSION["sc_UserId"];

    $userResponse = select_from("users", [
        "user_id",
        "name",
        "surname",
        "email",
        "phone",
        "signup_date",
        "members"
    ], ["user_id" => $userId], ["fetch_first" => true]);

    $userData = json_decode($userResponse, true);

    if ($userData["success"] && !empty($userData["data"])) {
        $response["success"] = true;
        $response["message"] = "Users retrieved successfully";
        $response["users"] = $userData["data"];
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