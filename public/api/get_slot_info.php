<?php
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
	$userId = $authUser["user_id"] ?? null;

    if (!$userId) {
        throw new Exception("Unauthorized access. User not found or invalid token.");
    }

    $where = [];

    $search        = $_GET["search"] ?? '';
    $selectSlot = $_GET["select_slot"] ?? '';

    if (!empty($selectSlot)) {
        $where["slot_id"] = $selectSlot;
    }
    
    if (!empty($search)) {
		$where["OR"] = [
			"slot_name ILIKE"    => "%{$search}%",
			// "company_address ILIKE" => "%{$search}%"
		];
	}

    $slotResponse = select_from(
        "slot", 
        [
            "slot_id",
            "company_id",
            "slot_name",
            "slot_description",
            "max_capacity",
            "current_capacity",
            "status"
        ],
        $where, 
        [
            "order_by"          => "slot_name",
            "oder_direction"    => "ASC",
            "fetch_all"         => true
        ]
    );

    $slotData = json_decode($slotResponse, true);

    if ($slotData["success"] && !empty($slotData["data"])) {
        $response = [
            "success"   => true,
            "message"   => "Slot info loaded.",
            "count"     => $slotData["count"],
            "data"      => array_values($slotData["data"])
        ];
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;