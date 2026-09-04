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
    $editorId = $authUser["user_id"] ?? null;

    if (!$editorId) throw new Exception("Unauthorized access.");

    $rightId     = (int)($_POST["edit_right_id"] ?? 0);
    $serviceName = trim($_POST["edit_service_name"] ?? "");
    $canAccess   = isset($_POST["edit_can_access"]) && $_POST["edit_can_access"] == 1 ? 1 : 0;

    $repository = new ServiceRightRepository();
	$service = new ServiceRightService($repository);

	$result = $service->updateUserRight(
		$rightId,
		(int)$editorId,
		$serviceName,
		$canAccess
	);

    log_activity(
        $editorId,
        "update user right",
        "Updated user right '{$serviceName}' (ID: {$rightId}) — can_access={$canAccess}",
        "service_rights",
        $rightId
    );

    foreach (
		$result["collaborator_changes"]
		as $change
	) {
		$collaboratorId =
			(int)$change["user_id"];

		$collaboratorRightId =
			(int)$change["right_id"];

		if ($change["action"] === "updated") {
			log_activity(
				$editorId,
				"update collaborator right",
				"Updated collaborator right '{$serviceName}' (user_id={$collaboratorId}) — can_access={$canAccess}",
				"service_rights",
				$collaboratorRightId
			);

			continue;
		}

		log_activity(
			$editorId,
			"auto-create collaborator right",
			"Created new right '{$serviceName}' for collaborator (user_id={$collaboratorId}) — can_access={$canAccess}",
			"service_rights",
			$collaboratorRightId
		);
	}

    $response = [
        "success" => true,
        "message" => "User right updated successfully.",
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