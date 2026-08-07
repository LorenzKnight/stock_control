<!-- Modal de bienvenida -->
<div class="bg-popup" id="welcome-onboarding-modal">
	<div class="welcome-modal-card">
		<div class="welcome-icon" aria-hidden="true">
			<span>📦</span>
		</div>

		<p class="welcome-eyebrow">
			<?= tr('welcome_to_allstockcontrol') ?>
		</p>

		<h1><?= tr('start_your_inventory') ?></h1>

		<p class="welcome-description">
			<?= tr('welcome_first_product_description') ?>
		</p>

		<div class="welcome-action-card">
			<div class="welcome-action-number">
				1
			</div>

			<div class="welcome-action-content">
				<strong>
					<?= tr('create_first_product') ?>
				</strong>

				<span>
					<?= tr('create_first_product_help') ?>
				</span>
			</div>

			<div class="welcome-product-icon" aria-hidden="true">
				<svg
					viewBox="0 0 64 64"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						d="M12 20L31 10L50 20V42L31 53L12 42V20Z"
						stroke="currentColor"
						stroke-width="2.6"
						stroke-linejoin="round"
					/>
					<path
						d="M12 20L31 31L50 20"
						stroke="currentColor"
						stroke-width="2.6"
						stroke-linejoin="round"
					/>
					<path
						d="M31 31V53"
						stroke="currentColor"
						stroke-width="2.6"
					/>

					<circle
						cx="49"
						cy="49"
						r="11"
						fill="#ffffff"
						stroke="currentColor"
						stroke-width="2.6"
					/>

					<path
						d="M49 44V54M44 49H54"
						stroke="currentColor"
						stroke-width="2.6"
						stroke-linecap="round"
					/>
				</svg>
			</div>
		</div>

		<button class="welcome-primary-btn" id="start-first-product">
			<span>
				<?= tr('create_my_first_product') ?>
			</span>

			<svg
				viewBox="0 0 24 24"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
				aria-hidden="true"
			>
				<path
					d="M5 12H19M14 7L19 12L14 17"
					stroke="currentColor"
					stroke-width="2"
					stroke-linecap="round"
					stroke-linejoin="round"
				/>
			</svg>
		</button>

		<button class="welcome-secondary-btn" id="explore-dashboard-first">
			<span><?= tr('explore_dashboard_first') ?></span>
			<span aria-hidden="true">›</span>
		</button>
	</div>
</div>

<!-- Modal de recompensa: primer producto -->
<div class="bg-popup" id="first-product-reward-modal">
	<div class="onboarding-reward-card">

		<div class="onboarding-reward-icon" aria-hidden="true">
			<span class="reward-icon">🎉</span>
		</div>

		<p class="onboarding-reward-eyebrow">
			<?= tr('first_product_reward_eyebrow') ?>
		</p>

		<h2 id="first-product-reward-title">
			<?= tr('first_product_reward_title') ?>
		</h2>

		<p
			class="onboarding-reward-description"
			id="first-product-reward-description"
		>
			<?= tr('first_product_reward_description') ?>
		</p>

		<div class="onboarding-reward-product">
			<span><?= tr('product') ?>:</span>

			<strong id="first-product-reward-name">
				—
			</strong>
		</div>

		<button
			type="button"
			class="onboarding-reward-primary"
			id="view-first-product"
		>
			<?= tr('view_my_product') ?>
			<span aria-hidden="true">→</span>
		</button>

		<button
			type="button"
			class="onboarding-reward-secondary"
			id="create-another-product"
		>
			<?= tr('create_another_product') ?>
		</button>

	</div>
</div>

<!-- Futuro: recompensa primera venta -->
<div class="bg-popup" id="first-sale-reward-modal">
	<!-- contenido futuro -->
</div>