<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "No categories found",
    "data" => []
];

try {
    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    $categoriesResponse = select_from("category", [
        "category_id",
        "category_name"
    ], [
        "cat_parent_sub" => null,
        "sub_parent" => null
    ], [
        "order_by" => "category_name"
    ]);

    $categories = json_decode($categoriesResponse, true);

    if (!$categories["success"] || empty($categories["data"])) {
        throw new Exception("No categories available.");
    }

    $response = [
        "success" => true,
        "message" => "Categories loaded successfully.",
        "data" => $categories["data"]
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>