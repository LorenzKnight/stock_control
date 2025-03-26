<?php
require_once('../logic/stock_be.php');
header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "No subcategories found",
    "data" => []
];

try {
    $markId = $_GET["mark_id"] ?? null;
    if (!$markId || !is_numeric($markId)) {
        throw new Exception("Invalid mark ID.");
    }

    $categoriesResponse = select_from("category", [
        "category_id",
        "category_name"
    ], [
        "cat_parent_sub" => $markId,
        "sub_parent" => null
    ], [
        "order_by" => "category_name"
    ]);

    $categories = json_decode($categoriesResponse, true);

    if (!$categories["success"] || empty($categories["data"])) {
        throw new Exception("No subcategories available for this mark.");
    }

    $response = [
        "success" => true,
        "message" => "Subcategories loaded successfully.",
        "data" => $categories["data"]
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>