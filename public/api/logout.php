<?php
	session_start();
	session_unset();
	session_regenerate_id(true);
	session_destroy();

	header("Content-Type: application/json");

	header("Refresh: 2; URL=../stock.php");

	echo json_encode([
		"success" => true,
		"message" => "Your session is being closed...",
		"img_gif" => "../images/sys-img/loading1.gif",
		"redirect_url" => "../stock.php" 
	]);
	exit;
?>