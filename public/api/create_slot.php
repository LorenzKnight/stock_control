<?php
use App\Slots\SlotRepository;
use App\Slots\SlotService;

require_once ('../inc/cors.php');
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
	$companyId = (int)($authUser["company_id"] ?? 0);
	
	if ($userId <= 0) {
        throw new Exception("Unauthorized access.");
    }

	if ($companyId <= 0) {
		throw new Exception("Invalid company.");
	}

    // if (!check_user_permission($userId, 'process_handler')) {
	// 	throw new Exception("Access denied. You do not have permission to create data.");
	// }

    $slotIdRaw = $_POST["slot_id"] ?? '';
    $slotId = (int)($slotIdRaw);

    if ($slotIdRaw !== '' && $slotId <= 0) {
		throw new Exception("Invalid slot ID.");
	}

	$slotName           = trim($_POST["slot_name"] ?? '');
    $currentCapacity    = (int)($_POST["current_capacity"] ?? 0);
    $maxCapacity        = (int)($_POST["max_capacity"] ?? 0);
	$slotDescription	= trim($_POST["slot_description"] ?? '');
	$status             = (int)($_POST["slot_status"] ?? 0);

    if ($slotName === '') {
		throw new Exception("Slot Name is required.");
	}

	$repository = new SlotRepository();
	$service = new SlotService($repository);

	$slotData = [
		"slot_name"                 => $slotName,
		"current_capacity"          => $currentCapacity,
		"max_capacity"              => $maxCapacity,
		"slot_description"			=> $slotDescription,
		"status"					=> $status
	];

	if ($slotId > 0) {
		$service->updateSlot(
			$companyId,
			$slotId,
			$slotData
		);

		$recordId = $slotId;
		$activityType = "update_slot";
		$description = "Slot updated";
		$successMessage = "Slot updated successfully!";
    } else {
        $recordId =
			$service->createSlot(
				$userId,
				$companyId,
				$slotData
			);

		$activityType = "create_slot";
		$description = "New slot created";
		$successMessage = "Slot created successfully!";
    }

    log_activity(
		$userId,
		$activityType,
		$description,
		"slot",
		$recordId
	);

    $response = [
		"success"		=> true,
		"message"		=> $successMessage,
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