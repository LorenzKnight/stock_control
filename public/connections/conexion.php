<?php
if (!isset($_SESSION)) {
  session_start();
}

$conn_host    = ' host=music_pgdb'; //container's name instead for "localhost" 
$conn_port    = ' port=5434';
$conn_dbname  = ' dbname=music_player_db';
$conn_user    = ' user=admin';
$conn_pass    = ' password=Admin456';

$sql = pg_connect($conn_host . $conn_port . $conn_dbname . $conn_user . $conn_pass);
if ($sql == false) {
  echo "sql connection error!";
  exit();
}

if (is_file("inc/functions.php")) 
    include("inc/functions.php"); 
else
{
    include("../inc/functions.php");
}

$dominio = "localhost:8888";
// $dominio = "http://www.loopsdancestudio.se";
$pageName = "Rate";
?>