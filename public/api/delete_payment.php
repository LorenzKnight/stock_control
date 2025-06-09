<?php
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

	$userId = $_SESSION["sc_UserId"] ?? null;
    if (!$userId) throw new Exception("User session not found.");

    if (!check_user_permission($userId, 'delete_data')) {
		throw new Exception("Access denied. You do not have permission to delete data.");
	}

	if (empty($_POST["payment_id"]) || !is_numeric($_POST["payment_id"])) {
		throw new Exception("Missing or invalid payment ID.");
	}

	$paymentId = (int)($_POST["payment_id"]);

	$paymentRes = json_decode(select_from("payments", ["sales_id", "interest", "amount", "due"], ["payment_id" => $paymentId], ["fetch_first" => true]), true);
	if (!$paymentRes || !$paymentRes['success']) {
        throw new Exception("Payment record not found.");
    }
	$payment = $paymentRes['data'];
	
	$saleRes = json_decode(select_from("sales", ["sales_id", "customer_id", "price_sum", "installments_month", "no_installments", "due"], ["sales_id" => $payment["sales_id"]], ["fetch_first" => true]), true);
    if (!$saleRes || !$saleRes['success']) {
        throw new Exception("Sale record not found.");
    }
    $sale = $saleRes['data'];
	
	$revertAmount = $sale["due"] + $payment["amount"];
	$updateResponse = json_decode(update_table("sales", ["due" => $revertAmount], ["sales_id" => $sale["sales_id"]]), true);
	if (!$updateResponse || !$updateResponse["success"]) {
        throw new Exception("Failed to update sales record.");
    }

	$deleteInterestEarnings = json_decode(delete_from("interest_earnings", ["payment_id" => $paymentId]), true);
	$deleteResponse = json_decode(delete_from("payments", ["payment_id" => $paymentId]), true);
	if (!$deleteInterestEarnings["success"] || !$deleteResponse["success"]) {
		throw new Exception("Database error while deleting payment.");
	}

	log_activity(
		$userId,
		"delete_payment",
		"User deleted a payment (ID: {$paymentId}).",
		"payment",
		$paymentId
	);

	$response["success"] = true;
	$response["message"] = "Payment deleted successfully.";
	$response["img_gif"] = "../images/sys-img/loading1.gif";
	$response["redirect_url"] = "";

} catch (Exception $e) {
	$response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
?>