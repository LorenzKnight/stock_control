<?php
use App\ServiceRights\ServiceRightRepository;
use App\ServiceRights\ServiceRightService;

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
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $creatorId = $authUser["user_id"];

    if (!$creatorId) throw new Exception("Unauthorized access.");

    $userId = intval($_POST["user_id"] ?? 0);
    $serviceName = trim($_POST["service_name"] ?? "");
    $canAccess = isset($_POST["can_access"]) && $_POST["can_access"] == 1 ? 1 : 0;

    $repository = new ServiceRightRepository();
	$service = new ServiceRightService($repository);

	$result = $service->createUserRight(
		$userId,
		(int)$creatorId,
		$serviceName,
		$canAccess
	);

	$rightId = (int)$result["right_id"];

    log_activity(
        $creatorId,
        "create user right",
        "Created user right '{$serviceName}' with can_access={$canAccess}",
        "service_rights",
        $rightId
    );

    foreach ($result["cloned_rights"] as $clonedRight) {
		$collaboratorId =
			(int)$clonedRight["user_id"];

		$collaboratorRightId =
			(int)$clonedRight["right_id"];

		log_activity(
			$creatorId,
			"auto-clone user right",
			"Cloned right '{$serviceName}' for collaborator (user_id={$collaboratorId}) from parent user {$userId}",
			"service_rights",
			$collaboratorRightId
		);
	}

    $response = [
        "success" => true,
        "message" => "User right created successfully.",
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