<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Invalid request",
	"img_gif" => "images/sys-img/error.gif",
	"redirect_url" => null
];
file_put_contents("debug_post.txt", print_r($_POST, true));
file_put_contents("debug_files.txt", print_r($_FILES, true));
try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	// Validar ID del producto
	if (empty($_POST["edit_product_id"])) {
		throw new Exception("Missing product ID.");
	}
	$productId = intval($_POST["edit_product_id"]);

	// Recoger los demás datos del formulario
	$productData = [
		"product_name"       => $_POST["edit_product_name"] ?? "",
        "product_type"       => (int) ($_POST["edit_product_type"] ?? 0),
        "product_mark"       => (int) ($_POST["edit_product_mark"] ?? 0),
        "product_model"      => (int) ($_POST["edit_product_model"] ?? 0),
        "product_sub_model"  => (int) ($_POST["edit_product_sub_model"] ?? 0),
        "product_year"       => (int) ($_POST["edit_product_year"] ?? 0),
        "prise"              => is_numeric($_POST["edit_prise"] ?? null) ? $_POST["edit_prise"] : 0,
        "description"        => $_POST["edit_description"] ?? ""
	];

	// Validar campos mínimos (puedes añadir más validaciones)
	if (empty($productData["product_name"])) {
		throw new Exception("Product name is required.");
	}

	// Procesar imagen si se envió una nueva
	if (!empty($_FILES["edit_Product_image"]["name"])) {
		$imgName = time() . "_" . basename($_FILES["edit_Product_image"]["name"]);
		$imgPath = "../images/products/" . $imgName;

		if (move_uploaded_file($_FILES["edit_Product_image"]["tmp_name"], $imgPath)) {
			$productData["product_image"] = $imgName;
		} else {
			throw new Exception("Failed to upload product image.");
		}
	}

	// Actualizar en la base de datos
	$where = ["product_id" => $productId];
	$updateResult = update_table("products", $productData, $where);
	$result = json_decode($updateResult, true);

	if (!$result["success"]) {
		throw new Exception("Database update failed.");
	}

	$response["success"] = true;
	$response["message"] = "Product updated successfully.";
	$response["img_gif"] = "images/sys-img/loading1.gif";
	$response["redirect_url"] = "";
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