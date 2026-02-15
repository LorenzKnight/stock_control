document.addEventListener("DOMContentLoaded", async function () {
    // 📌 Manejo del formulario de subscripcion y checkout via Stripe
	let formSubscription = document.getElementById('formSubscription');
	if (formSubscription) {
		const STRIPE_PK_LIVE = 'REMOVED_STRIPE_LIVE_PUBLIC';
		const STRIPE_PK_TEST = 'REMOVED_STRIPE_TEST_PUBLIC';

		let stripe;
		
		formSubscription.addEventListener('submit', async function (e) {
			e.preventDefault();

			if (typeof window.Stripe !== 'function') {
				alert('Stripe.js no se cargó. Revisa <script src="https://js.stripe.com/v3"></script> y tu CSP.');
				return;
			}
			stripe = stripe || window.Stripe(STRIPE_PK_TEST); // Cambia a STRIPE_PK_LIVE en producción

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/checkout.php', {
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
						stripe.redirectToCheckout({ sessionId: data.sessionId }); // RESOLVER ESTO
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