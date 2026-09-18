<?php
use App\Shippings\ShippingRepository;
use App\Shippings\ShippingService;

require_once ('../inc/cors.php');
require_once('../logic/stock_be.php');

global $sql;

if (!$sql) {
	$sql = get_pg_connection();
}

header("Content-Type: application/json");

$response = [
	"success"		=> false,
	"message"		=> "Invalid request",
	"img_gif"		=> "../images/sys-img/error.gif",
	"redirect_url"	=> ""
];

$transactionStarted = false;
$qrPath = null;

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
		throw new Exception("Company ID is required.");
	}

	if (!check_user_permission($userId, 'process_handler')) {
		throw new Exception("Access denied. You do not have permission to create data.");
	}

	$repository = new ShippingRepository();
	$service = new ShippingService($repository);

	if (!pg_query($sql, "BEGIN")) {
		throw new RuntimeException("Could not start shipping creation transaction.");
	}

	$transactionStarted = true;

	$shipping = $service->createShipping(
		$userId,
		$companyId,
		$_POST
	);

	$shippingId = (int)$shipping["shipping_id"];
	$shippingNo = (int)$shipping["shipping_no"];
	$qrImageName = (string)$shipping["qr_image"];
	$qrDirectory = __DIR__ . "/../images/shippings-code";

	if (!is_dir($qrDirectory)) {
		throw new RuntimeException(
			"Shipping QR directory not found."
		);
	}

	$qrPath = $qrDirectory . "/" . $qrImageName;

	QRcode::png(
		(string)$shippingNo,
		$qrPath,
		QR_ECLEVEL_L,
		15,
		2
	);

	if (!is_file($qrPath) || filesize($qrPath) === 0) {
		throw new RuntimeException( "Failed to generate shipping QR code.");
	}

	$service->attachQrImage(
		$shippingId,
		$companyId,
		$qrImageName
	);

	if (!pg_query($sql, "COMMIT")) {
		throw new RuntimeException("Could not complete shipping creation.");
	}

	$transactionStarted = false;

	log_activity(
		$userId,
		"create_shipping",
		"New shipment is added",
		"shippings",
		$shippingId
	);

	$response = [
		"success"		=> true,
		"message"		=> "Shipment created successfully!",
		"img_gif"		=> "../images/sys-img/loading1.gif",
		"redirect_url"	=> ""
	];

} catch (Exception $e) {
	if ($transactionStarted) {
		pg_query($sql, "ROLLBACK");
	}

	if ($qrPath !== null && is_file($qrPath)) {
		unlink($qrPath);
	}

	$response = [
		"success"		=> false,
		"message"		=> $e->getMessage(),
		"img_gif"		=> "../images/sys-img/error.gif",
		"redirect_url"	=> ""
	];
}

echo json_encode($response);
exit;