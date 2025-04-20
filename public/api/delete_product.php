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

	// Validar ID del producto
	if (empty($_POST["product_id"]) || !is_numeric($_POST["product_id"])) {
		throw new Exception("Missing or invalid product ID.");
	}

	$productId = intval($_POST["product_id"]);
	$sessionUserId = $_SESSION["sc_UserId"] ?? null;

	if (!$sessionUserId) {
		throw new Exception("No authenticated user.");
	}

    $deleteImgResult = delete_image_from_record([
		"table"        => "products",
		"id_column"    => "product_id",
		"id_value"     => $productId,
		"image_column" => "product_image",
		"image_folder" => "images/products"
	]);

	if (!$deleteImgResult["success"]) {
		throw new Exception("Image deletion failed: " . $deleteImgResult["message"]);
	}

	$deleteResponse = delete_from("products", ["product_id" => $productId]);
	$deleteResult = json_decode($deleteResponse, true);

	if (!$deleteResult) {
		throw new Exception("Database error while deleting product.");
	}

	if (empty($deleteResult["count"])) {
		throw new Exception("No product found with the provided ID.");
	}

	log_activity(
		$sessionUserId,
		"delete_product",
		"User deleted a product (ID: {$productId}).",
		"products",
		$productId
	);

	$response["success"] = true;
	$response["message"] = "Product deleted successfully.";
	$response["img_gif"] = "../images/sys-img/loading1.gif";
	$response["redirect_url"] = "";

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>