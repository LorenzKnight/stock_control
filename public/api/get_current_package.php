<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "current_pack" => null,
    "message" => "Unable to fetch current package"
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) {
        throw new Exception("User not logged in");
    }

    $userResponse = select_from("users", ["members"], ["user_id" => $userId], ["fetch_first" => true]);
    $userData = json_decode($userResponse, true);

    if ($userData["success"] && !empty($userData["data"]["members"])) {
        $response["success"] = true;
        $response["current_pack"] = intval($userData["data"]["members"]);
        $response["message"] = "Current package retrieved";
    }
} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;
?>