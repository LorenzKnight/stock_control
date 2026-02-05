<?php
require_once('../inc/cors.php');
require_once('../logic/stock_be.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed.",
        "img_gif" => "../images/sys-img/error.gif"
    ]);
    exit;
}

// 🔐 Sanitizar inputs
$name = trim(strip_tags($_POST["contact-us-name"] ?? ''));
$email = filter_var($_POST["contact-us-email"] ?? '', FILTER_SANITIZE_EMAIL);
$message = trim(strip_tags($_POST["contact-us-message"] ?? ''));

// 🛑 Validaciones básicas
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    echo json_encode([
        "success" => false,
        "message" => "Please complete all fields correctly.",
        "img_gif" => "../images/sys-img/error.gif"
    ]);
    exit;
}

// 🕳️ Honeypot anti-spam
if (!empty($_POST['company'])) {
    http_response_code(204); // silencioso para bots
    exit;
}

$user_ip = getUserIP();

if (tooManyEmailsFromIP($user_ip, 'contact_form')) {
    http_response_code(429); // Too Many Requests
    exit;
}

// 📬 Configuración del correo
$to = "info@allstockcontrol.com";
$from = "no-reply@allstockcontrol.com";
$subject = "New contact message from AllStockControl";

// 📨 Contenido HTML
$htmlContent = "
    <strong>New contact form message</strong><br><br>

    <strong>Name:</strong> {$name}<br>
    <strong>Email:</strong> {$email}<br><br>

    <strong>Message:</strong><br>
    <div style='margin-top:8px; white-space:pre-line;'>
        {$message}
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