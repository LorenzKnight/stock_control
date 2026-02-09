<?php
require_once('../inc/cors.php');
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
        throw new Exception("Method not allowed.");
    }

    $authUser = requireAuth();
    $userId = intval($authUser["user_id"] ?? 0);

    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    $quantity       = intval($_POST["quantity"] ?? 0);
    $productId      = intval($_POST["product_id"] ?? 0);
    $notificationId = intval($_POST["notification_id"] ?? 0);

	if ($quantity <= 0) {
        throw new Exception("Quantity must be greater than zero.");
    }
	if ($productId <= 0) {
		throw new Exception("Invalid product.");
	}
    if ($notificationId <= 0) {
        throw new Exception("Invalid notification.");
    }

	$notifCheck = json_decode(select_from(
		"notifications",
		[
			"notification_id",
			"from_user_id",
			"to_user_id",
			"notification_type"
		],
		[
			"notification_id" => $notificationId,
			"to_user_id" => $userId
		],
		["fetch_first" => true]
	), true);

	if (empty($notifCheck["success"]) || empty($notifCheck["data"])) {
		throw new Exception("Notification not found or access denied.");
	}

	$notif = $notifCheck["data"];

	if ($notif["notification_type"] !== "Product Request") {
		throw new Exception("Invalid notification type.");
	}

	$fromUserId = intval($notif["from_user_id"]);

	$productData = json_decode(select_from(
        "products",
        ["*"],
        ["product_id" => $productId],
        ["fetch_first" => true]
    ), true);

    if (empty($productData["success"]) || empty($productData["data"])) {
        throw new Exception("Product not found.");
    }

    $productInfo = $productData["data"];

	if ($quantity > intval($productInfo["quantity"])) {
        throw new Exception("Requested quantity exceeds available stock.");
    }

    $requestUser = json_decode(select_from(
        "users",
        ["company_id"],
        ["user_id" => $fromUserId],
        ["fetch_first" => true]
    ), true);

    if (empty($requestUser["success"]) || empty($requestUser["data"])) {
        throw new Exception("Requesting user not found.");
    }

    $requestCompany = $requestUser["data"]["company_id"] ?? "Unknown Company";

    $productToUpdate = json_decode(select_from(
        "products",
        ["*"],
        [
            "product_name" => $productInfo["product_name"],
            "company_id" => $requestCompany
        ],
        ["fetch_first" => true]
    ), true);

    if ($productToUpdate["success"] && !empty($productToUpdate["data"])) {
        $productUpdateResult = update_table(
            "products",
            [
                "quantity" => intval($productToUpdate["data"]["quantity"]) - $quantity
            ],
            [
                "product_name" => $productInfo["product_name"],
                "company_id" => $requestCompany
            ]
        );

        if (empty($productUpdateResult["success"]) || !$productUpdateResult["success"]) {
            throw new Exception("Failed to update product quantity. Please try again.");
        }
    } else {
		// CREAR LA LOGICA PARA QUE SE CREE UN REGISTRO DE PRODUCTO NUEVO EN LA EMPRESA DEL USUARIO QUE HIZO LA SOLICITUD, 
		// CON LA CANTIDAD SOLICITADA Y LOS DEMÁS DATOS IGUALES AL PRODUCTO ORIGINAL (EXCEPTO EL ID, QUE DEBE SER AUTO_INCREMENT). 
		// SI SE DECIDE NO CREAR UN NUEVO REGISTRO, SIMPLEMENTE LANZAR UNA EXCEPCIÓN INDICANDO QUE EL PRODUCTO A ACTUALIZAR NO SE ENCONTRÓ EN LA EMPRESA DEL USUARIO SOLICITANTE.
        $newProductData = [
			"company_id"		=> $requestCompany,
			"create_by"			=> $userId,
			"sale_unit_type"	=> $productInfo["sale_unit_type"] ?? null,
			"units_per_pack"	=> $productInfo["units_per_pack"] ?? null,
			"product_image"		=> $productInfo["product_image"] ?? null,
			"product_name"		=> $productInfo["product_name"] ?? null,
			"hs_code"			=> $productInfo["hs_code"] ?? null,
			"product_type"		=> $productInfo["product_type"] ?? null,
			"product_mark"		=> $productInfo["product_mark"] ?? null,
			"product_model"		=> $productInfo["product_model"] ?? null,
			"product_sub_model" => $productInfo["product_sub_model"] ?? null,
			"product_year"		=> $productInfo["product_year"] ?? null,
			"description"		=> $productInfo["description"] ?? null,
			"currency"			=> $productInfo["currency"] ?? null,
			"price"				=> $productInfo["price"] ?? null,
			"purpose"			=> $productInfo["purpose"] ?? null,
			"quantity"			=> $quantity,
			"min_quantity"		=> $productInfo["min_quantity"] ?? null,
			"weight_per_unit"	=> $productInfo["weight_per_unit"] ?? null,
			"total_weight"		=> $productInfo["total_weight"] ?? null,
			"status"			=> 1,
			"created_at"		=> date("Y-m-d H:i:s")
        ];

        $newProductResult = insert_into("products", $newProductData);

        if (empty($newProductResult["success"]) || !$newProductResult["success"]) {
            throw new Exception("Failed to create new product in requesting user's company.");
        }
    }

    // 6️⃣ Marcar notificación como leída / respondida
	update_table("notifications",
		["notification_link" => 0],
		["notification_id" => $notificationId]
	);

    log_activity(
        $userId,
        "answer_product_request",
        "Answered product request (Notification ID: {$notificationId}, Product ID: {$productId}, Qty: {$quantity})",
        "products",
        $productId
    );

    $response = [
        "success" => true,
        "message" => "Product request answered successfully.",
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