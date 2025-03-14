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

    $companyResponse = select_from("companies", [
        "company_name",
        "organization_no",
        "company_address",
        "company_phone",
        "company_logo"
    ], ["user_id" => $userId], ["fetch_first" => true]);

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