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
	<script src="http://js.stripe.com/v3/"></script>
	<script defer src="js/actions.js"></script>
	<script defer src="js/checkPermission.js"></script>
	<script src="logic/payment_message.php"></script>
</head>

<body>
	<?php include("components/modal_edit_my_info.php"); ?>
	<?php include("components/modal_subscription.php"); ?>
	<?php include("components/modal_edit_company.php"); ?>
	<?php include("components/modal_edit_member.php"); ?>
	<?php include("components/modal_add_member.php"); ?>
	<?php include("components/message.php"); ?>
	<?php include("components/header.php"); ?>
	<?php include("components/profile_container.php"); ?>
</body>

</html>