<?php
require_once('../logic/stock_be.php');

global $sql;
$sql = get_pg_connection();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$response = [
    "success" => false,
    "message" => "Access denied",
    "data" => [
        "can_access" => false
    ]
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed.");
    }

    if (empty($_SESSION["sc_UserId"])) {
        throw new Exception("No user is logged in.");
    }

    $userId = $_SESSION["sc_UserId"];

    if (empty($_GET["service_name"])) {
        throw new Exception("Missing 'service_name' parameter.");
    }

    $serviceName = pg_escape_string($sql, $_GET["service_name"]);

    $rightsResponse = select_from(
        "service_rights",
        ["can_access"],
        [
            "user_id" => $userId,
            "service_name" => $serviceName
        ],
        ["fetch_first" => true]
    );

    $data = json_decode($rightsResponse, true);

    if (
        !empty($data["success"]) &&
        $data["success"] &&
        !empty($data["data"]) &&
        isset($data["data"]["can_access"]) &&
        (int)$data["data"]["can_access"] === 1
    ) {
        $response = [
            "success" => true,
            "message" => "Access granted for service: $serviceName",
            "data" => [
                "can_access" => true
            ]
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Access denied for service: $serviceName",
            "data" => [
                "can_access" => false
            ]
        ];
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;