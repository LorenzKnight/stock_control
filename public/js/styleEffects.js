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

document.addEventListener("DOMContentLoaded", async function () {
	const contactBox = document.getElementById('contactBox');
	let originalImg = null;

    contactBox.addEventListener('click', function () {
        if (!contactBox.classList.contains('expanded')) {
			const img = contactBox.querySelector('img');
            if (img) {
				originalImg = img.cloneNode(true);
                img.remove();
            }
            contactBox.classList.add('expanded');

			const form = contactBox.querySelector('form');
			if (form) {
				form.style.display = 'flex';
				setTimeout(() => {
					form.style.opacity = 1;
				}, 10);
			}
        }
    });

    // Ejemplo para botón de cerrar
    const closeBtn = document.getElementById('closeContactForm');
    if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation(); // Evita que el click vuelva a activar

            // Quitar clase expandido
            contactBox.classList.remove('expanded');

            // Ocultar formulario si es necesario
            const form = contactBox.querySelector('form');
            if (form) {
                form.style.display = 'none';
                form.style.opacity = 0;
            }

            // Volver a insertar la imagen si la teníamos guardada
            if (originalImg) {
                contactBox.prepend(originalImg);
                originalImg = null; // Reset
            }
        });
    }
});