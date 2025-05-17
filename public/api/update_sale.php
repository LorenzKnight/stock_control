<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Update failed",
	"img_gif" => "images/sys-img/error.gif",
	"redirect_url" => null
];

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$userId = $_SESSION["sc_UserId"] ?? null;
	if (!$userId) throw new Exception("User session not found.");

	// Leer la entrada JSON
	$input = json_decode(file_get_contents("php://input"), true);

	if (!$input || !isset($input['sale_id']) || !isset($input['customer_id'])) {
        throw new Exception("Incomplete data to update the sale.");
    }

	// Validar campos obligatorios
	$requiredFields = ["sale_id", "customer_id", "price_sum", "initial", "delivery_date", "remaining", "interest", "installments_month", "payment_date", "due"];
	foreach ($requiredFields as $field) {
		if (empty($input[$field])) throw new Exception("Missing required field: $field");
	}

	$saleId = (int)$input["sale_id"];
	$updateFields = [
		"customer_id" => (int)$input["customer_id"],
		"price_sum" => (float)$input["price_sum"],
		"initial" => (float)$input["initial"],
		"delivery_date" => $input["delivery_date"],
		"remaining" => (float)$input["remaining"],
		"interest" => (int)$input["interest"],
		"installments_month" => (int)$input["installments_month"],
		"no_installments" => (int)$input["no_installments"],
		"payment_date" => $input["payment_date"],
		"due" => (float)$input["due"]
	];

	// Actualizar la venta en la base de datos
	$result = update_table("sales", $updateFields, ["sales_id" => $saleId]);
	if (!$result["success"]) throw new Exception("Failed to update sale.");

	// Eliminar productos antiguos
	delete_from("purchased_products", ["sales_id" => $saleId]);

	// Agregar los productos nuevos
	foreach ($input["products"] as $product) {
		$productFields = [
			"sales_id" => $saleId,
			"customer_id" => (int)$input["customer_id"],
			"product_id" => (int)$product["product_id"],
			"quantity" => (int)$product["quantity"],
			"price" => (float)$product["price"],
			"discount" => (float)$product["discount"],
			"total" => (float)$product["total"],
			"create_by" => $userId
		];
		$insertResult = insert_into("purchased_products", $productFields);
		if (!$insertResult["success"]) throw new Exception("Failed to add product: " . $product["product_id"]);
	}

	// Registrar en el log
	log_activity(
		$userId,
		"update_sale",
		"User updated sale info (ID: $saleId).",
		"sales",
		$saleId
	);

	$response = [
		"success" => true,
		"message" => "Sale updated successfully.",
		"img_gif" => "images/sys-img/loading1.gif",
		"redirect_url" => ""
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>