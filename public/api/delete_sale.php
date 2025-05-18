<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Deletion failed",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => null
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    if (empty($_POST["sale_id"])) {
        throw new Exception("Sale ID is required.");
    }

    $saleId = (int)$_POST["sale_id"];

    // Eliminar productos asociados a la venta
    $deleteProductsResult = json_decode(delete_from("purchased_products", ["sales_id" => $saleId]), true);
    if (!$deleteProductsResult["success"]) {
        throw new Exception("Failed to delete associated products.");
    }

    // Eliminar la venta
    $deleteSaleResult = json_decode(delete_from("sales", ["sales_id" => $saleId]), true);
    if (!$deleteSaleResult["success"]) {
        throw new Exception("Failed to delete sale.");
    }

    // Registrar en el log
    log_activity(
        $_SESSION["sc_UserId"] ?? null,
        "delete_sale",
        "Sale ID $saleId and associated products deleted.",
        "sales",
        $saleId
    );

    $response = [
        "success" => true,
        "message" => "Sale and associated products deleted successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>