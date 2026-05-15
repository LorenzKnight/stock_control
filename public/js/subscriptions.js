document.addEventListener("DOMContentLoaded", async function () {
    // 📌 Manejo del formulario de subscripcion y checkout via Stripe
	let formSubscription = document.getElementById('formSubscription');
	if (formSubscription) {
		let stripe;

		async function getStripeInstance() {
			if (stripe) return stripe;

			const res = await fetch('/inc/public_key_config.php', {
			method: 'GET',
			headers: { 'Accept': 'application/json' }
			});

			const cfg = await res.json();
			if (!cfg.success || !cfg.stripe_pk) {
				throw new Error(cfg.message || 'Stripe config not available.');
			}

			if (typeof window.Stripe !== 'function') {
				throw new Error('Stripe.js no se cargó. Revisa <script src="https://js.stripe.com/v3"></script> y tu CSP.');
			}

			stripe = window.Stripe(cfg.stripe_pk);
			return stripe;
		}
		
		formSubscription.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			const lang = typeof getCurrentLang === 'function'
				? getCurrentLang()
				: (window.APP_LANG || 'en');

			formData.append('lang', lang);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				const stripeInstance = await getStripeInstance();

				let response = await fetch('/api/checkout.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				showBanner(banner);

				if (data.success && data.sessionId) {
					// Espera medio segundo antes de redirigir a Stripe
					setTimeout(() => {
						stripeInstance.redirectToCheckout({ sessionId: data.sessionId });
					}, 500);
				} else if (data.success && data.redirect_url) {
					// Caso anterior: redirección manual
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);
				}
			} catch (error) {
				statusText.innerText = "Error processing request.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}
});