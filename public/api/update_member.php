<?php
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

    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    $targetUserId = intval($_POST["edit_user_id"] ?? 0);
    if ($targetUserId <= 0) throw new Exception("Invalid user ID.");

    $name     = trim($_POST["edit_name"] ?? '');
    $surname  = trim($_POST["edit_surname"] ?? '');
    $birthday = trim($_POST["edit_birthday"] ?? '');
    $phone    = trim($_POST["edit_phone"] ?? '');
    $email    = trim($_POST["edit_email"] ?? '');
    $rank     = intval($_POST["edit_rank"] ?? 0);

    if ($name === '' || $surname === '' || $email === '') {
        throw new Exception("Name, surname and email are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    $updateData = [
        "name" => $name,
        "surname" => $surname,
        "birthday" => $birthday,
        "phone" => $phone,
        "email" => $email,
        "rank" => $rank
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