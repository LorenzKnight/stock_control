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

    if (isset($_GET["id"]) && $_GET["id"] !== "") {

        $targetId = intval($_GET["id"]);

        $userResponse = select_from(
            "users",
            [
                "user_id",
                "parent_user",
                "name",
                "surname",
                "email",
                "phone",
                "image",
                "rank",
                "status",
                "signup_date"
            ],
            ["user_id" => $targetId],
            ["fetch_first" => true]
        );

        $parsed = json_decode($userResponse, true);

        if (!$parsed["success"] || empty($parsed["data"])) {
            throw new Exception("User not found.");
        }

        // Obtener nombre del rol
        $rolesResponse = select_from("roles", ["role_id", "role_name"], [], []);
        $rolesData = json_decode($rolesResponse, true);

        $ranks = [];
        if ($rolesData["success"] && !empty($rolesData["data"])) {
            foreach ($rolesData["data"] as $role) {
                $ranks[$role["role_id"]] = $role["role_name"];
            }
        }

        $user = $parsed["data"];
        $user["rank_text"] = $ranks[$user["rank"]] ?? "Unknown role";
        $user["full_name"] = trim(($user["name"] ?? '') . ' ' . ($user["surname"] ?? ''));

        echo json_encode([
            "success" => true,
            "message" => "Single user loaded",
            "user"    => $user
        ]);
        exit;
    }

    $userData = json_decode(select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]), true);
	if (!is_array($userData) || !$userData["success"] || empty($userData["data"])) {
        throw new Exception("Error fetching user data.");
    }
	$userInfo = $userData["data"];

	$altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];

    $search = $_GET["search"] ?? '';
    $includeParent = isset($_GET["include_parent"]) && $_GET["include_parent"] == 1;

    if ($includeParent) {
        $where = [
            "OR" => [
                "parent_user" => $altUser,
                "user_id"     => $altUser
            ]
        ];
    } else {
        $where = [
            "parent_user" => $altUser
        ];
    }

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
        "parent_user",
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
            $user["full_name"] = trim(($user["name"] ?? '') . ' ' . ($user["surname"] ?? ''));
        }

        $response = [
            "success"   => true,
            "message"   => "Users retrieved successfully",
            "count"     => $users["count"],
            "users"     => $users["data"],
            "ranks"     => $ranks
        ];
    } else {
        throw new Exception("No users found.");
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

// Responder con JSON
echo json_encode($response);
exit;