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

	if (!check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$userInfo = json_decode(select_from("users", ["company_id"], ["user_id" => $userId], ["fetch_first" => true]), true);
	$userData = $userInfo["data"];

	$companyId		= intval($_POST["shipping_company_id"] ?? $userData["company_id"]);

	$method			= intval($_POST["shipping_method"] ?? 1);
	$destination	= trim($_POST["destination"] ?? '');
	$estimate_date	= trim($_POST["delivery_date"] ?? null);
	$description	= trim($_POST["description"] ?? '');
	$status			= intval($_POST["status"] ?? 0);

	$newOrdNo = get_next_increment_value("shippings", "shipping_no", $companyId, $companyId."30000");

	if ($destination === '') {
		throw new Exception("Destination is required.");
	}

	$insertShippingData = [
		"shipping_no"				=> $newOrdNo,
		"company_id"				=> $companyId,
		"shipping_img"				=> null,
		"shipping_method"			=> $method,
		"destination"				=> $destination,
		"delivery_date"				=> $estimate_date,
		"description"				=> $description,
		"status"					=> $status,
		"create_by"					=> $userId,
		"created_at"				=> date("Y-m-d H:i:s")
	];
	
	$insertResponse = insert_into("shippings", $insertShippingData, ["id" => "shippings_id"]);
	$insertResult = json_decode($insertResponse, true);

	// Generar código QR para el shipping
	$qrText = (string)$newOrdNo; // El texto que irá en el QR
	$qrPath = "../images/shippings-code/" . $qrText . ".png"; // Ruta relativa
	$qrImgName = $qrText . ".png";

	// Asegúrate que la carpeta ../uploads/qr exista y tenga permisos de escritura
	QRcode::png($qrText, $qrPath, QR_ECLEVEL_L, 15, 2);

	// Actualizar el shipping_img con la ruta al QR generado
	update_table("shippings", [
	"shipping_img" => $qrImgName
	], [
		"shippings_id" => $insertResult["id"]
	]);


	if (!$insertResult["success"]) {
		throw new Exception("Error saving customer data.");
	}

	log_activity(
		$userId,
		"create_customer",
		"New shipment is added",
		"shippings",
		$insertResult["id"] ?? null
	);

	$response = [
		"success"		=> true,
		"message"		=> "Shipment created successfully!",
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