<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Accept");

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

    $requiredFields = ["name", "surname", "email", "password"];
    $data = [];

	foreach ($requiredFields as $field) {
		if (empty($_POST[$field])) {
			throw new Exception("The $field field is required.");
		}
		$data[$field] = trim($_POST[$field]);
	}

    $data["status"] = 1;
    $data["username"] = strtolower($data["name"] . "_" . $data["surname"]);
    $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
    $data["verified"] = 0;
    $data["signup_date"] = date("Y-m-d H:i:s");
    $data["rank"] = 3;
    $data["package_id"] = 1;
    $data["status_by_admin"] = 1;

    $emailCheckResponse = select_from("users", ["user_id"], ["email" => $data["email"]], ["fetch_first" => true]);
    $emailCheck = json_decode($emailCheckResponse, true);

    if ($emailCheck && $emailCheck["success"] && !empty($emailCheck["data"])) {
        throw new Exception("The email is already registered.");
    }

    $user_ip = getUserIP();

    if (tooManyEmailsFromIP($user_ip)) {
        throw new Exception("Too many attempts. Please try later.");
    }

    $insertResponse = insert_into("users", $data, ["id" => "user_id"]);
    $insertResult = json_decode($insertResponse, true);

    if (!$insertResult["success"] || empty($insertResult["id"])) {
        throw new Exception("Error inserting into database.");
    }

    $userId = (int)$insertResult["id"];
    $verifyToken = bin2hex(random_bytes(32));
    
    $user_location = getLocationByIP($user_ip);
    $deviceType = getDeviceType();
    $deviceName = getDeviceName();

    $tokenInsertResponse = insert_into("user_tokens", [
        "user_id"       => $userId,
        "token"         => $verifyToken,
        "status"        => "active",
        "ip_address"    => $user_ip,
        "device_type"   => $deviceType,
        "device_name"   => $deviceName,
        "location"      => $user_location,
        "expires_at"    => date("Y-m-d H:i:s", strtotime("+24 hours"))
    ]);

    $tokenInsertResult = json_decode($tokenInsertResponse, true);

    if (!$tokenInsertResult["success"]) {
        throw new Exception("Error inserting verification token: " . ($tokenInsertResult["message"] ?? 'unknown'));
    }

    $verifyLink = "https://allstockcontrol.com/api/verify_email.php?token={$verifyToken}";

    $emailContent = "
        Hi <strong>{$data['name']}</strong>,<br><br>

        Thank you for registering at <strong>AllStockControl</strong>.<br><br>

        To activate your account, click here:<br><br>

        <a href='{$verifyLink}'
            style='display:inline-block; padding:12px 22px;
            background:#2e86de; color:#fff;
            text-decoration:none; border-radius:4px;
        '>
            Verify my account
        </a>

        <br><br>
        <p class='info'>This link is valid for 24 hours.</p>
    ";

    if (!sendSystemEmail("no-reply@allstockcontrol.com", $data["email"], "Verifica tu cuenta", $emailContent)) {
        throw new Exception("User created but verification email failed.");
    }


    $response = [
        "success" => true,
        "message" => "Data received successfully",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => "../stock.php"
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

// Responder con JSON
echo json_encode($response);
exit;