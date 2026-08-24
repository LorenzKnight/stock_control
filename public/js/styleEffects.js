document.addEventListener("DOMContentLoaded", async function () {

	const DESKTOP_MIN_WIDTH = 885;

	document.addEventListener('scroll', function() {
		const header = document.querySelector('.floating-header');
		const wrapperDarkBlue = document.querySelector('.wrapper-dark-blue');
		const ascLogo = document.querySelector('.asc-logo');

		if (!header || !wrapperDarkBlue || !ascLogo) return;

		const wrapperRect = wrapperDarkBlue.getBoundingClientRect();

		if (wrapperRect.bottom <= 120) {
			header.classList.add('scrolled-out');
			ascLogo.classList.add('color-change');
		} else {
			header.classList.remove('scrolled-out');
			ascLogo.classList.remove('color-change');
		}
	});

	const homeBtns = document.querySelectorAll('.start-btn');
	homeBtns.forEach(btn => {
		btn.addEventListener('click', function(e) {
			e.preventDefault();

			const isMobile = window.matchMedia(`(max-width: ${DESKTOP_MIN_WIDTH - 1}px)`).matches;
			if (isMobile && typeof window.closeMenu === 'function') {
				window.closeMenu();
			}

			scrollToTop();
		});
	});

	// 👉 Asegura que exista una función global (no-op) por si el menú no está en esta vista
	window.closeMenu = window.closeMenu || function () {};

	const menuHamburger      = document.getElementById('home-btn-mobile');
	const mobileHeaderAscLogo= document.getElementById('mobile-header-asc-logo');
	const mobileMenu         = document.getElementById('mobile-menu');
	const floatingHeader     = document.querySelector('.floating-header');

	if (menuHamburger && mobileMenu && floatingHeader) {
		const closeMenu = () => {
			if (!mobileMenu.classList.contains('hidden')) {
				mobileMenu.classList.add('hidden');
			}
			floatingHeader.classList.remove('floating-header-transform');
			menuHamburger.classList.remove('img-color-change');
			if (mobileHeaderAscLogo) mobileHeaderAscLogo.classList.remove('img-color-change');
		};
		// 👉 Exponerla para que otros handlers (como scrollToElementOnClick) puedan llamarla
		window.closeMenu = closeMenu;

		const toggleOpen = (e) => {
			if (e) { e.preventDefault(); e.stopPropagation(); }

			const willOpen = mobileMenu.classList.contains('hidden');
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
			e.preventDefault();
			toggleOpen(e);
		}, { passive: false });

		document.addEventListener('click', function (e) {
			if (!mobileMenu.classList.contains('hidden') &&
				!mobileMenu.contains(e.target) &&
				!menuHamburger.contains(e.target)) {
					closeMenu();
			}
		}, true);
	}

	// ✅ Ahora puedes registrar el scroll-to con cierre de menú en móvil
	scrollToElementOnClick(".features-btn", "features-container", 70);

	scrollToElementOnClick(".pricing-btn", "pricing-container", 50);
	

	const contactBox = document.getElementById('contactBox');
	let originalImg = null;
	if (contactBox) {
		contactBox.addEventListener('click', function (e) {
			// ✅ Cerrar formulario
			const closeBtn = e.target.closest('#closeContactForm');
			if (closeBtn) {
				e.preventDefault();
				e.stopPropagation();

				contactBox.classList.remove('expanded');

				const form = contactBox.querySelector('#contactForm');

				if (form) {
					form.style.opacity = '0';

					setTimeout(() => {
						form.style.display = 'none';
					}, 200);
				}

				if (originalImg) {
					contactBox.prepend(originalImg);
					originalImg = null;
				}

				return;
			}

			// ✅ Abrir formulario
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
						form.style.opacity = '1';
					}, 10);
				}
			}
		});
	}

	// ############################ FUNCTIONS ############################

	function scrollToTop() {
		if (window.scrollY > 0) {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		}
	}

	function scrollToElementOnClick(triggerSelector, targetId, offset = 0, desktopMinWidth = DESKTOP_MIN_WIDTH) {
		const triggers = document.querySelectorAll(triggerSelector);
		const target   = document.getElementById(targetId);
		if (!triggers.length || !target) return;

		const handler = (e) => {
			e.preventDefault();
			const isMobile = window.matchMedia(`(max-width: ${desktopMinWidth - 1}px)`).matches;
			// 👉 Llama a la global expuesta
			if (isMobile && typeof window.closeMenu === 'function') {
				window.closeMenu();
			}
			const elementPosition = target.getBoundingClientRect().top + window.pageYOffset;
			const offsetPosition  = elementPosition - offset;
			window.scrollTo({ top: offsetPosition, behavior: "smooth" });
		};

		triggers.forEach(el => el.addEventListener('click', handler));
	}
});