<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

if (empty($_SESSION["sc_UserId"])) {
    $response = [
        "success" => false,
        "message" => "User data not found",
        "data" => []
    ];
    exit;
}

$response = [
    "success" => false,
    "message" => "User data not found",
    "data" => []
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    $userDataResponse = select_from("users", [
        "user_id",
        "parent_user",
        "name",
        "surname",
        "birthday",
        "phone",
        "email",
        "image",
        "signup_date",
        "company_id"
    ], ["user_id" => $userId], ["fetch_first" => true]);

    $userData = json_decode($userDataResponse, true);

    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }

    $userInfo = $userData["data"];
    $members = null;

    $altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];
    $planInfo = json_decode(select_from("users", [
        "members"
    ], ["user_id" =>  $altUser], ["fetch_first" => true]), true);

    if ($planInfo["success"] && isset($planInfo["data"]["members"])) {
        $members = $planInfo["data"];
    }

    $userInfo["members"] = $members["members"] ?? null;
   
    $response = [
        "success" => true,
        "message" => "User data retrieved successfully.",
        "data" => $userInfo
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>