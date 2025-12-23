<?php require_once('logic/stock_be.php'); ?>
<?php include('logic/mini_language_switcher.php'); ?>

<!DOCTYPE html>
<html class="no-js" lang="<?= htmlspecialchars($lang) ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<title><?= htmlspecialchars($t['title']) ?></title>
	<meta name="description" content="<?= htmlspecialchars($t['description']) ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="icon" type="image/png" href="images/sys-img/asc-favicon.png" />

	<link rel="alternate" hreflang="en" href="<?= htmlspecialchars(url_with_lang('en')) ?>" />
	<link rel="alternate" hreflang="es" href="<?= htmlspecialchars(url_with_lang('es')) ?>" />
	<link rel="alternate" hreflang="sv" href="<?= htmlspecialchars(url_with_lang('sv')) ?>" />
	<link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(url_with_lang('en')) ?>" />

	<!-- ✅ Open Graph básico -->
	<meta property="og:type" content="website">
	<meta property="og:title" content="<?= htmlspecialchars($t['title']) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($t['description']) ?>">
	<meta property="og:url" content="<?= htmlspecialchars(url_with_lang($lang)) ?>">
	<meta property="og:image" content="https://allstockcontrol.com/images/sys-img/asc-favicon.png">

	<link rel="stylesheet" href="/css/styles.css">

	<script>
		window.APP_LANG = "<?= htmlspecialchars($lang) ?>";
		window.APP_LANG_FROM_URL = <?= in_array(substr(trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'), 0, 2), ['en','es','sv'], true) ? 'true' : 'false' ?>;

		window.i18n = {
			signup: "<?= htmlspecialchars($t['toggle_signup']) ?>",
			login:  "<?= htmlspecialchars($t['toggle_login']) ?>"
		};
	</script>

	<script defer src="/js/actions.js"></script>
	<script defer src="/js/styleEffects.js"></script>
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