<header>
	<div class="header-container">
		<nav>
			<div style="flex: 1;">
				<div class="logo">
					<a href="profile"><h3>All<strong>Stock</strong>Control</h3></a>
				</div>
			</div>
			<ul class="menu" id="header-menu">
				<li class="no-redirect" id="home-site" data-page="stock">
					<img src="/images/sys-img/home-icon.png" alt="Home" id="notification-icon" class="home-icon">
				</li>

				<li id="btn-create-product" data-page="products">
					<?= htmlspecialchars(tr('header_products') ?? 'Products') ?>
				</li>

				<li data-page="storage">
					<?= htmlspecialchars(tr('header_storage') ?? 'Storage') ?>
				</li>

				<li id="btn-create-client" data-page="customers">
					<?= htmlspecialchars(tr('header_customers') ?? 'Customers') ?>
				</li>

				<li class="hidden" id="shipping-seccion" data-page="shipping">
					<?= htmlspecialchars(tr('header_shipping') ?? 'Shipping') ?>
				</li>

				<li id="btn-create-sale" data-page="sales">
					<?= htmlspecialchars(tr('header_sales') ?? 'Sales') ?>
				</li>

				<li data-page="payments">
					<?= htmlspecialchars(tr('header_payments') ?? 'Payments') ?>
				</li>

				<li class="no-redirect" id="notification-site" data-page="notifications">
					<img src="/images/sys-img/notif.png" alt="Notification" id="notification-icon" class="notification-icon">
					<div class="notifications-no" id="notif-count" style="display: none;"></div>
				</li>

				<li class="no-redirect">
					<div class="profile" id="profileTrigger">
						<img id="header-profile-pic" src="" alt="header profile pic">
					</div>

					<div id="profileDropdown" style="display: none;">
						<ul>
							<li class="no-redirect" style="border-bottom: 1px solid var(--clr-light-border);">
								<a href="<?= htmlspecialchars(localized_url('profile')) ?>" style="margin: 0 auto; font-weight: bold;" id="my-name"></a>
							</li>

							<li id="reports-site" data-page="reports">
								<a href="<?= htmlspecialchars(localized_url('reports')) ?>">
									<?= htmlspecialchars(tr('header_reports') ?? 'Reports') ?>
								</a>
							</li>

							<li>
								<a href="<?= htmlspecialchars(localized_url('settings')) ?>">
									<?= htmlspecialchars(tr('header_settings') ?? 'Settings') ?>
								</a>
							</li>

							<li id="system-admin-site">
								<a href="<?= htmlspecialchars(localized_url('system-admin')) ?>">
									<?= htmlspecialchars(tr('header_system_admin') ?? 'System-Admin') ?>
								</a>
							</li>

							<li class="no-redirect" style="border-top: 1px solid var(--clr-light-border);">
								<a href="#" style="margin: 0 auto;" class="logout-button turn-off">
									<?= htmlspecialchars(tr('header_logout') ?? 'Log Out') ?>
								</a>
							</li>
						</ul>
					</div>
				</li>
			</ul>
		</nav>
	</div>

	<div id="onboarding-progress" class="onboarding-box hidden">
		<div class="onboarding-header">
			<strong><?= htmlspecialchars(tr('get_your_inventory_ready') ?? 'Get your inventory ready') ?></strong>
			<span id="onboarding-percent">0%</span>
		</div>

		<div class="onboarding-bar">
			<div id="onboarding-bar-fill"></div>
		</div>

		<ul class="onboarding-steps">
			<li id="step-company"><?= htmlspecialchars(tr('company_setup') ?? 'Set up your company profile') ?></li>
			<li id="step-product"><?= htmlspecialchars(tr('create_first_product') ?? 'Create your first product') ?></li>
			<li id="step-client"><?= htmlspecialchars(tr('add_first_customer') ?? 'Add your first customer') ?></li>
			<li id="step-sale"><?= htmlspecialchars(tr('record_your_first_sale') ?? 'Record your first sale') ?></li>
		</ul>
	</div>
</header>