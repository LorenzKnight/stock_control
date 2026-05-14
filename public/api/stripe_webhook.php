<?php
define('IS_STRIPE_WEBHOOK', true);
require_once('../logic/stock_be.php');

if (isProductionEnv()) {
	\Stripe\Stripe::setApiKey($_ENV['STRIPE_SK_LIVE']);
	$endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET_LIVE'];
} else {
	\Stripe\Stripe::setApiKey($_ENV['STRIPE_SK_TEST']); // tu clave secreta
	$endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET_TEST']; // tu clave secreta del webhook (desde el dashboard de Stripe)
}

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

// ------------------------------------------
// Evento: checkout completado (nueva suscripción)
// ------------------------------------------
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;

    $userId = $session->metadata->user_id ?? null;
    $packageId = $session->metadata->package_id ?? null;
    $subscriptionId = $session->subscription ?? null;
    $subscId = $session->metadata->subsc_id ?? null;
    $amountTotal = $session->amount_total / 100; // Stripe da el monto en centavos
    $extraPack = $session->metadata->extra_pack ?? null;
    
    if ($userId && $subscriptionId) {
        if (!empty($subscId) && is_numeric($subscId)) {
            // 🔁 ACTUALIZAR la suscripción existente con el nuevo stripe_subscription_id
            update_table("subscriptions", [
                "stripe_subscription_id" => $subscriptionId,
                "package_id" => $packageId,
                "estimated_cost" => $amountTotal,
                "subscription_date" => date("Y-m-d H:i:s"),
                "expiration_date" => date("Y-m-d H:i:s", strtotime("+1 month"))
            ], ["subsc_id" => $subscId]);

            log_activity($userId, "webhook_update", "Subscripción actualizada con nuevo stripe_subscription_id: $subscriptionId", "subscriptions", $userId);
        } else {
            $existing = json_decode(select_from("subscriptions", ["subsc_id"], [
                "user_id" => $userId,
                "stripe_subscription_id" => $subscriptionId
            ], ["fetch_first" => true]), true);

            $alreadyExists = (
                isset($existing["success"]) &&
                $existing["success"] &&
                !empty($existing["data"])
            );

            if (!$alreadyExists) {
                $insert = insert_into("subscriptions", [
                    "user_id" => $userId,
                    "package_id" => $packageId,
                    "stripe_subscription_id" => $subscriptionId,
                    "estimated_cost" => $amountTotal,
                    "subscription_date" => date("Y-m-d H:i:s"),
                    "expiration_date" => date("Y-m-d H:i:s", strtotime("+1 month"))
                ], ["id" => "subsc_id"]);

                $insertResult = json_decode($insert, true);

                if (!is_array($insertResult) || empty($insertResult['success'])) {
                    log_activity($userId, "webhook_error", "Fallo al registrar la suscripción: " . $insert, "subscriptions", $userId);
                } else {
                    log_activity($userId, "webhook_success", "Suscripción insertada correctamente. ID: ". $insertResult["id"], "subscriptions", $userId);
                }
            }
        }

        // También puedes actualizar el usuario si quieres
        update_table("users", ["package_id" => $packageId], ["user_id" => $userId]);

        log_activity(
            $userId,
            "webhook_checkout_completed",
            "Sesión de Stripe completada con éxito. Subscripción: $subscriptionId",
            "subscriptions",
            $userId
        );

        // ---------------------------------------
        // 🆕 Si el usuario pagó un servicio extra
        // ---------------------------------------
        if (!empty($extraPack)) {
            $extraUpdate = update_table("extra_services", [
                "status" => 0
            ], [
                "user_id" => $userId,
                "status" => 1
            ]);

            log_activity(
                $userId,
                "webhook extra pack",
                "Servicio extra '$extraPack' actualizado a status=0 tras pago exitoso.",
                "extra_services",
                $userId
            );
        }
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

        update_table("users", ["status" => 1], ["user_id" => $userId]);

        update_table("users", ["status" => 1], ["parent_user" => $userId]);

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

        update_table("users", ["status" => 0], ["user_id" => $userId]);

        update_table("users", ["status" => 0], ["parent_user" => $userId]);

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