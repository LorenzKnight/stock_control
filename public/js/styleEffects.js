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

const homeBtn = document.getElementById('home-btn');
if (homeBtn) {
	homeBtn.addEventListener('click', function(e) {
		e.preventDefault();

		scrollToTop();
	});
}

scrollToElementOnClick("pricing-btn", "pricing-container", 50)
// AQUI 



document.addEventListener("DOMContentLoaded", async function () {
	const menuHamburger			= document.getElementById('home-btn-mobile');
	const mobileHeaderAscLogo	= document.getElementById('mobile-header-asc-logo');
	const mobileMenu			= document.getElementById('mobile-menu');
	const floatingHeader		= document.querySelector('.floating-header');

	if (menuHamburger && mobileMenu && floatingHeader) {
		const closeMenu = () => {
			if (!mobileMenu.classList.contains('hidden')) {
				mobileMenu.classList.add('hidden');
			}
			floatingHeader.classList.remove('floating-header-transform');
			menuHamburger.classList.remove('img-color-change');
			if (mobileHeaderAscLogo) mobileHeaderAscLogo.classList.remove('img-color-change');
		};

		const toggleOpen = (e) => {
      		if (e) { e.preventDefault(); e.stopPropagation(); }
			const willOpen = mobileMenu.classList.contains('hidden'); // estado antes del toggle
			
			if (willOpen) {
				mobileMenu.classList.toggle('hidden');
				floatingHeader.classList.add('floating-header-transform');
				menuHamburger.classList.toggle('img-color-change');
				if (mobileHeaderAscLogo) mobileHeaderAscLogo.classList.add('img-color-change');
			} else {
				closeMenu();
			}
		};

		menuHamburger.addEventListener('click', toggleOpen, false);
		menuHamburger.addEventListener('touchend', function (e) {
			// evita doble disparo (touch+click) en iOS
			e.preventDefault();
			toggleOpen(e);
		}, { passive: false });

		// (opcional) cerrar al hacer clic fuera
		document.addEventListener('click', function (e) {
			if (!mobileMenu.classList.contains('hidden') &&
				!mobileMenu.contains(e.target) &&
				!menuHamburger.contains(e.target)) {
					closeMenu();
			}
		}, true);
	}

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

//################################################################ FUNCTIONS ################################################################

function scrollToTop() {
	if (window.scrollY > 0) {
		window.scrollTo({
			top: 0,
			behavior: 'smooth'
		});
	}
};

function scrollToElementOnClick(triggerId, targetId, offset = 0) {
	const trigger = document.getElementById(triggerId);
	const target = document.getElementById(targetId);

	if (trigger && target) {
		trigger.addEventListener("click", function (e) {
		e.preventDefault();
		const elementPosition = target.getBoundingClientRect().top + window.pageYOffset;
		const offsetPosition = elementPosition - offset;

		window.scrollTo({
			top: offsetPosition,
			behavior: "smooth"
		});
		});
	} else {
		console.warn(`Elemento con ID '${triggerId}' o '${targetId}' no encontrado.`);
	}
}