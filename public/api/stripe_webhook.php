<?php
define('IS_STRIPE_WEBHOOK', true);
require_once('../logic/stock_be.php');

\Stripe\Stripe::setApiKey('sk_test_51RgisC2U3dKi7TbUepLwOh1zeTOYwRz3QNgqRn18kJm5DH8hs6nbv0qiLBbmmpmtS7HjqbTG38TMZnuBkoINEvdc00AQjR1jfP'); // tu clave secreta
$endpointSecret = 'whsec_YrXZi2jJDbN5hmQ12pNrH53jXhPWKOhf'; // tu clave secreta del webhook (desde el dashboard de Stripe)

// 1. Captura y verifica el evento
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
} catch (\UnexpectedValueException $e) {
    http_response_code(400); // JSON inválido
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400); // Firma inválida
    exit();
}

// -------------------------
// Evento: checkout completado
// -------------------------
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;

    $userId = $session->metadata->user_id ?? null;
    $packageId = $session->metadata->package_id ?? null;
    $subscriptionId = $session->subscription ?? null;
    $amountTotal = $session->amount_total / 100; // Stripe da el monto en centavos
    
    if ($userId && $subscriptionId) {
        // Verifica si ya existe para evitar duplicados
        $existing = json_decode(select_from("subscriptions", ["stripe_subscription_id"], [
            "user_id" => $userId,
            "stripe_subscription_id" => $subscriptionId
        ], ["fetch_first" => true]), true);

        if (!$existing['success']) {
            $insert = insert_into("subscriptions", [
                "user_id" => $userId,
                "package_id" => $packageId,
                "stripe_subscription_id" => $subscriptionId,
                "estimated_cost" => $amountTotal,
                "subscription_date" => date("Y-m-d H:i:s"),
                "expiration_date" => date("Y-m-d H:i:s", strtotime("+1 month"))
            ]);

            $insertResult = json_decode($insert, true);
            if (!$insertResult['success']) {
                log_activity($userId, "webhook_error", "Fallo al registrar la suscripción", "subscriptions", $userId);
            }
        }

        // También puedes actualizar el usuario si quieres
        update_table("users", ["package_id" => $packageId], ["user_id" => $userId]);

        // Log opcional
        log_activity(
            $userId,
            "webhook_checkout_completed",
            "Stripe webhook: sesión completada y subscripción registrada. ID: $subscriptionId",
            "subscriptions",
            $userId
        );
    }
}

// -------------------------
// Evento: pago mensual exitoso
// -------------------------
elseif ($event->type === 'invoice.paid') {
    $invoice = $event->data->object;
    $subscriptionId = $invoice->subscription ?? null;

    $record = json_decode(select_from("subscriptions", ["user_id"], [
        "stripe_subscription_id" => $subscriptionId
    ], ["fetch_first" => true]), true);

    if ($record["success"]) {
        $userId = $record["data"]["user_id"];

        update_table("subscriptions", [
            "expiration_date" => date("Y-m-d H:i:s", strtotime("+1 month"))
        ], ["stripe_subscription_id" => $subscriptionId]);

        update_table("users", ["account_status" => "active"], ["user_id" => $userId]);

        log_activity(
            $userId,
            "webhook_invoice_paid",
            "Pago mensual exitoso recibido. Subscripción: $subscriptionId",
            "subscriptions",
            $userId
        );
    }
}

// -------------------------
// Evento: pago mensual fallido
// -------------------------
elseif ($event->type === 'invoice.payment_failed') {
    $invoice = $event->data->object;
    $subscriptionId = $invoice->subscription ?? null;

    $record = json_decode(select_from("subscriptions", ["user_id"], [
        "stripe_subscription_id" => $subscriptionId
    ], ["fetch_first" => true]), true);

    if ($record["success"]) {
        $userId = $record["data"]["user_id"];

        update_table("users", ["account_status" => "suspended"], ["user_id" => $userId]);

        log_activity(
            $userId,
            "webhook_payment_failed",
            "Pago mensual fallido. Cuenta suspendida.",
            "users",
            $userId
        );
    }
}

// 3. Finalizar con 200 OK
http_response_code(200);
echo "OK";
exit;