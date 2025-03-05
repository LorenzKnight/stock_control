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
</head>

<body style="background-color: #022e63;">
	<?php include("components/message.php"); ?>
	<div class="container height-100 flex" id="result-container">
		<div class="container-login">
			<?php include("components/modal_signup.php"); ?>
		</div>
		<div class="container-signup">
			<?php include("components/modal_login.php"); ?>
		</div>
	</div>
</body>

</html>