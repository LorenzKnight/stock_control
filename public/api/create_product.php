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
    $productQuantity        = is_numeric($_POST["quantity"] ?? null) ? intval($_POST["quantity"]) : 0;
    $productMinQuantity     = isset($_POST["min_quantity"]) && trim($_POST["min_quantity"]) !== '' ? intval($_POST["min_quantity"]) : 10;
    $description     		= trim($_POST["description"] ?? '');
    $confirmUpdate          = $_POST["confirm_update"] ?? 'false';

    if ($productName === '') throw new Exception("Product name is required.");
    if ($productType === 0) throw new Exception("Product type is required.");
    if ($productQuantity < 0) throw new Exception("Quantity must be 0 or more.");
    if ($productPrise < 0) throw new Exception("Price must be 0 or more.");

    $imageName = null;
	try {
		$imageName = handle_uploaded_image(
			"product_image",
			__DIR__ . "/../images/products/",
            "product",
			$userId,
            ["jpg", "jpeg", "png", "webp"],
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
        "min_quantity"      => $productMinQuantity,
		"currency"			=> $productPriceCurrency,
        "prise"				=> $productPrise,
        "description"       => $description,
        "status"            => 1,
        "created_at"        => date("Y-m-d H:i:s")
    ];
	
    if ($imageName) {
        $insertData["product_image"] = $imageName;
    }

    $productRes = json_decode(select_from("products", ["product_id", "quantity"], [
        "company_id" => $companyId,
        "product_mark" => $productMark,
        "product_model" => $productModel,
        "product_sub_model" => $productSubModel,
        "product_year" => $productYear
    ], ["fetch_first" => true]), true);

    $existingProduct = $productRes["data"];

    if ($productRes["success"] && !empty($existingProduct) && $confirmUpdate !== 'true') {
        $response = [
            "success" => false,
            "needs_confirmation" => true,
            "message" => "This product already exists. Do you want to update the quantity?",
            "existing_product_id" => $existingProduct["product_id"],
            "existing_quantity" => $existingProduct["quantity"]
        ];
        echo json_encode($response);
        exit;
    }

    if ($productRes["success"] && !empty($existingProduct) && $confirmUpdate === 'true') {
        $updateResult = json_decode(update_table("products", [
            "quantity" => $existingProduct["quantity"] + $productQuantity,
            "updated_at" => date("Y-m-d H:i:s")
        ], ["product_id" => $existingProduct["product_id"]]), true);

        if (!$updateResult["success"]) {
            throw new Exception("Error updating existing product quantity.");
        }

        $finalProductId = $existingProduct["product_id"];
    } else {
        $insertResult = json_decode(insert_into("products", $insertData, ["id" => "product_id"]), true);

        if (!$insertResult["success"]) {
            throw new Exception("Error saving product data.");
        }

        $finalProductId = $insertResult["id"];
    }

    log_activity(
        $userId,
        "create_product",
        "User added a new product: {$productName}",
        "products",
        $finalProductId
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