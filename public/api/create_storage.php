<?php
use App\Storages\StorageRepository;
use App\Storages\StorageService;

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
	$userId = (int)($authUser["user_id"] ?? 0);

	if ($userId <= 0) {
		throw new Exception("Unauthorized access.");
	}

	// if (!check_user_permission($userId, 'process_handler')) {
	// 	throw new Exception("Access denied. You do not have permission to create data.");
	// }

	$slotId = (int)($_POST["storages_info"] ?? 0);
	$productIds = $_POST["products_info"] ?? [];

	if (!is_array($productIds)) {
		$productIds = [];
	}

	$repository = new StorageRepository();
	$service = new StorageService($repository);

	$result =
		$service->saveStorageSelection(
			$userId,
			$slotId,
			$productIds
		);

	$insertedCount = $result["inserted_count"];
	$deletedCount  = $result["deleted_count"];

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