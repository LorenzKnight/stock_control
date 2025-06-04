<?php
require_once('../logic/stock_be.php'); // Ajusta la ruta si es necesario

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Could not fetch roles",
    "data" => []
];

try {
    $result = json_decode(select_from("roles", ["role_id", "role_name"], [], ["order_by" => "role_id"]), true);

    if (!$result || !$result["success"]) {
        throw new Exception($result["message"] ?? "No roles found");
    }

    $roles = [];
    foreach ($result["data"] as $row) {
        $roles[$row["role_id"]] = $row["role_name"];
    }

    $response = [
        "success" => true,
        "message" => "Roles fetched successfully",
        "data" => $roles
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>