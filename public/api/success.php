<?php
define('IS_STRIPE_WEBHOOK', true);
require_once('../logic/stock_be.php');

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    die("Missing session ID.");
}

try {
    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    // Mostrar mensaje visual solamente
    $_SESSION["payment_message"] = "¡Gracias! Tu suscripción está siendo procesada.";
} catch (Exception $e) {
    $_SESSION["payment_message"] = "Error al verificar tu pago. Intenta más tarde.";
}

header("Location: ../profile.php");
exit;

// require_once('../logic/stock_be.php');

// $response = [
// 	"success" => false,
// 	"message" => "Invalid request",
// 	"img_gif" => "../images/sys-img/error.gif",
// 	"redirect_url" => "../profile.php"
// ];

// \Stripe\Stripe::setApiKey('sk_test_51RgisC2U3dKi7TbUepLwOh1zeTOYwRz3QNgqRn18kJm5DH8hs6nbv0qiLBbmmpmtS7HjqbTG38TMZnuBkoINEvdc00AQjR1jfP');

// $sessionId = $_GET['session_id'] ?? null;

// if (!$sessionId) {
//     die("Missing session ID.");
// }

// try {
//     // 1. Obtener los datos de la sesión desde Stripe
//     $session = \Stripe\Checkout\Session::retrieve($sessionId);
//     $paymentStatus = $session->payment_status;
//     $customerEmail = $session->customer_details->email ?? 'N/A';
//     $amountTotal = $session->amount_total / 100; // Stripe da el monto en centavos

//     if ($paymentStatus !== 'paid') {
//         throw new Exception("Pago no completado. Estado: $paymentStatus");
//     }

//     // 2. Recuperar datos personalizados de sesión si los guardaste (opcional)
//     $userId = $_SESSION['sc_UserId'] ?? null;
//     if (!$userId) {
//         throw new Exception("No user session found.");
//     }

//     // 3. Actualizar base de datos (ejemplo)
//     $subscriptionDate = date("Y-m-d H:i:s");
//     $expirationDate = date("Y-m-d H:i:s", strtotime("+1 month"));

// 	$packageId = $session->metadata->package_id ?? null;
// 	if (!$packageId) {
// 		throw new Exception("No package ID found in session metadata.");
// 	}

//     $data = [
//         "user_id" => $userId,
//         "package_id" => $packageId,
//         "estimated_cost" => $amountTotal,
//         "subscription_date" => $subscriptionDate,
//         "expiration_date" => $expirationDate
//     ];

//     $insertRes = insert_into("subscriptions", $data, ["id" => "subsc_id"]);
//     $insertResult = json_decode($insertRes, true);

//     if (!$insertResult['success']) {
//         throw new Exception("No se pudo registrar la suscripción.");
//     }

//     // (Opcional) actualizar usuario
//     update_table("users", ["package_id" => $packageId], ["user_id" => $userId]);

//     log_activity(
//         $userId,
//         "stripe_payment_success",
//         "Pago exitoso vía Stripe. Monto: $amountTotal USD. Email: $customerEmail",
//         "subscriptions",
//         $userId
//     );

//     $response = [
// 		"success" => true,
// 		"message" => "Subscription upgraded successfully!",
// 		"img_gif" => "../images/sys-img/loading1.gif",
// 		"redirect_url" => "../profile.php"
// 	];
// } catch (Exception $e) {
//     $response = [
//         "success" => false,
//         "message" => $e->getMessage(),
//         "img_gif" => "../images/sys-img/error.gif",
//         "redirect_url" => "../profile.php"
//     ];
// }

// if ($response["success"]) {
// 	$_SESSION["payment_message"] = $response["message"];
// }

// header("Location: " . $response["redirect_url"]);
// exit;
?>