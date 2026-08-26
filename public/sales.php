<?php require_once('logic/stock_be.php'); ?>
<?php include('logic/mini_language_switcher.php'); ?>

<?php
// Asset versions
$stylesVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/css/styles.css');
$functionsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/functions.js');
$actionsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/actions.js');
$salesVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/sales.js');
$realtimeVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/realtimeClient.js');
$checkPermissionVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/checkPermission.js');
?>

<!DOCTYPE html>
<html class="no-js" lang="<?= htmlspecialchars($lang ?? 'en') ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" type="image/png" href="images/sys-img/asc-favicon.png" />
	<title>All Stock Control</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/styles.css?v=<?= $stylesVersion ?>">
	<script defer src="js/functions.js?v=<?= $functionsVersion ?>"></script>
	<script>
		window.APP_LANG = <?= json_encode($lang ?? 'en') ?>;
		window.i18n = <?= json_encode($t ?? [], JSON_UNESCAPED_UNICODE) ?>;
	</script>
	<script defer src="js/actions.js?v=<?= $actionsVersion ?>"></script>
	<script defer src="js/sales.js?v=<?= $salesVersion ?>"></script>
	<script defer src="js/realtimeClient.js?v=<?= $realtimeVersion ?>"></script>
	<script defer src="js/checkPermission.js?v=<?= $checkPermissionVersion ?>"></script>
</head>

<body>
	<?php include("components/modal_onboarding.php"); ?>
	<?php include("components/modal_sale_options.php"); ?>
	<?php include("components/modal_add_sale.php"); ?>
	<?php include("components/message.php"); ?>
	<?php include("components/confirm_and_notification.php"); ?>
	<?php include("components/header.php"); ?>
	<?php include("components/sales_container.php"); ?>
</body>

</html>