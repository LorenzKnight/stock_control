<?php
require_once('../logic/stock_be.php');
\Stripe\Stripe::setApiKey('sk_test_51RgisC2U3dKi7TbUepLwOh1zeTOYwRz3QNgqRn18kJm5DH8hs6nbv0qiLBbmmpmtS7HjqbTG38TMZnuBkoINEvdc00AQjR1jfP');

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    die("Missing session ID.");
}

try {
    // 1. Obtener los datos de la sesión desde Stripe
    $session = \Stripe\Checkout\Session::retrieve($sessionId);
    $paymentStatus = $session->payment_status;
    $customerEmail = $session->customer_details->email ?? 'N/A';
    $amountTotal = $session->amount_total / 100; // Stripe da el monto en centavos

    if ($paymentStatus !== 'paid') {
        throw new Exception("Pago no completado. Estado: $paymentStatus");
    }

    // 2. Recuperar datos personalizados de sesión si los guardaste (opcional)

    $userId = $_SESSION['sc_UserId'] ?? null;
    if (!$userId) {
        throw new Exception("No user session found.");
    }

    // Aquí podrías haber guardado el paquete elegido en base de datos antes del checkout,
    // pero si no lo hiciste, podrías también usar `metadata` (si lo incluiste en la sesión)

    // 3. Actualizar base de datos (ejemplo)
    $subscriptionDate = date("Y-m-d H:i:s");
    $expirationDate = date("Y-m-d H:i:s", strtotime("+1 month"));

    $data = [
        "user_id" => $userId,
        "members_packs" => 1, // ⚠️ Cambia esto por el pack real
        "estimated_cost" => $amountTotal,
        "subscription_date" => $subscriptionDate,
        "expiration_date" => $expirationDate
    ];

    $insertRes = insert_into("subscriptions", $data, ["id" => "subsc_id"]);
    $insertResult = json_decode($insertRes, true);

    if (!$insertResult['success']) {
        throw new Exception("No se pudo registrar la suscripción.");
    }

    // (Opcional) actualizar usuario
    update_table("users", ["package_id" => 1], ["user_id" => $userId]);

    log_activity(
        $userId,
        "stripe_payment_success",
        "Pago exitoso vía Stripe. Monto: $amountTotal USD. Email: $customerEmail",
        "subscriptions",
        $userId
    );

    echo "<h2>¡Pago exitoso!</h2>";
    echo "<p>Gracias por tu compra. Licencia activada correctamente.</p>";

} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}