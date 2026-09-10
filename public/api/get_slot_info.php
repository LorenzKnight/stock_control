<?php
use App\Slots\SlotRepository;
use App\Slots\SlotService;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success"   => false,
    "message"   => "No company info found",
    "count"     => 0,
    "data"      => []
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        throw new Exception("Method not allowed.");
    }

    $authUser = requireAuth();
	$userId = (int)($authUser["user_id"] ?? 0);
    $companyId = (int)($authUser["company_id"] ?? 0);

    if ($userId <= 0) {
        throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    if ($companyId <= 0) {
		throw new Exception("Unauthorized access: company not found.");
	}

    $search = trim($_GET["search"] ?? '');
    $selectSlot = isset($_GET["select_slot"]) ? (int)$_GET["select_slot"] : 0;

    $repository = new SlotRepository();
	$service = new SlotService($repository);

	$slots = $service->getSlots(
		$companyId,
		$search,
		$selectSlot
	);

    if (!empty($slots)) {
        $response = [
            "success"   => true,
            "message"   => "Slot info loaded.",
            "count"     => count($slots),
            "data"      => $slots
        ];
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;