<?php require_once('logic/stock_be.php'); ?>

<!DOCTYPE html>
<html class="no-js" lang="sw">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Stock Control</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/styles.css">
	<script defer src="js/actions.js"></script>
	<script defer src="js/checkPermission.js"></script>
</head>

<body>
	<?php include("components/modal_customers_options.php"); ?>
	<?php include("components/modal_add_customers.php"); ?>
	<?php include("components/message.php"); ?>
	<?php include("components/header.php"); ?>
	<?php include("components/customers_container.php"); ?>
</body>

</html>