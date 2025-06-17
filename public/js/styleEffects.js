document.addEventListener('scroll', function() {
	const header = document.querySelector('.floating-header');
	const blueCurve = document.querySelector('.blue-curve');
	const ascLogo = document.querySelector('.asc-logo');

	if (!header || !blueCurve || !ascLogo) return; // Seguridad

	const wrapperRect = blueCurve.getBoundingClientRect();

	// Verificamos si el header ha salido del wrapper
	if (wrapperRect.bottom <= 120) {
		header.classList.add('scrolled-out');
		ascLogo.classList.add('color-change');
	} else {
		header.classList.remove('scrolled-out');
		ascLogo.classList.remove('color-change');
	}
});