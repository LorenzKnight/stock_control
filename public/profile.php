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

// Asset versions
$stylesVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/css/styles.css');
$functionsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/functions.js');
$subscriptionsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/subscriptions.js');
$actionsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/actions.js');
$realtimeVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/realtimeClient.js');
$checkPermissionVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/js/checkPermission.js');
$paymentMessageVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . '/logic/payment_message.php');
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
	<link rel="stylesheet" href="/css/styles.css?v=<?= $stylesVersion ?>">

	<!-- Hotjar Tracking Code for https://allstockcontrol.com -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.css">
	<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.js.iife.js"></script>

	<script defer src="/js/functions.js?v=<?= $functionsVersion ?>"></script>
	<script src="<?= htmlspecialchars($stripeJsSrc) ?>"></script>
	<script defer src="/js/subscriptions.js?v=<?= $subscriptionsVersion ?>"></script>
	<script>
		window.APP_LANG = <?= json_encode($lang ?? 'en') ?>;
		window.i18n = <?= json_encode($t ?? [], JSON_UNESCAPED_UNICODE) ?>;
	</script>
	<script defer src="/js/actions.js?v=<?= $actionsVersion ?>"></script>
	<script defer src="/js/realtimeClient.js?v=<?= $realtimeVersion ?>"></script>
	<script defer src="/js/checkPermission.js?v=<?= $checkPermissionVersion ?>"></script>
	<script src="/logic/payment_message.php?v=<?= $paymentMessageVersion ?>"></script>
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