<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "No company info found",
    "data" => []
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    $userData = json_decode(select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]), true);
    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }
    $userInfo = $userData["data"];

    $altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];
    
    $companyResponse = select_from("companies", [
        "company_name",
        "organization_no",
        "company_address",
        "company_phone",
        "company_logo"
    ], ["user_id" => $altUser], ["fetch_first" => true]);

    $companyData = json_decode($companyResponse, true);

    if ($companyData["success"] && !empty($companyData["data"])) {
        $response["success"] = true;
        $response["message"] = "Company info loaded.";
        $response["data"] = $companyData["data"];
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>