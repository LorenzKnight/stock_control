document.addEventListener('scroll', function() {
	const header = document.querySelector('.floating-header');
	const blueCurve = document.querySelector('.blue-curve');

	if (!header || !blueCurve) return; // Seguridad

	const wrapperRect = blueCurve.getBoundingClientRect();

	// Verificamos si el header ha salido del wrapper
	if (wrapperRect.bottom <= 120) {
		header.classList.add('scrolled-out');
	} else {
		header.classList.remove('scrolled-out');
	}
});