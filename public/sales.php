<?php require_once('logic/stock_be.php'); ?>
<?php include('logic/mini_language_switcher.php'); ?>

<!DOCTYPE html>
<html class="no-js" lang="<?= htmlspecialchars($lang ?? 'en') ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" type="image/png" href="images/sys-img/asc-favicon.png" />
	<title>All Stock Control</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/styles.css">
	<script defer src="js/functions.js"></script>
	<script>
		window.APP_LANG = <?= json_encode($lang ?? 'en') ?>;
		window.i18n = <?= json_encode($t ?? [], JSON_UNESCAPED_UNICODE) ?>;
	</script>
	<script defer src="js/actions.js"></script>
	<script defer src="js/sales.js"></script>
	<script defer src="js/realtimeClient.js"></script>
	<script defer src="js/checkPermission.js"></script>
</head>

<body>
	<?php include("components/modal_sale_options.php"); ?>
	<?php include("components/modal_add_sale.php"); ?>
	<?php include("components/message.php"); ?>
	<?php include("components/confirm_and_notification.php"); ?>
	<?php include("components/header.php"); ?>
	<?php include("components/sales_container.php"); ?>
</body>

</html>