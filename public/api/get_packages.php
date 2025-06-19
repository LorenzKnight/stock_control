<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "packages" => [],
    "message" => "Unable to fetch packages"
];

try {
    // Obtener solo los paquetes activos (package_status = 1)
    $packagesResponse = select_from("packages", [
        // todos los campos estan disponibles
    ], ["package_status" => 1], [
        "order_by" => "members_limit",
		"order_direction" => "ASC"
    ]);

    $packagesData = json_decode($packagesResponse, true);

    if (!$packagesData["success"] || empty($packagesData["data"])) {
        throw new Exception("No active packages found");
    }

    $response["success"] = true;
    $response["packages"] = array_values($packagesData["data"]);
    $response["message"] = "Packages retrieved successfully";
} catch (Exception $e) {
    $response = [
        "success" => false,
        "packages" => [],
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;
?>