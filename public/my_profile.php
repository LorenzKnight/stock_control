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

<body>
	<?php include("components/message.php"); ?>
	<?php include("components/header.php"); ?>

	<div class="container">
		<h1 id="hi-user"></h1>
		<div class="personal-data flex">
			<div class="info-box">
				<h1>Welcome to</h1>
				<h4>Stock Control</h4>
				<p>You now have full access to our stock management platform, giving you complete control over inventory tracking and optimization. Where will efficiency take your business today?</p>
			</div>
			<div class="small-box">
				<h2 class="box-title">My info</h2>
				<span id="my-data"></span>
				<button class="button-style-neutral">Edit</button>
			</div>
			<div class="small-box">
				<h2 class="box-title">Membership</h2>
				<h1 id="subsc"></h1>
				<button class="button-style-neutral">Subscription</button>
			</div>
			<div class="small-box">
				<h2 class="box-title">Spot</h2>
				<p><span id="spot">0</span> / <span id="total-spot">0</span></p>
				<button class="button-style-neutral">Add user</button>
			</div>
			<div class="small-box">
				<h2 class="box-title">Company data</h2>
				<button class="button-style-neutral">Edit company</button>
			</div>
			
		</div>
		<div class="personal-data">
			<h2 style="margin-left: 10px;">User List</h2>
			<div class="flex" id="child-user-table">Cargando usuarios...</div>
		</div>
		<div>
			</br>
			TO DO</br>
			ingresar productos</br>
			registrar clientes (agregar notas)</br>
			hacer ventas con planes de pago</br>
			pagos del producto</br>
		</div>
	</div>
</body>

</html>