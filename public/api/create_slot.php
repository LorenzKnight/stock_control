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

	if ($slotId > 0) {
		$existingSlot = json_decode(
			select_from(
				"slot",
				["slot_id"],
				[
					"company_id" => $companyId,
					"slot_name"  => $slotName
				],
				["fetch_first" => true]
			),
			true
		);

    	$existingSlotId = (int)($existingSlot["data"]["slot_id"] ?? 0);

		if ($existingSlotId > 0 && $existingSlotId !== $slotId) {
			throw new Exception("A slot with this name already exists.");
		}

		$updateData = [
			"company_id"				=> $companyId,
			"slot_name"                 => $slotName,
			"current_capacity"          => $currentCapacity,
			"max_capacity"              => $maxCapacity,
			"slot_description"			=> $slotDescription,
			"status"					=> $status
		];
	
        $updateResponse = update_table("slot", $updateData, ["slot_id" => $slotId]);
        $updateResult = json_decode($updateResponse, true);

	    if (empty($updateResult["success"])) {
			throw new Exception("Update failed.");
		}

        $recordId = $slotId;
		$activityType = "update_slot";
		$description = "Slot updated";
		$successMessage = "Slot updated successfully!";
    } else {
		$repository = new SlotRepository();
		$service = new SlotService($repository);

        $recordId =
			$service->createSlot(
				$userId,
				$companyId,
				[
					"slot_name" => $slotName,
					"current_capacity" => $currentCapacity,
					"max_capacity" => $maxCapacity,
					"slot_description" => $slotDescription,
					"status" => $status
				]
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