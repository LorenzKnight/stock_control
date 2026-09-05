<?php
use App\ServiceRights\ServiceRightRepository;
use App\ServiceRights\ServiceRightService;

require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header("Content-Type: application/json");

$response = [
    "success" => false,
    "message" => "Invalid request",
    "img_gif" => "images/sys-img/error.gif",
    "redirect_url" => ""
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $authUser = requireAuth();
    $deleterId = $authUser["user_id"] ?? null;

    if (empty($deleterId)) {
        throw new Exception("Unauthorized access.");
    }

    $rightId = (int)($_POST["right_id"] ?? 0);

    $repository = new ServiceRightRepository();
	$service = new ServiceRightService($repository);

	$result = $service->deleteUserRight(
		$rightId
	);

	$serviceName = $result["service_name"];

    log_activity(
        $deleterId,
        "delete user right",
        "Deleted user right '{$serviceName}' (ID: {$rightId})",
        "service_rights",
        $rightId
    );

    foreach (
		$result["deleted_collaborator_rights"]
		as $deletedRight
	) {
		$collaboratorId =
			(int)$deletedRight["user_id"];

		$collaboratorRightId =
			(int)$deletedRight["right_id"];

		log_activity(
			$deleterId,
			"delete collaborator right",
			"Deleted collaborator right '{$serviceName}' (user_id={$collaboratorId})",
			"service_rights",
			$collaboratorRightId
		);
	}

    $response = [
        "success" => true,
        "message" => "User right deleted successfully.",
        "img_gif" => "images/sys-img/loading1.gif",
        "redirect_url" => ""
    ];

} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => $e->getMessage(),
        "img_gif" => "images/sys-img/error.gif",
        "redirect_url" => ""
    ];
}

echo json_encode($response);
exit;