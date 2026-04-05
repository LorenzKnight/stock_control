<?php
require_once('../inc/cors.php');
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

	$authUser = requireAuth();
	$userId = intval($authUser["user_id"] ?? 0);

	if ($userId <= 0) {
		throw new Exception("Unauthorized access.");
	}

	// if (!check_user_permission($userId, 'process_handler')) {
	// 	throw new Exception("Access denied. You do not have permission to create data.");
	// }

	$slotId = intval($_POST["storages_info"] ?? 0);

	if ($slotId <= 0) {
		throw new Exception("Slot is required.");
	}

	$userInfo = json_decode(
		select_from("users",
			["company_id"],
			[
				"user_id" => $userId
			],
			["fetch_first" => true]
		), true
	);

	if (empty($userInfo["success"]) || empty($userInfo["data"])) {
		throw new Exception("User data not found.");
	}

	$companyId = intval($userInfo["data"]["company_id"] ?? 0);

	if ($companyId <= 0) {
		throw new Exception("Invalid company.");
	}

	$productIds = $_POST["products_info"] ?? [];

	if (!is_array($productIds)) {
		$productIds = [];
	}

	$newSelection = [];

	for ($i = 0; $i < count($productIds); $i++) {
		$productId = intval($productIds[$i] ?? 0);

		if ($productId <= 0) {
			continue;
		}

		$newSelection[$productId] = true;
	}

	if (empty($newSelection)) {
		throw new Exception("At least one product is required.");
	}

	$currentStorage = json_decode(
		select_from("storage",
			["storage_id", "product_id"],
			[
				"company_id" => $companyId,
				"slot_id"    => $slotId
			]
		), true
	);

	$currentMap = [];

	if (!empty($currentStorage["success"]) && !empty($currentStorage["data"]) && is_array($currentStorage["data"])) {
		foreach ($currentStorage["data"] as $row) {
			$currentProductId = intval($row["product_id"] ?? 0);
			$currentStorageId = intval($row["storage_id"] ?? 0);

			if ($currentProductId > 0 && $currentStorageId > 0) {
				$currentMap[$currentProductId] = [
					"storage_id" => $currentStorageId
				];
			}
		}
	}

	$insertedCount = 0;
	$deletedCount  = 0;

	/*
	 * 1) Insertar los nuevos productos que no existían
	 */
	foreach ($newSelection as $productId => $value) {
		if (isset($currentMap[$productId])) {
			unset($currentMap[$productId]);
			continue;
		}

		$insertResponse = insert_into("storage",
			[
				"company_id" => $companyId,
				"slot_id"    => $slotId,
				"product_id" => $productId,
				"created_by" => $userId,
				"created_at" => date("Y-m-d H:i:s")
			],
			["id" => "storage_id"]
		);

		$insertResult = json_decode($insertResponse, true);

		if (empty($insertResult["success"])) {
			throw new Exception("Error saving storage data.");
		}

		$insertedCount++;
	}

	/*
	 * 2) Eliminar lo que existía antes y ya no viene en la nueva selección
	 */
	if (!empty($currentMap)) {
		foreach ($currentMap as $remainingProductId => $row) {
			$storageId = intval($row["storage_id"] ?? 0);

			if ($storageId <= 0) {
				continue;
			}

			$deleteResponse = delete_from("storage",
				["storage_id" => $storageId]
			);

			$deleteResult = json_decode($deleteResponse, true);

			if (empty($deleteResult["success"])) {
				throw new Exception("Error deleting old storage data.");
			}

			$deletedCount++;
		}
	}

	log_activity(
		$userId,
		"save_storage",
		"Storage selection saved for slot ID: " . $slotId,
		"storage",
		$slotId
	);

	$response = [
		"success"		=> true,
		"message"		=> "Storage saved successfully! Inserted: {$insertedCount}, Deleted: {$deletedCount}.",
		"img_gif"		=> "../images/sys-img/loading1.gif",
		"redirect_url"	=> ""
	];

} catch (Exception $e) {
	$response = [
		"success"		=> false,
		"message"		=> $e->getMessage(),
		"img_gif"		=> "../images/sys-img/error.gif",
		"redirect_url"	=> ""
	];
}

echo json_encode($response);
exit;