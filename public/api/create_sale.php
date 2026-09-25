<?php
use App\Sales\SaleRepository;
use App\Sales\SaleService;
use App\Inventory\InventoryRepository;
use App\Inventory\InventoryService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
	"success" => false,
	"message" => "Sale could not be created",
	"img_gif" => "../images/sys-img/error.gif",
    "redirect_url" => ""
];

$transactionStarted = false;

try {
	if ($_SERVER["REQUEST_METHOD"] !== "POST") {
		throw new Exception("Method not allowed");
	}

	$authUser = requireAuth();

	$userId = (int)($authUser["user_id"] ?? 0);
	$companyId = (int)($authUser["company_id"] ?? 0);

	if ($userId <= 0) {
		throw new Exception("Unauthorized access. User not found or invalid token.");
	}

	if ($companyId <= 0) {
		throw new Exception("Company ID is required.");
	}

	if (!check_user_permission($userId, 'sales_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$input = json_decode(file_get_contents('php://input'), true);

	if (!is_array($input) || empty($input)) {
		throw new Exception("No data received.");
	}

	$saleRepository = new SaleRepository();
	$inventoryRepository = new InventoryRepository();

	$inventoryService = new InventoryService(
		$inventoryRepository
	);

	$saleService = new SaleService(
		$saleRepository,
		$inventoryService
	);

	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start sale creation transaction.");
	}

	$transactionStarted = true;

	$result = $saleService->createSale(
		$userId,
		$companyId,
		$input
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete sale creation.");
	}

	$transactionStarted = false;

	$saleId = (int)$result["sale_id"];
	$orderNo = (int)$result["order_no"];

	/*
	 * A partir de aquí la venta ya existe.
	 * Estas operaciones no deben hacer rollback
	 * de una venta válida.
	 */

	if (!empty($result["sum_mismatch"])) {
		try {
			log_activity(
				$userId,
				"warning_sum_mismatch",
				"Mismatch: price_sum=" . ($input["price_sum"] ?? 0) . " vs sumLines=" . $result["products_sum"],
				"sales",
				$saleId
			);
		} catch (Throwable $e) {
			error_log("Could not log sale sum mismatch for sale {$saleId}: " . $e->getMessage());
		}
	}

	$showSaleReward = $saleService->processSaleOnboarding($userId);

	/*
	 * Notificaciones de stock bajo.
	 */
	$lowStockAlerts = $result["low_stock_alerts"] ?? [];

	if (!empty($lowStockAlerts)) {
		try {
			$companyUserIds = $saleRepository->findCompanyUserIds($companyId);

			foreach ($lowStockAlerts as $alert) {
				$productId = (int)($alert["product_id"] ?? 0);
				$productName = (string)($alert["product_name"] ?? "Product");
				$newStock = (int)($alert["new_stock"] ?? 0);

				if ($productId <= 0) {
					continue;
				}

				foreach ($companyUserIds as $toUserId) {
					if ($toUserId <= 0) {
						continue;
					}

					notify_user(
						$toUserId,
						null,
						"{$productName} is low on stock (Current: {$newStock})",
						$productId,
						"Stock Update",
						0
					);

					triggerRealtimeNotification(
						$toUserId
					);

					log_activity(
						$toUserId,
						"low_stock_info",
						"{$productName} is low on stock (Current: {$newStock})",
						"notifications",
						$productId
					);
				}
			}

		} catch (Throwable $e) {
			error_log(
				"Could not send low-stock notifications for sale {$saleId}: " .
				$e->getMessage()
			);
		}
	}

	try {
		log_activity(
			$userId,
			"create_sale",
			"Created new sale #{$saleId} with customer_id " . (int)($input["customer_id"] ?? 0),
			"sales",
			$saleId
		);
	} catch (Throwable $e) {
		error_log("Could not log sale creation for sale {$saleId}: " . $e->getMessage());
	}

	$response = [
		"success" => true,
		"message" => "Sale created successfully",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => "",
		"show_reward_modal" => $showSaleReward,
		"reward_type" => $showSaleReward ? "first_sale" : null,
		"sale_id" => $saleId,
		"order_no" => $orderNo,
		"total_interest" => $result["total_interest"] ?? 0.0
	];
} catch (Throwable $e) {
	if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;