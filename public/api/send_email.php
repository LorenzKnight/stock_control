<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed.",
        "img_gif" => "../images/sys-img/error.gif"
    ]);
    exit;
}

// 1. 🕳️ Honeypot
if (isset($_POST["company"]) && trim((string)$_POST["company"]) !== '') {
    echo json_encode([
        "success" => true,
        "message" => "Message sent successfully!",
        "img_gif" => "../images/sys-img/loading1.gif"
    ]);

    exit;
}

// 2. Obtener y validar inputs
$name = trim(strip_tags((string)($_POST["contact-us-name"] ?? '')));
$email = trim((string)($_POST["contact-us-email"] ?? ''));
$message = trim(strip_tags((string)($_POST["contact-us-message"] ?? '')));


// 🛑 Validaciones básicas
if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Please complete all fields correctly.",
        "img_gif" => "../images/sys-img/error.gif"
    ]);
    exit;
}

// 3. Límites razonables
if (
    mb_strlen($name) > 100 ||
    mb_strlen($email) > 190 ||
    mb_strlen($message) > 5000
) {
    echo json_encode([
        "success" => false,
        "message" => "The submitted message is too long.",
        "img_gif" => "../images/sys-img/error.gif"
    ]);

    exit;
}

// 4. Rate limiting por IP
$userIp = getUserIP();

if (tooManyEmailsFromIP($userIp, 'contact_form')) {
    http_response_code(429); // Too Many Requests

    echo json_encode([
        "success" => false,
        "message" => "Too many messages. Please try again later.",
        "img_gif" => "../images/sys-img/error.gif"
    ]);

    exit;
}

// 5. Escapar contenido para HTML
$safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$safeEmail = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// 📬 Configuración del correo
$to = "info@allstockcontrol.com";
$from = "no-reply@allstockcontrol.com";
$subject = "New contact message from AllStockControl";

// 📨 Contenido HTML
$htmlContent = "
    <strong>New contact form message</strong><br><br>

    <strong>Name:</strong> {$safeName}<br>
    <strong>Email:</strong> {$safeEmail}<br><br>

    <strong>Message:</strong><br>
    <div style='margin-top:8px; white-space:pre-line;'>
        {$safeMessage}
    </div>
";

// 📤 Enviar correo
$sent = sendSystemEmail($from, $to, $subject, $htmlContent);

if ($sent) {
    echo json_encode([
        "success" => true,
        "message" => "Message sent successfully!",
        "img_gif" => "../images/sys-img/loading1.gif"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "There was an error sending the message. Please try again later.",
        "img_gif" => "../images/sys-img/error.gif"
    ]);
}
exit;