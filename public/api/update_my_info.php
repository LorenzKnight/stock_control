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
    $userId = intval($authUser["user_id"] ?? 0);

    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    $name = trim($_POST['user_name'] ?? '');
    $surname = trim($_POST['user_surname'] ?? '');
    $birthday = trim($_POST['user_birthday'] ?? '');
    $countryCode = trim($_POST['country_code'] ?? '');
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
        "country_code" => $countryCode,
        "phone" => $phone,
        "email" => $email
    ];

    if ($birthday !== '') {
        $updateData["birthday"] = $birthday;
    }

    $previousData = json_decode(select_from(
		"users",
		["image"],
		["user_id" => $userId],
        ["fetch_first" => true]
	),true);

	$previousImage = null;
	if (!empty($previousData["success"]) && !empty($previousData["data"]) && isset($previousData["data"]["image"])) {
		$tmp = trim((string)$previousData["data"]["image"]);
		$previousImage = $tmp !== '' ? $tmp : null;
	}

    try {
		$imageName = handle_uploaded_image(
			"image",
			__DIR__ . "/../images/profile",
			"profile",
			$userId,
            ["jpg", "jpeg", "png", "webp"],
            $previousImage
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