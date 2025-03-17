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

    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $surname === '' || $email === '') {
        throw new Exception("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    $updateData = [
        "name" => $name,
        "surname" => $surname,
        "phone" => $phone,
        "email" => $email
    ];

    if ($birthday !== '') {
        $updateData["birthday"] = $birthday;
    }

    if (!empty($_FILES["image"]["name"])) {
        $uploadDir = "../images/profile/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $allowed = ["png", "jpg", "jpeg", "webp"];

        if (!in_array(strtolower($ext), $allowed)) {
            throw new Exception("Invalid file type for profile image.");
        }

        $newName = "profile_user_" . $userId . "_" . time() . "." . $ext;
        $targetFile = $uploadDir . $newName;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            throw new Exception("Failed to upload profile image.");
        }

        $updateData["image"] = $newName;
    }

    $updateResponse = update_table("users", $updateData, ["user_id" => $userId], ["echo_query" => false]);
    $updateResult = json_decode($updateResponse, true);

    if (!$updateResult["success"]) {
        throw new Exception("Update failed.");
    }

    $description = "User updated personal information.";
    if (!empty($updateData["image"])) {
        $description .= " Profile image updated.";
    }

    log_activity(
        $userId,
        "update_user_info",
        $description,
        "users",
        $userId
    );

    $response = [
        "success" => true,
        "message" => "Personal info updated successfully!",
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
?>