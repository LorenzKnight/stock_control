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

    $name = trim($_POST['user_name'] ?? '');
    $surname = trim($_POST['user_surname'] ?? '');
    $birthday = trim($_POST['user_birthday'] ?? '');
    $phone = trim($_POST['user_phone'] ?? '');
    $email = trim($_POST['user_email'] ?? '');

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

    try {
		$imageName = handle_uploaded_image(
			"image",
			__DIR__ . "/../images/profile",
			["jpg", "jpeg", "png", "webp"],
			"profile",
			$userId
		);

		if ($imageName) {
			$updateData["image"] = $imageName;
		}
	} catch (Exception $imgEx) {
		throw new Exception("Profile image upload failed: " . $imgEx->getMessage());
	}

    $updateResponse = update_table("users", $updateData, ["user_id" => $userId]);
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