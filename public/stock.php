<?php require_once('logic/stock_be.php'); ?>
<?php include('logic/mini_language_switcher.php'); ?>

<!DOCTYPE html>
<html class="no-js" lang="<?= htmlspecialchars($lang) ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?= htmlspecialchars($t['title']) ?></title>
	<meta name="description" content="<?= htmlspecialchars($t['description']) ?>">
	<meta http-equiv="Content-Language" content="<?= htmlspecialchars($t['content_language']) ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="alternate" hreflang="en" href="<?= htmlspecialchars(url_with_lang('en')) ?>">
	<link rel="alternate" hreflang="es" href="<?= htmlspecialchars(url_with_lang('es')) ?>">
	<link rel="alternate" hreflang="sv" href="<?= htmlspecialchars(url_with_lang('sv')) ?>">

	<link rel="stylesheet" href="css/styles.css">
	<script defer src="js/actions.js"></script>
	<script defer src="js/styleEffects.js"></script>
</head>

<body>
	<?php include("components/message.php"); ?>
	<?php include("components/front_header.php"); ?>
	<?php include("components/banner_container.php"); ?>
	<?php include("components/descriptions_container.php"); ?>
	<?php include("components/features_container.php"); ?>
	<?php include("components/pricing_container.php"); ?>
	<?php include("components/footer.php"); ?>
</body>

</html>