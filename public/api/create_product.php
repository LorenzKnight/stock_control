<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

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

    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    $productName     = trim($_POST["product_name"] ?? '');
    $productType     = intval($_POST["product_type"] ?? 0);
    $productMark     = intval($_POST["product_mark"] ?? 0);
    $productModel    = intval($_POST["product_model"] ?? 0);
    $productSubModel = intval($_POST["product_sub_model"] ?? 0);
    $productYear     = intval($_POST["product_year"] ?? '');
    $productPrise    = trim($_POST["prise"] ?? '');
    $description     = trim($_POST["description"] ?? '');

    if ($productName === '') {
        throw new Exception("Product name are required.");
    }

    if ($productType === 0) {
        throw new Exception("Product type are required.");
    }

	$companyResult = select_from("companies", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]);
	$companyData = json_decode($companyResult, true);

	if (!$companyData["success"] || empty($companyData["data"]["company_id"])) {
		throw new Exception("Company ID not found for this user.");
	}

	$companyId = intval($companyData["data"]["company_id"]);

    $imageName = null;
    if (!empty($_FILES["Product_image"]["name"])) {
        $uploadDir = "../images/products/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($_FILES["Product_image"]["name"], PATHINFO_EXTENSION);
        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (!in_array(strtolower($ext), $allowed)) {
            throw new Exception("Invalid file type for product image.");
        }

        $imageName = "product_user_{$userId}_" . time() . "." . $ext;
        $targetFile = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES["Product_image"]["tmp_name"], $targetFile)) {
            throw new Exception("Failed to upload product image.");
        }
    }

    $insertData = [
        "create_by"         => $userId,
        "company_id"        => $companyId,
        "product_name"      => $productName,
        "product_type"      => $productType,
        "product_mark"      => $productMark,
        "product_model"     => $productModel,
        "product_sub_model" => $productSubModel,
        "product_year"      => $productYear,
        "prise"				=> $productPrise,
        "description"       => $description,
        "status"            => 1,
        "created_at"        => date("Y-m-d H:i:s")
    ];

    if ($imageName) {
        $insertData["product_image"] = $imageName;
    }

    $insertResponse = insert_into("products", $insertData, ["id" => "product_id"]);
    $insertResult = json_decode($insertResponse, true);

    if (!$insertResult["success"]) {
        throw new Exception("Error saving product data.");
    }

    log_activity(
        $userId,
        "create_product",
        "User added a new product: {$productName}",
        "products",
        $insertResult["id"] ?? null
    );

    $response = [
        "success" => true,
        "message" => "Product created successfully!",
        "img_gif" => "../images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "../images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;
?>