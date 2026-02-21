<div class="wrapper-dark-blue">
	<div class="banner-container banner-height" id="result-container">
		<div class="container-right" style="color: var(--clr-white);">
			<?php include("components/modal_signup.php"); ?>
			<div class="positioning" id="container-login-info" style="display: block;">
				<h1><?= htmlspecialchars($t['home_main_h1']) ?></h1>
				 <h2><?= htmlspecialchars($t['home_main_subtitle']) ?></h2>
				
				 <p>
					<a href="#" class="toggle-link" style="color: var(--warning-orange); text-decoration: none;">
						<?= htmlspecialchars($t['home_cta']) ?>
					</a>
				</p>
				<small class="cta-note"><?= htmlspecialchars($t['cta_note']) ?></small>
			</div>
		</div>
		<div class="container-left" style="color: var(--clr-white);">
			<?php include("components/modal_login.php"); ?>
			<div class="positioning" id="container-signup-info" style="display: none;">
				<p class="alt-h1"><?= htmlspecialchars($t['signup_message']) ?></p>
			</div>
		</div>
	</div>
</div>
<div class="blue-curve"></div>