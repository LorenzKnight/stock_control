<?php
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
	"success"		=> false,
	"message"		=> "Invalid request",
	"img_gif"		=> "../images/sys-img/error.gif",
	"redirect_url"	=> ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'edit_data')) {
        throw new Exception("Access denied. You do not have permission to request products.");
    }

    if (empty($_POST["product_id"]) || !is_numeric($_POST["product_id"])) {
        throw new Exception("Missing or invalid product ID.");
    }

    $productId = (int)$_POST["product_id"];
    $productData = json_decode(select_from("products", ["company_id"], ["product_id" => $productId], ["fetch_first" => true]), true);

    if (!$productData || !$productData["success"] || empty($productData["data"])) {
        throw new Exception("Product not found or invalid product ID.");
    }
    $companyId = $productData["data"]["company_id"] ?? null;
    
    $UserData = json_decode(select_from("users", ["user_id"], ["company_id" => $companyId]), true);

    foreach ($UserData["data"] as $user) {
        notify_user(
            $userId,
            $user["user_id"],
            $productId,
            null,
            "Product Request"
        );

        triggerRealtimeNotification($user["user_id"]);

        // Log activity for each user
        log_activity(
            $user["user_id"],
            "request_product",
            "User requested a product (ID: {$productId}).",
            "products",
            $productId
        );
    }

    $response = [
		"success"		=> true,
		"message"		=> "Product request submitted successfully.",
		"img_gif"		=> "../images/sys-img/loading1.gif",
		"redirect_url"	=> ""
	];
} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;