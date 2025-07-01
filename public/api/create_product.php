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

    if (!check_user_permission($userId, 'create_data')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

    $companyId              = intval($_POST["company_id"] ?? '');
    $productName     		= trim($_POST["product_name"] ?? '');
    $productType     		= intval($_POST["product_type"] ?? 0);
    $productMark     		= intval($_POST["product_mark"] ?? 0);
    $productModel    		= intval($_POST["product_model"] ?? 0);
    $productSubModel		= intval($_POST["product_sub_model"] ?? 0);
    $productPriceCurrency   = trim($_POST["currency"] ?? '');
    $productPrise         	= trim($_POST["prise"] ?? '');
    $productYear     		= intval($_POST["product_year"] ?? '');
    $productQuantity 		= trim($_POST["quantity"] ?? '');
    $description     		= trim($_POST["description"] ?? '');

    if ($productName === '') {
        throw new Exception("Product name are required.");
    }

    if ($productType === 0) {
        throw new Exception("Product type are required.");
    }

    $imageName = null;
	try {
		$imageName = handle_uploaded_image(
			"product_image",
			__DIR__ . "/../images/products/",
			["jpg", "jpeg", "png", "webp"],
            "product",
			$userId
		);
	} catch (Exception $ex) {
		throw new Exception("Image upload failed: " . $ex->getMessage());
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
		"quantity"          => $productQuantity,
		"currency"			=> $productPriceCurrency,
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