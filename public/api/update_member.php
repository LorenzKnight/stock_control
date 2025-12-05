<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $userId = $authUser["user_id"] ?? null;
    if (!$userId) throw new Exception("User not authenticated.");

    $targetUserId = intval($_POST["edit_user_id"] ?? 0);
    if ($targetUserId <= 0) throw new Exception("Invalid user ID.");

    if ($targetUserId === $userId) {
        throw new Exception("You cannot change your own status.");
    }

    if (isset($_POST["toggle_only"]) && isset($_POST["status"])) {
        $status = intval($_POST["status"]);
        if (!in_array($status, [0,1])) {
            throw new Exception("Invalid status value.");
        }

        $updateResponse = update_table(
            "users",
            ["status" => $status],
            ["user_id" => $targetUserId]
        );

        $updateResult = json_decode($updateResponse, true);
        if (!$updateResult["success"]) {
            throw new Exception("Status update failed.");
        }

        if ($status == 0) {
            sendForceLogout($targetUserId);
        }

        log_activity(
            $userId,
            "toggle_user_status",
            "User status changed (ID: $targetUserId)",
            "users",
            $targetUserId
        );

        echo json_encode([
            "success" => true,
            "message" => "User status updated"
        ]);
        exit;
    }

    $name           = trim($_POST["edit_name"] ?? '');
    $surname        = trim($_POST["edit_surname"] ?? '');
    $birthday       = trim($_POST["edit_birthday"] ?? '');
    $countryCode    = trim($_POST["edit_member_country_code"] ?? '');
    $phone          = trim($_POST["edit_phone"] ?? '');
    $email          = trim($_POST["edit_email"] ?? '');
    $company        = intval($_POST["edit_company"] ?? 0);
    $rank           = intval($_POST["edit_rank"] ?? 0);
    $status         = isset($_POST["edit_status"]) ? 1 : 0;

    if ($name === '' || $surname === '' || $email === '') {
        throw new Exception("Name, surname and email are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    $updateData = [
        "name"          => $name,
        "surname"       => $surname,
        "birthday"      => $birthday,
        "country_code"  => $countryCode,
        "phone"         => $phone,
        "email"         => $email,
        "company_id"    => $company,
        "rank"          => $rank,
        "status"        => $status
    ];

    $updateResponse = update_table("users", $updateData, ["user_id" => $targetUserId]);
    $updateResult = json_decode($updateResponse, true);

    if (!$updateResult["success"]) {
        throw new Exception("Update failed.");
    }

    log_activity(
        $userId,
        "edit_secondary_user",
        "User edited co-worker (ID: $targetUserId)",
        "users",
        $targetUserId
    );

    $response = [
        "success" => true,
        "message" => "Co-worker info updated successfully!",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];
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