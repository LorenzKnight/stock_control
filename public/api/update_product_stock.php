<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Unknown error",
	"data"    => null
];

try {

	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed.");
	}

	$authUser = requireAuth();
	$userId = $authUser["user_id"] ?? null;
	if (!$userId) throw new Exception("Unauthorized access.");

	// Obtener empresa del usuario
	$userData = json_decode(
		select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]),
		true
	);

	if (!$userData["success"] || empty($userData["data"])) {
		throw new Exception("Unable to verify user company.");
	}

	$userCompanyId = $userData["data"]["company_id"];

	// Inputs
	$productId = intval($_POST["product_id"] ?? 0);
	$amount    = intval($_POST["amount"] ?? 0);

	if ($productId <= 0) throw new Exception("Invalid product ID.");
	if ($amount <= 0) throw new Exception("Amount must be greater than 0.");

	// Verificar producto
	$productData = json_decode(
		select_from("products", ["company_id", "quantity"], ["product_id" => $productId], ["fetch_first" => true]),
		true
	);

	if (!$productData["success"] || empty($productData["data"])) {
		throw new Exception("Product not found.");
	}

	if ($productData["data"]["company_id"] != $userCompanyId) {
		throw new Exception("Access denied.");
	}

	$previousQty = intval($productData["data"]["quantity"]);

	// Actualizar stock
	$update = json_decode(update_table(
		"products",
		["quantity" => "quantity + {$amount}"],
		["product_id" => $productId]
	), true);

	if (!$update["success"]) {
		throw new Exception("Error updating stock.");
	}

	// Respuesta OK
	$response = [
		"success" => true,
		"message" => "Stock updated successfully.",
		"data" => [
			"product_id"      => $productId,
			"added_amount"    => $amount,
			"previous_stock"  => $previousQty,
			"new_stock"       => $previousQty + $amount
		]
	];

} catch (Exception $e) {
	$response = [
		"success" => false,
		"message" => $e->getMessage()
	];
}

echo json_encode($response);
exit;