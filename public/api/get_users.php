<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success" => false,
    "message" => "Error fetching data",
    "count" => 0,
    "users" => [],
    "ranks" => []
];

try {
    if (!isset($_SESSION["sc_UserId"])) {
        throw new Exception("No user is logged in.");
    }

    $userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

    $where = [
		"parent_user" => ["condition" => "IS NOT NULL"]
	];

	$selectCompany = $_GET["select_company"] ?? null;
	if ($selectCompany !== null && $selectCompany !== '') {
		$where["company_id"] = $selectCompany;
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
?>