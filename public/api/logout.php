<?php
	session_start();
	session_unset();  // Elimina todas las variables de sesión
	session_destroy(); // Destruye la sesión

	header("Content-Type: application/json");

	// Redirección en PHP (opcional, pero puede ser bloqueada por el navegador si no se maneja correctamente)
	header("Refresh: 2; URL=../stock.php");

	echo json_encode([
		"success" => true,
		"message" => "Your session is being closed...",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => "../stock.php" // Esta URL será manejada por JavaScript en el frontend
	]);
	exit;
?>