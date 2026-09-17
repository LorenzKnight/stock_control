<?php
use App\Inventory\InventoryRepository;
use App\Inventory\InventoryService;

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
	$companyId = intval($authUser["company_id"] ?? null);

    if (!$userId) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
    }
    
	if (!$companyId) {
		throw new Exception("User company not found.");
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
			"notification_type",
			"notification_link",
			"created_at"
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

	if (($notif["notification_type"] ?? "") !== "Product Request") {
		throw new Exception("Invalid notification type.");
	}

	$fromUserId = (int)($notif["from_user_id"]);

	if ($fromUserId <= 0) {
        throw new Exception("Invalid requesting user.");
    }

    $repository = new InventoryRepository();
    $service = new InventoryService($repository);

	$transfer = $service->transferStock(
		$userId,
		$companyId,
		$fromUserId,
		$productId,
		$quantity
	);

	$productName = $transfer["product_name"];

	$notifUpdate = update_table("notifications",
		["handled" => 1],
		[
			"from_user_id"			=> $fromUserId,
			"notification_type"		=> "Product Request",
			"notification_content"	=> "{$productName} was requested",
			"handled"				=> 0
		]
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