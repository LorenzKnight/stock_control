<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$conn_host    = 'host=stock_pgdb'; //container's name instead for "localhost" 
$conn_port    = ' port=5432';
$conn_dbname  = ' dbname=stock_control_db';
$conn_user    = ' user=admin';
$conn_pass    = ' password=Admin456';

$sql = pg_connect($conn_host . $conn_port . $conn_dbname . $conn_user . $conn_pass);
if ($sql == false) {
  echo "sql connection error!";
  exit();
}

if (is_file("inc/functions.php")) {
  include("inc/functions.php");
} else {
  include("../inc/functions.php");
}

if (is_file("inc/security.php")) {
  include("inc/security.php");
} else {
  include("../inc/security.php");
}

$dominio = "localhost:8889";
// $dominio = "http://www.stockcontrol.se";
$pageName = "Stock Control";
?>