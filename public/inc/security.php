<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['mp_UserId'])) {
	header("Location: discover?login=1");
	exit();
}

echo "<h3 style='color: red; text-align: center;'> Obs. The security module is active!</h3>";
?>