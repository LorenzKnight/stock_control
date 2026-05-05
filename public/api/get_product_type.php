<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success"   => false,
    "message"   => "No company info found",
    "count"     => 0,
    "data"      => []
];

try {
    $authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;
// var_dump($authUser);
    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

    $userData = json_decode(select_from("users", ["parent_user"], ["user_id" => $userId], ["fetch_first" => true]), true);
    if (!$userData["success"] || empty($userData["data"])) {
        throw new Exception("No user data found.");
    }
    $userInfo = $userData["data"];

    $altUser = empty($userInfo["parent_user"] ?? null) ? $userId : $userInfo["parent_user"];

    $where = [
		"user_id" => $altUser
	];

    $selectCompany = $_GET["select_company"] ?? '';
    
    if (!empty($selectCompany) && is_numeric($selectCompany)) {
		$where["company_id"] = intval($selectCompany);
	}
    
    $companyResponse = select_from("product_type", [
        "product_type_id",
        "product_type_name",
        "company_id",
        "create_by",
        "created_at"
    ], $where, [
        "order_by" => "product_type_id",
        "order_direction" => "ASC"
    ]);

    $companyData = json_decode($companyResponse, true);

    if ($companyData["success"] && !empty($companyData["data"])) {
        $response = [
            "success"   => true,
            "message"   => "Product type info loaded.",
            "count"     => $companyData["count"],
            "data"      => array_values($companyData["data"])
        ];
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;