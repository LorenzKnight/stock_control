<?php require_once('logic/stock_be.php'); ?>
<?php include('logic/mini_language_switcher.php'); ?>
<?php
$host = $_SERVER['HTTP_HOST'] ?? '';

function ends_with($haystack, $needle) {
	if ($needle === '') return true;
	$len = strlen($needle);
	return $len === 0 ? true : (substr($haystack, -$len) === $needle);
}

$isLocalHost = (
	$host === 'localhost' || 
	$host === '127.0.0.1' || 
	ends_with($host, '.local')
);

$isProduction = function_exists('isProductionEnv') ? isProductionEnv() : (!$isLocalHost);

$stripeJsSrc = $isProduction ? 'https://js.stripe.com/v3/' : 'http://js.stripe.com/v3/';
?>

<!DOCTYPE html>
<html class="no-js" lang="<?= htmlspecialchars($lang) ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" type="image/png" href="/images/sys-img/asc-favicon.png" />
	<title>All Stock Control</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/css/styles.css">

	<!-- Hotjar Tracking Code for https://allstockcontrol.com -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.css">
	<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.js.iife.js"></script>

	<script defer src="/js/functions.js"></script>
	<script src="<?= htmlspecialchars($stripeJsSrc) ?>"></script>
	<script defer src="/js/subscriptions.js"></script>
	<script>
		window.APP_LANG = <?= json_encode($lang ?? 'en') ?>;
		window.i18n = <?= json_encode($t ?? [], JSON_UNESCAPED_UNICODE) ?>;
	</script>
	<script defer src="/js/actions.js"></script>
	<script defer src="/js/realtimeClient.js"></script>
	<script defer src="/js/checkPermission.js"></script>
	<script src="/logic/payment_message.php"></script>
</head>

<body>
	<?php include("components/modal_onboarding.php"); ?>
	<?php include("components/modal_setup.php"); ?>
	<?php include("components/modal_try_pack.php"); ?>
	<?php include("components/modal_reactivate_subscription.php"); ?>
	<?php include("components/modal_edit_my_info.php"); ?>
	<?php include("components/modal_subscription.php"); ?>
	<?php include("components/modal_edit_company.php"); ?>
	<?php include("components/modal_edit_member.php"); ?>
	<?php include("components/modal_add_member.php"); ?>
	<?php include("components/message.php"); ?>
	<?php include("components/confirm_and_notification.php"); ?>
	<?php include("components/header.php"); ?>
	<?php include("components/profile_container.php"); ?>
</body>

</html>