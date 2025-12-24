<div class="floating-logo">
    <img src="/images/sys-img/asc-logo.png" alt="Logo" class="asc-logo">
</div>

<div class="floating-header">
    <ul class="menu">
        <li class="no-redirect">
			<a href="" class="start-btn">
				<?= htmlspecialchars($t['nav_start']) ?>
			</a>
		</li>
        <li class="no-redirect">
			<a href="" class="features-btn">
				<?= htmlspecialchars($t['nav_features']) ?>
			</a>
		</li>
        <li class="no-redirect">
			<a href="" class="pricing-btn">
				<?= htmlspecialchars($t['nav_pricing']) ?>
			</a>
		</li>
        <li class="no-redirect">
			<a href="" class="toggle-link">
				<?= htmlspecialchars($t['nav_signup']) ?>
			</a>
		</li>

		<!-- <li class="no-redirect">
			<nav class="lang-switch" style="font-size: 9px;">
				<a href="<?= htmlspecialchars(url_with_lang('en')) ?>">EN</a> |
				<a href="<?= htmlspecialchars(url_with_lang('es')) ?>">ES</a> |
				<a href="<?= htmlspecialchars(url_with_lang('sv')) ?>">SV</a>
			</nav>
		</li> -->
    </ul>

	<div class="mobile-menu-container">
		<div class="mobile-logo">
			<img src="/images/sys-img/asc-logo.png" alt="Logo" id="mobile-header-asc-logo">
		</div>
		<ul class="menu-hamburger" id="menu-hamburger">
			<li class="no-redirect"><a href="#" id="home-btn-mobile"><img src="images/sys-img/hamburger-menu-icon.png"></a></li>
		</ul>
	</div>
	<div class="mobile-menu hidden" id="mobile-menu">
		<ul class="mobile-menu-list">
			<li class="no-redirect">
				<a href="" class="start-btn">
					<?= htmlspecialchars($t['nav_start']) ?>
				</a>
			</li>
			<li class="no-redirect">
				<a href="" class="features-btn">
					<?= htmlspecialchars($t['nav_features']) ?>
				</a>
			</li>
			<li class="no-redirect">
				<a href="" class="pricing-btn">
					<?= htmlspecialchars($t['nav_pricing']) ?>
				</a>
			</li>
			<li class="no-redirect">
				<a href="" class="toggle-link">
					<?= htmlspecialchars($t['nav_signup']) ?>
				</a>
			</li>
		</ul>
	</div>
</div>