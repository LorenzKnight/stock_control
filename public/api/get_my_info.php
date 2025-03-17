<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "User data not found",
    "data" => []
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    $userDataResponse = select_from("users", [
        "name",
        "surname",
        "birthday",
        "phone",
        "email",
        "image"
    ], ["user_id" => $userId], ["fetch_first" => true]);

    $userData = json_decode($userDataResponse, true);

    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }

    $response = [
        "success" => true,
        "message" => "User data retrieved successfully.",
        "data" => $userData["data"]
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>