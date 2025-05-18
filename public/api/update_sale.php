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

    // Capturar datos del formulario
    $saleId = isset($_POST["sale_id"]) ? (int)$_POST["sale_id"] : null;
    $customerId = isset($_POST["customer_id"]) ? (int)$_POST["customer_id"] : null;

    if (!$saleId || !$customerId) {
        throw new Exception("Incomplete data to update the sale.");
    }

    // Datos de la venta
    $updateFields = [
        "customer_id" => $customerId,
        "price_sum" => number_format((float)$_POST["edit_price_sum"], 2, '.', ''),
        "initial" => number_format((float)$_POST["edit_initial"], 2, '.', ''),
        "delivery_date" => date('Y-m-d H:i:s', strtotime($_POST["edit_delivery_date"])),
        "remaining" => number_format((float)$_POST["edit_remaining"], 2, '.', ''),
        "interest" => (int)$_POST["edit_interest"],
        "no_installments" => (int)$_POST["edit_installments_month"],
        "payment_date" => date('Y-m-d H:i:s', strtotime($_POST["edit_payment_date"])),
        "due" => number_format((float)$_POST["edit_due"], 2, '.', '')
    ];

    // Actualizar la venta
    $result = update_table("sales", $updateFields, ["sales_id" => $saleId]);
	if (is_string($result)) {
		$result = json_decode($result, true);
	}

    if (!$result || !$result["success"]) {
        throw new Exception("Failed to update sale. " . ($result["message"] ?? "Unknown error."));
    }

    // Eliminar productos antiguos
    $deleteResult = delete_from("purchased_products", ["sales_id" => $saleId]);

	if (is_string($deleteResult)) {
		$deleteResult = json_decode($deleteResult, true);
	}

    if (!$deleteResult || !$deleteResult["success"]) {
		throw new Exception("Failed to delete old products. " . ($deleteResult["message"] ?? "Unknown error."));
    }

    // Verificar si los productos se enviaron correctamente
    if (empty($_POST["products"])) {
        throw new Exception("No products received.");
    }

    // Decodificar el JSON de productos
    $products = json_decode($_POST["products"], true);
    if (!is_array($products)) {
        throw new Exception("Invalid products format.");
    }

    // Agregar los productos nuevos
    foreach ($products as $product) {
        if (!isset($product["product_id"], $product["quantity"], $product["price"], $product["discount"], $product["total"])) {
            throw new Exception("Invalid product data.");
        }

        $productFields = [
            "sales_id" => $saleId,
            "customer_id" => $customerId,
            "product_id" => (int)$product["product_id"],
            "quantity" => (int)$product["quantity"],
            "price" => number_format((float)$product["price"], 2, '.', ''),
            "discount" => number_format((float)$product["discount"], 2, '.', ''),
            "total" => number_format((float)$product["total"], 2, '.', ''),
            "create_by" => $userId
        ];
        $insertResult = insert_into("purchased_products", $productFields);

		if (is_string($insertResult)) {
			$insertResult = json_decode($insertResult, true);
		}

        if (!$insertResult || !$insertResult["success"]) {
            throw new Exception("Failed to add product: " . $product["product_id"] . ". " . ($insertResult["message"] ?? "Unknown error."));
        }
    }

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