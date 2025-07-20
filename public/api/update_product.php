<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "images/sys-img/error.gif",
	"redirect_url" => null
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'edit_data')) {
		throw new Exception("Access denied. You do not have permission to edit data.");
	}

	if (empty($_POST["edit_product_id"]) || !is_numeric($_POST["edit_product_id"])) {
		throw new Exception("Missing product ID.");
	}
	$productId = (int) $_POST["edit_product_id"];

	$productData = [
		"product_name"      => $_POST["edit_product_name"] ?? "",
        "product_type"      => (int) ($_POST["edit_product_type"] ?? 0),
        "product_mark"      => (int) ($_POST["edit_product_mark"] ?? 0),
        "product_model"     => (int) ($_POST["edit_product_model"] ?? 0),
        "product_sub_model" => (int) ($_POST["edit_product_sub_model"] ?? 0),
        "product_year"      => (int) ($_POST["edit_product_year"] ?? 0),
        "prise"             => is_numeric($_POST["edit_prise"] ?? null) ? $_POST["edit_prise"] : 0,
		"quantity"          => is_numeric($_POST["edit_quantity"] ?? null) ? $_POST["edit_quantity"] : 0,
		"min_quantity"      => isset($_POST["edit_min_quantity"]) && trim($_POST["edit_min_quantity"]) !== '' ? intval($_POST["edit_min_quantity"]) : 10,
        "description"       => $_POST["edit_description"] ?? "",
		"updated_at"		=> date("Y-m-d H:i:s")
	];

	if ($productData["product_name"] === '') {
		throw new Exception("Product name is required.");
	}

	if ($productData["product_type"] === 0) {
		throw new Exception("Product type is required.");
	}

	try {
		$imageName = handle_uploaded_image(
			"edit_Product_image",
			__DIR__ . "/../images/products",
			["jpg", "jpeg", "png", "webp"],
			"product",
			$userId
		);

		if ($imageName) {
			$productData["product_image"] = $imageName;
		}
	} catch (Exception $imgEx) {
		throw new Exception("Product image upload failed: " . $imgEx->getMessage());
	}

	$updateResult = json_decode(update_table("products", $productData, ["product_id" => $productId]), true);
	if (!$updateResult["success"]) {
		throw new Exception("Database update failed.");
	}

	// AQUI
	// triggerRealtimeNotification($userId);

	log_activity(
		$userId,
		"update_product",
		"User updated product info (ID: $productId).",
		"products",
		$productId
	);

	$response = [
		"success" => true,
		"message" => "Product updated successfully.",
		"img_gif" => "images/sys-img/loading1.gif",
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