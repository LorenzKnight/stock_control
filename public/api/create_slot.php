<?php
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
	$userId = $authUser["user_id"] ?? null;
	
	if (!$userId) {
        throw new Exception("Unauthorized access.");
    }

    // if (!check_user_permission($userId, 'process_handler')) {
	// 	throw new Exception("Access denied. You do not have permission to create data.");
	// }

    $slotIdRaw = $_POST["slot_id"] ?? '';
    $slotId = intval($slotIdRaw);

    if ($slotIdRaw !== '' && $slotId <= 0) {
		throw new Exception("Invalid slot ID.");
	}

    $userInfo = json_decode(
        select_from(
            "users",
            ["company_id"],
            [
                "user_id" => $userId
            ], ["fetch_first" => true]
        ),
        true
    );

    if (empty($userInfo["success"]) || empty($userInfo["data"])) {
        throw new Exception("User data not found.");
    }
	$userData = $userInfo["data"];

    $companyId			= intval($userData["company_id"] ?? 0);
	$slotName           = trim($_POST["slot_name"] ?? '');
    $currentCapacity    = intval($_POST["current_capacity"] ?? 0);
    $maxCapacity        = intval($_POST["max_capacity"] ?? 0);
	$slotDescription	= trim($_POST["slot_description"] ?? '');
	$status             = intval($_POST["slot_status"] ?? 0);

    if ($companyId <= 0) {
        throw new Exception("Invalid company.");
    }

    if ($slotName === '') {
		throw new Exception("Slot Name is required.");
	}

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

    $existingSlotId = intval($existingSlot["data"]["slot_id"] ?? 0);

	if ($existingSlotId > 0 && $existingSlotId !== $slotId) {
		throw new Exception("A slot with this name already exists.");
	}

    $slotData = [
		"company_id"				=> $companyId,
		"slot_name"                 => $slotName,
        "current_capacity"          => $currentCapacity,
        "max_capacity"              => $maxCapacity,
		"slot_description"			=> $slotDescription,
		"status"					=> $status
	];
	
    if ($slotId > 0) {
        $updateData = $slotData;
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
        $insertData = $slotData;
		$insertData["created_by"] = $userId;
		$insertData["created_at"] = date("Y-m-d H:i:s");

        $insertResponse = insert_into("slot", $insertData, ["id" => "slot_id"]);
        $insertResult = json_decode($insertResponse, true);

        if (empty($insertResult["success"])) {
            throw new Exception("Error saving slot data.");
        }

        $recordId = $insertResult["id"] ?? null;
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