<?php require_once('logic/stock_be.php'); ?>
<?php include('logic/mini_language_switcher.php'); ?>
<?php include('logic/slug.php'); ?>

<!DOCTYPE html>
<html class="no-js" lang="<?= htmlspecialchars($lang) ?>">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?= htmlspecialchars($metaTitle) ?></title>
	<meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
	<link rel="icon" type="image/png" href="/images/sys-img/asc-favicon.png" />

	<link rel="alternate" hreflang="en" href="<?= htmlspecialchars(url_with_lang('en')) ?>" />
	<link rel="alternate" hreflang="es" href="<?= htmlspecialchars(url_with_lang('es')) ?>" />
	<link rel="alternate" hreflang="sv" href="<?= htmlspecialchars(url_with_lang('sv')) ?>" />
	<link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(url_with_lang('en')) ?>" />
	<link rel="canonical" href="<?= htmlspecialchars($canon) ?>" />

	<!-- ✅ Open Graph básico -->
	<meta property="og:type" content="website">
	<meta property="og:title" content="<?= htmlspecialchars($metaTitle) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
	<meta property="og:url" content="<?= htmlspecialchars($canon) ?>">
	<meta property="og:image" content="https://allstockcontrol.com/images/sys-img/asc-favicon.png">

	<link rel="stylesheet" href="/css/styles.css">

	<script defer src="/js/functions.js"></script>
	<script>
		window.APP_LANG = "<?= htmlspecialchars($lang) ?>";
		window.APP_LANG_FROM_URL = <?= in_array(substr(trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'), 0, 2), ['en','es','sv'], true) ? 'true' : 'false' ?>;

		window.i18n = {
			// toggles
			signup: "<?= htmlspecialchars(tr('toggle_signup')) ?>",
			login:  "<?= htmlspecialchars(tr('toggle_login')) ?>",

			// pricing
			contact: "<?= htmlspecialchars(tr('pricing_contact')) ?>",
			perMonth: "<?= htmlspecialchars(tr('pricing_per_month')) ?>",
			includes: "<?= htmlspecialchars(tr('pricing_includes')) ?>",
			maxMembers: "<?= htmlspecialchars(tr('pricing_max_members')) ?>",
			maxAdmins: "<?= htmlspecialchars(tr('pricing_max_admins')) ?>",
			maxBranches: "<?= htmlspecialchars(tr('pricing_max_branches')) ?>",
			maxProducts: "<?= htmlspecialchars(tr('pricing_max_products')) ?>",
			asAgreed: "<?= htmlspecialchars(tr('pricing_as_agreed')) ?>",
			shipping: "<?= htmlspecialchars(tr('pricing_shipping')) ?>",
			priority: "<?= htmlspecialchars(tr('pricing_priority')) ?>"
		};
	</script>
	
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-0WS3W1169B"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-0WS3W1169B');
	</script>

	<script defer src="/js/actions.js"></script>
	<script defer src="/js/styleEffects.js"></script>
</head>

<body>
	<?php include(__DIR__ . "/logic/pages_router.php"); ?>
	<?php include("components/footer.php"); ?>
</body>

</html>