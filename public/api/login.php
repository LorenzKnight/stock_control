<?php
session_start();
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");

$supportedLangs = ['en', 'es', 'sv'];

$lang = $_POST['lang'] ?? $_GET['lang'] ?? '';
$lang = strtolower($lang);

if (!in_array($lang, $supportedLangs, true)) {
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
    $lang = in_array($browserLang, $supportedLangs, true) ? $browserLang : 'en';
}

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

    if (empty($_POST["login_email"]) || empty($_POST["login_password"])) {
        throw new Exception("Email and password are required.");
    }

    $email = pg_escape_string($sql, trim($_POST["login_email"]));
    $password = pg_escape_string($sql, trim($_POST["login_password"]));

    $userResponse = select_from("users", 
        ["user_id", "email", "verified", "rank", "password", "company_id", "parent_user", "status", "status_by_admin"], 
        ["email" => $email], 
        ["fetch_first" => true]
    );
    $userData = json_decode($userResponse, true);

    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("Incorrect credentials.");
    }

    $user = $userData["data"];
    $hashedPassword = $user["password"];

    if (!password_verify($password, $hashedPassword)) {
        throw new Exception("Contraseña incorrecta.");
    }

    if (intval($user["verified"]) !== 1) {
        throw new Exception("Please verify your email before logging in.");
    }

    // 🚫 Bloquear acceso si el usuario está inactivo
    if (intval($user["status"]) === 0) {
        throw new Exception("Your account is inactive. Contact your administrator.");
    }

    // 🚫 Bloquear acceso si un administrador lo ha desactivado (status_by_admin = 0)
    if (intval($user["status_by_admin"]) === 0) {
        throw new Exception("Your account has been disabled by the system. Please contact support.");
    }

    // 🏢 Garantizar que el usuario tenga una empresa predeterminada
    $companyId = isset($user["company_id"])
        ? intval($user["company_id"])
        : 0;

    $isMainUser =
    empty($user["parent_user"]) ||
    intval($user["parent_user"]) === 0;

    if ($companyId <= 0 && $isMainUser) {
        $userId = intval($user["user_id"]);

        if ($userId <= 0) {
            throw new Exception("Invalid user ID.");
        }

        if (!pg_query($sql, "BEGIN")) {
            throw new Exception("Could not start company creation transaction.");
        }

        try {
            $lockedUserData = json_decode(select_from("users",
                [
                    "user_id",
                    "company_id"
                ],
                [
                    "user_id" => $userId
                ],
                [
                    "fetch_first" => true,
                    "for_update" => true
                ]
            ), true);

            if (
                !is_array($lockedUserData) ||
                !array_key_exists("success", $lockedUserData)
            ) {
                throw new Exception(
                    "Invalid response while locking the user."
                );
            }

            if (empty($lockedUserData["success"])) {
                throw new Exception(
                    $lockedUserData["message"] ??
                    "Could not lock the user while initializing the company."
                );
            }

            $lockedUser = $lockedUserData["data"] ?? [];

            if (!$lockedUser) {
                throw new Exception("The user could not be found.");
            }

            $lockedCompanyId = intval(
                $lockedUser["company_id"] ?? 0
            );

            if ($lockedCompanyId > 0) {
                $companyId = $lockedCompanyId;
            } else {
                $existingCompanyData = json_decode(select_from("companies",
                    ["company_id"],
                    ["user_id" => $userId],
                    [
                        "order_by" => "company_id",
                        "order_direction" => "asc",
                        "limit" => 1,
                        "fetch_first" => true
                    ]
                ), true);

                if (!is_array($existingCompanyData) || !array_key_exists("success", $existingCompanyData)) {
                    throw new Exception("Invalid response while verifying the user's company.");
                }

                if (empty($existingCompanyData["success"]) && ($existingCompanyData["message"] ?? "") !== "No records found") {
                    throw new Exception($existingCompanyData["message"] ?? "Could not verify the user's company.");
                }

                if (!empty($existingCompanyData["success"]) && !empty($existingCompanyData["data"]["company_id"])) {
                    $companyId = intval($existingCompanyData["data"]["company_id"]);
                } else {
                    $createCompanyData = json_decode(insert_into("companies",
                        [
                            "user_id"         => $userId,
                            "company_type"    => null,
                            "company_name"    => "My Company",
                            "organization_no" => null,
                            "company_address" => null,
                            "country_code"    => null,
                            "company_phone"   => null,
                            "company_logo"    => null
                        ],
                        [
                            "id" => "company_id"
                        ]
                    ), true);

                    if (!is_array($createCompanyData) || empty($createCompanyData["success"])) {
                        throw new Exception($createCompanyData["message"] ?? "Could not create the default company.");
                    }

                    $companyId = intval($createCompanyData["id"] ?? 0);

                    if ($companyId <= 0) {
                        throw new Exception("The default company ID was not returned.");
                    }
                }

                $updateUserData = json_decode(update_table("users",
                    [
                        "company_id" => $companyId
                    ],
                    [
                        "user_id" => $userId
                    ]
                ), true);

                if (!is_array($updateUserData) || empty($updateUserData["success"])) {
                    throw new Exception($updateUserData["message"] ?? "Could not associate the company with the user.");
                }

                if (intval($updateUserData["count"] ?? 0) !== 1) {
                    throw new Exception("The company could not be assigned to the user.");
                }
            }

            if (!pg_query($sql, "COMMIT")) {
                throw new Exception("Could not complete company creation.");
            }

            $user["company_id"] = $companyId;

        } catch (Throwable $companyError) {
            pg_query($sql, "ROLLBACK");

            throw new Exception(
                "Could not initialize your company: " .
                $companyError->getMessage()
            );
        }
    }

    $issuedAt = time();
    $expiresAt = $issuedAt + JWT_EXPIRATION; // por defecto: 24h

    $payload = [
        "iss"		 => JWT_ISSUER,
        "iat"		 => $issuedAt,
        "exp"		 => $expiresAt,
        "user_id"	 => $user["user_id"],
        "email"		 => $user["email"],
        "rank"		 => $user["rank"],
		"company_id" => $user["company_id"] ?? null
    ];

    $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
    $expiresAtISO = date('Y-m-d H:i:s', $expiresAt);

    // 🔄 Refresh token (30 días)
    $refreshToken = bin2hex(random_bytes(64)); // ultra seguro
    $refreshExpiresAt = date(
        'Y-m-d H:i:s',
        time() + (60 * 60 * 24 * 30)
    );

    $deviceType = getDeviceType();
    $deviceName = getDeviceName();

    update_table(
        "user_tokens",
        ["status" => "revoked"],
        [
            "user_id"     => $user["user_id"],
            "device_type" => $deviceType,
            "status"      => "active"
        ]
    );

    $user_ip = getUserIP();
    $user_location = getLocationByIP($user_ip);

    $insertResponse = insert_into("user_tokens", [
		"user_id"               => $user["user_id"],
		"token"                 => $jwt,
        "refresh_token"         => $refreshToken,
		"status"                => "active",
		"ip_address"            => $user_ip,
		"device_type"           => $deviceType,
        "device_name"           => $deviceName,
		"location"              => $user_location,
		"created_at"            => date('Y-m-d H:i:s'),
		"expires_at"            => $expiresAtISO,
        "refresh_expires_at"    => $refreshExpiresAt
    ]);

    $insertData = json_decode($insertResponse, true);

    // Validar resultado
    if (empty($insertData["success"]) || !$insertData["success"]) {
        $errorMsg = $insertData["message"] ?? "Error saving token to database.";
        throw new Exception($errorMsg);
    }

    $_SESSION["sc_UserId"] = $user["user_id"];
    $_SESSION["sc_Mail"] = $user["email"];
    $_SESSION["sc_Nivel"] = $user["rank"];
	$_SESSION["sc_CompanyId"] = $user["company_id"] ?? null;

    $isMobile = isset($_POST["app_login"]) && $_POST["app_login"] === "true";

    $response = [
        "success" => true,
        "message" => "Logging in....",
        "token" => $jwt,
        "refresh_token" => $refreshToken,
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => $isMobile ? "" : "/" . $lang . "/profile"
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