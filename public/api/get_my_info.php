<?php
$allowed_origins = [
    "http://localhost:3000",            // entorno local React
    "https://www.allstockcontrol.com"   // dominio real
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    // bloquea orígenes no autorizados
    header("Access-Control-Allow-Origin: https://www.allstockcontrol.com");
}

header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// Manejar preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once('../logic/stock_be.php');

$response = [
    "success" => false,
    "message" => "User data not found",
    "data" => []
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;

    if (!$userId) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            $token = trim(substr($authHeader, 7));

            // 🔹 Aquí verificas tu token (ajústalo a tu lógica de login.php)
            // Si tu token guarda el user_id en base64 o JWT, aquí lo validas
            // Ejemplo simple (ajústalo a tu sistema real):
            $userId = verifyAuthToken($token); // función que devuelve el user_id o null
        }
    }

    if (empty($userId)) {
        throw new Exception("User not authenticated.");
    }

    $userDataResponse = select_from("users", [
        "user_id",
        "parent_user",
        "name",
        "surname",
        "birthday",
        "country_code",
        "phone",
        "email",
        "image",
        "signup_date",
        "company_id",
        "package_id"
    ], ["user_id" => $userId], ["fetch_first" => true]);

    $userData = json_decode($userDataResponse, true);

    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }

    $userInfo = $userData["data"];

    $altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];
    $planInfo = json_decode(select_from("users", [
        "package_id"
    ], ["user_id" =>  $altUser], ["fetch_first" => true]), true);

    if ($planInfo["success"] && isset($planInfo["data"]["package_id"])) {
        $packageId = $planInfo["data"]["package_id"] ?? null;
    }

    if (!empty($packageId)) {
        $packageInfo = json_decode(select_from("packages", [
            "package_id",
            "package_name",
            "members_limit",
            "admins_limit",
            "branch_affiliate_limit",
            "products_limit",
            "package_duration"
        ], ["package_id" =>  $packageId], ["fetch_first" => true]), true);

        if ($packageInfo["success"] && isset($packageInfo["data"])) {
            $userInfo["package_info"] = $packageInfo["data"];
        }
    } else {
        $userInfo["package_info"] = null;
    }
   
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