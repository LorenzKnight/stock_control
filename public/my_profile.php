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
        <div class="personal-data">
            Datos personales del usuario <br>
        </div>
        <div class="other-data">
            informacion de la subscripcion de stock <br>
            lista de cuentas de usuario dependientes donde se le puede dar derechos <br>
        </div>
    </div>
</body>

</html>