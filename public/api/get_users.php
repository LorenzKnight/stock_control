<?php
require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Error fetching data",
    "count" => 0,
    "users" => [],
    "ranks" => []
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed.");
    }

    $authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;

    if (!$userId) {
        throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    $userData = json_decode(select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]), true);
	if (!is_array($userData) || !$userData["success"] || empty($userData["data"])) {
        throw new Exception("Error fetching user data.");
    }
	$userInfo = $userData["data"];

	$altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];

    $search = $_GET["search"] ?? '';

    $where = [
        "parent_user" => $altUser
	];

	$selectCompany = $_GET["select_company"] ?? null;
	if ($selectCompany !== null && $selectCompany !== '') {
		$where["company_id"] = $selectCompany;
	}

    if (!empty($search)) {
		$where["OR"] = [
			"name ILIKE"     => "%{$search}%",
			"surname ILIKE"  => "%{$search}%",
			"email ILIKE"    => "%{$search}%",
			"username ILIKE" => "%{$search}%"
		];
	}

    // Obtener todos los usuarios
    $userResponse = select_from(
    "users",
    [
        "user_id",
        "name",
        "surname",
        "email",
        "phone",
        "image",
        "rank",
        "status",
        "signup_date"
    ], $where,
    [
        "order_by" => "user_id",
        "order_direction" => "ASC",
        "fetch_all" => true
    ]);

    $users = json_decode($userResponse, true);
    
    $minRoleId = 1; // Cambia esto según el rol mínimo que quieras mostrar
    $rolesResponse = select_from("roles", ["role_id", "role_name"], [
        "role_id" => ["condition" => ">=", "value" => $minRoleId]
    ], [
        "order_by" => "role_id",
        "order_direction" => "ASC"
    ]);

    $rolesData = json_decode($rolesResponse, true);
    $ranks = [];

    if ($rolesData["success"] && !empty($rolesData["data"])) {
        foreach ($rolesData["data"] as $role) {
            $ranks[$role["role_id"]] = $role["role_name"];
        }
    }

    if ($users["success"] && !empty($users["data"])) {
        foreach ($users["data"] as &$user) {
            $user["rank_text"] = isset($ranks[$user["rank"]]) ? $ranks[$user["rank"]] : "Unknown role";
            $user["full_name"]      = trim(($user["name"] ?? '') . ' ' . ($user["surname"] ?? ''));
        }

        $response["success"] = true;
        $response["message"] = "Users retrieved successfully";
        $response["count"] = $users["count"];
        $response["users"] = $users["data"];
        $response["ranks"] = $ranks;
    } else {
        throw new Exception("No users found.");
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Responder con JSON
echo json_encode($response);
exit;