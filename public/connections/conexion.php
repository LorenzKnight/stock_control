<?php
if (!defined('DISABLE_SESSION') && !defined('IS_STRIPE_WEBHOOK') && session_status() === PHP_SESSION_NONE) {
  	session_start();
}

function get_pg_connection()
{
	if (isProductionEnv()) {
        $host = $_ENV['DB_HOST_PROD'];
        $port = $_ENV['DB_PORT_PROD'];
        $name = $_ENV['DB_NAME_PROD'];
        $user = $_ENV['DB_USER_PROD'];
        $pass = $_ENV['DB_PASSWORD_PROD'];
    } else {
        $host = $_ENV['DB_HOST_LOCAL'];
        $port = $_ENV['DB_PORT_LOCAL'];
        $name = $_ENV['DB_NAME_LOCAL'];
        $user = $_ENV['DB_USER_LOCAL'];
        $pass = $_ENV['DB_PASSWORD_LOCAL'];
    }

	if (!$name || !$user || !$pass) {
        error_log('❌ DB credentials missing');
        die('Database configuration error');
    }

	$connString = sprintf(
        "host=%s port=%s dbname=%s user=%s password=%s",
        $host,
        $port,
        $name,
        $user,
        $pass
    );

	global $sql;
	$sql = pg_connect($connString);
	if ($sql == false) {
		echo "sql connection error!";
		exit();
	}

	return $sql;
}

if (is_file("inc/functions.php")) {
  	include("inc/functions.php");
} else {
  	include("../inc/functions.php");
}

if (is_file("inc/trigger_rtn.php")) {
  	include("inc/trigger_rtn.php");
} else {
  	include("../inc/trigger_rtn.php");
}

if (!defined('DISABLE_SECURITY')) {
	if (is_file("inc/security.php")) {
		include("inc/security.php");
	} else {
		include("../inc/security.php");
	}
}

$dominio = "localhost:8889";
// $dominio = "https://www.allstockcontrol.com";
$pageName = "All Stock Control";