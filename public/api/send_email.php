<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $to = "joellorenzo.k@gmail.com";
    $name = strip_tags($_POST["contact-us-name"] ?? '');
    $email = filter_var($_POST["contact-us-email"] ?? '', FILTER_SANITIZE_EMAIL);
    $message = strip_tags($_POST["contact-us-message"] ?? '');

    $subject = "New contact form message";
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    $body = "Nombre: $name\nEmail: $email\n\nMensaje:\n$message\n";

    if (mail($to, $subject, $body, $headers)) {
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
} else {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);
}