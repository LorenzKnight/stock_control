document.addEventListener("DOMContentLoaded", async function () {
	function openCompanyModal() {
		const editCompanyForm = document.getElementById('edit-company-form');
		const companyPopupContent = editCompanyForm?.querySelector('.formular-medium-frame');

		if (editCompanyForm && companyPopupContent) {
			editCompanyForm.style.display = 'block';
			editCompanyForm.style.opacity = '0';
			editCompanyForm.style.transition = 'opacity 300ms ease';

			void editCompanyForm.offsetWidth;
			editCompanyForm.style.opacity = '1';

			companyPopupContent.style.transform = 'scale(0.7)';
			companyPopupContent.style.opacity = '0';
			companyPopupContent.style.transition = 'transform 300ms ease, opacity 300ms ease';

			void companyPopupContent.offsetWidth;
			companyPopupContent.style.transform = 'scale(1)';
			companyPopupContent.style.opacity = '1';

			if (!editCompanyForm.dataset.ddInit && typeof initDragAndDrop === 'function') {
				initDragAndDrop('company-logo-drop-area', 'company_logo', 'logo-preview');
				editCompanyForm.dataset.ddInit = '1';
			}
		}
	}

	function bindSubmitCompanyInfo() {
		const submitCompanyInfo = document.getElementById('submit-company-info');
		if (!submitCompanyInfo) return;

		if (submitCompanyInfo.dataset.bound === '1') return;
		submitCompanyInfo.dataset.bound = '1';

		submitCompanyInfo.addEventListener('click', async (e) => {
			e.preventDefault();

			// --- Banner global (ya existe en components/message.php) ---
			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			// --- 1) Validar checks ---
			const termsCheck = document.getElementById('terms-check');
			const privacyCheck = document.getElementById('privacy-check');

			if (!termsCheck?.checked || !privacyCheck?.checked) {
				statusText.innerText = "Please accept Terms and Privacy Policy to continue.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
				return;
			}

			// --- 2) Guardar aceptación en backend ---
			const formData = new FormData();
			formData.set('terms', '1');
			formData.set('gdpr', '1');

			try {
				const resp = await fetch('/api/update_accept_legal.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				const data = await resp.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;

					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							// aquí NO redirigimos; solo seguimos con tu flujo actual
							// (cerrar setup-form y luego abrir company modal si aplica)
						});
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);
					return;
				}
			} catch (error) {
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
				return;
			}

			// --- 3) Cerrar setup-form ---
			const setUp = document.getElementById('setup-form');
			const popupContent = setUp?.querySelector('.formular-frame');

			if (setUp && popupContent) {
				setUp.style.transition = 'opacity 300ms ease';
				setUp.style.display = 'block';
				void setUp.offsetWidth;
				setUp.style.opacity = '0';

				setUp.addEventListener('transitionend', () => {
					setUp.style.display = 'none';
					setUp.style.opacity = '';
					setUp.style.transition = '';
				}, { once: true });
			}

			let response = await fetch('/api/get_my_info.php', {
				method: 'GET',
				headers: { Accept: 'application/json' }
			});

			let data = await response.json();

			if (data.success && data.data) {
				let user = data.data;

				const companyId = Number(user.company_id);

				if (!Number.isFinite(companyId) || companyId <= 0) {
					openCompanyModal();
				}
			}
		});
	}

	// --- Onboarding guide --- REVISA PARA QUE NO SALGA EL LOG EN LA CONSOLA (solo es para debug)
	const onboardingBox = document.getElementById('onboarding-progress');
	if (onboardingBox) {
		const onboardingStatus = {
			company: false,
			product: false,
			client: false,
			sale: false
		};

		try {
			const response = await fetch('/api/get_my_info.php', {
				method: 'GET',
				headers: { Accept: 'application/json' }
			});

			const data = await response.json();

			if (data.success && data.data?.onboarding_progress) {
				const progress = data.data.onboarding_progress;

				onboardingStatus.company = progress.company === true || progress.company === 't';
				onboardingStatus.product = progress.product === true || progress.product === 't';
				onboardingStatus.client = progress.client === true || progress.client === 't';
				onboardingStatus.sale = progress.sale === true || progress.sale === 't';
			}
		} catch (error) {
			console.error('Error fetching onboarding progress:', error);
		}

		updateOnboardingProgress(onboardingStatus);

		const hasSeenOnboarding = localStorage.getItem('hasSeenOnboarding');

		// console.log('hasSeenOnboarding:', hasSeenOnboarding);

		if (!hasSeenOnboarding) {
			setTimeout(() => {
				startOnboardingGuide();
				localStorage.setItem('hasSeenOnboarding', 'true');
			}, 500);
		}
	}

	// --- Init flow ---
	const setUp = document.getElementById('setup-form');
	if (setUp) {
		const popupContent = setUp.querySelector('.formular-frame');
		try {
			let response = await fetch('/api/get_my_info.php', {
				method: 'GET',
				headers: { Accept: 'application/json' }
			});

			let data = await response.json();

			if (data.success && data.data) {
				let user = data.data;

				// 1) Si NO aceptó gdpr/terms -> mostrar setup
				if (user.gdpr === "0" || user.terms === "0") {
					setUp.style.display = 'block';
					setUp.style.opacity = '0';
					setUp.style.transition = 'opacity 0.5s ease';
					setTimeout(() => {
						setUp.style.opacity = '1';
					}, 10);

					popupContent.style.transform = 'scale(0.7)';
					popupContent.style.opacity = '0';
					popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
					setTimeout(() => {
						popupContent.style.transform = 'scale(1)';
						popupContent.style.opacity = '1';
					}, 50);
				}
				// 2) Si YA aceptó gdpr/terms pero NO tiene company -> abrir modal de company automáticamente
				else if (!Number.isFinite(Number(user.company_id)) || Number(user.company_id) <= 0) {
					openCompanyModal();
				}
			}
		} catch (error) {
			console.error("Error fetching setup data:", error);
		}
	}
	// Bind del botón siempre (exista o no setup)
	bindSubmitCompanyInfo();

	// 📌 Manejo del formulario terms y gdpr (habilitar/deshabilitar botón submit-company-info)
	const termsCheck = document.getElementById('terms-check');
	const privacyCheck = document.getElementById('privacy-check');
	const submitBtn = document.getElementById('submit-company-info');

	if (termsCheck && privacyCheck && submitBtn && typeof bindSubmitToCheckboxes === 'function') {
		bindSubmitToCheckboxes({
			checkboxIds: ['terms-check', 'privacy-check'],
			submitId: 'submit-company-info',
		});
	}


	// 📌 Mostrar mensaje de pago (si viene por URL)
	if (window.paymentMessage) {
		const banner = document.getElementById('status-message');
		const statusText = document.getElementById('status-text');
		const statusImage = document.getElementById('status-image');

		statusText.innerText = window.paymentMessage;
		statusImage.src = "../images/sys-img/loading1.gif";

		showBanner(banner);

		setTimeout(() => {
			hideBanner(banner);
		}, 3000);
	}
	
	// 📌 Redireccionar al hacer clic en Menu
	function getCurrentLang() {
		const supported = ['en', 'es', 'sv'];

		const pathParts = window.location.pathname.split('/').filter(Boolean);
		const urlLang = pathParts[0];

		if (supported.includes(urlLang)) {
			return urlLang;
		}

		if (window.APP_LANG && supported.includes(window.APP_LANG)) {
			return window.APP_LANG;
		}

		const browserLang = (navigator.language || '').slice(0, 2).toLowerCase();

		if (supported.includes(browserLang)) {
			return browserLang;
		}

		return 'en';
	}

	function localizedPath(page) {
		const lang = getCurrentLang();

		page = String(page || '').trim();
		page = page.replace(/^\/+/, '');
		page = page.replace(/\.php$/, '');

		return `/${lang}/${page}`;
	}

	document.querySelectorAll('.menu li[data-page]').forEach(item => {
		if (!item.classList.contains('no-redirect')) {
			item.addEventListener('click', function () {
				const page = this.dataset.page;
				window.location.href = localizedPath(page);
			});
		}

		const pathParts = window.location.pathname.split('/').filter(Boolean);

		let currentPage = pathParts[pathParts.length - 1] || 'stock';
		currentPage = currentPage.replace('.php', '');

		if (item.dataset.page === currentPage) {
			item.classList.add('active');
		}
	});

	let homeSite = document.getElementById('home-site');
	if (homeSite) {
		homeSite.addEventListener('click', function () {
			window.location.href = localizedPath('profile');
		});
	}

	let notificationSite = document.getElementById('notification-site');
	if (notificationSite) {
		notificationSite.addEventListener('click', function () {
			window.location.href = localizedPath('notifications');
		});
	}

	// 📌 Mostrar y ocultar menú de perfil
	var profileTrigger = document.getElementById('profileTrigger');
	var profileDropdown = document.getElementById('profileDropdown');
	
	if (profileTrigger && profileDropdown) {
		profileTrigger.addEventListener('click', function() {
			profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
		});

		document.addEventListener('click', function(event) {
			if (!profileTrigger.contains(event.target) && !profileDropdown.contains(event.target)) {
				profileDropdown.style.display = 'none';
			}
		});
	}

	// 📌 Manejo del formulario de registro
	const signupTermsCheck = document.getElementById('signup-terms-check');
    const signupPrivacyCheck = document.getElementById('signup-privacy-check');
    const submitSignupBtn = document.getElementById('submit-signup');

    if (signupTermsCheck && signupPrivacyCheck && submitSignupBtn) {
        await bindSubmitToCheckboxes({
			checkboxIds: ['signup-terms-check', 'signup-privacy-check'],
			submitId: 'submit-signup',
		});
    }

	let formSignUp = document.getElementById('formsignup');
	if (formSignUp) {
		formSignUp.addEventListener('submit', async function (e) {
			e.preventDefault();

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			const termsEl = document.getElementById('signup-terms-check');
			const privacyEl = document.getElementById('signup-privacy-check');

			// ✅ validar checks antes de enviar
			if (!termsEl?.checked || !privacyEl?.checked) {
				statusText.innerText = "Please accept Terms and Privacy Policy to continue.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
				return;
			}

			const password = document.getElementById('password').value.trim();
			const confirmPassword = document.getElementById('confirm_password').value.trim();

			if (password !== confirmPassword) {
				statusText.innerText = "Error: Passwords do not match.";
				statusImage.src = "../images/sys-img/error.gif";

				showBanner(banner);
				return;
			}

			let formData = new FormData(this);

			formData.set('terms', '1');
    		formData.set('gdpr', '1');
	
			try {
				let response = await fetch('api/signup.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;

					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = data.img_gif;
				showBanner(banner);
			}
		});
	}

	// 📌 Manejo del formulario de login
	let formlogin = document.getElementById('formlogin');
	if (formlogin) {
		formlogin.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/login.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);

				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	(function () {
		const wrapper = document.querySelector('.opcions-packages');
		if (!wrapper) return;

		wrapper.querySelectorAll('.packs-selection[tabindex]').forEach(el => el.removeAttribute('tabindex'));

		const syncSelectedClass = () => {
			const checked = wrapper.querySelector('input[name="group-pack"]:checked');
			wrapper.querySelectorAll('.packs-selection').forEach(el => {
				el.classList.toggle('selected-pack-group', checked && el.contains(checked));
			});
		};

		syncSelectedClass();

		wrapper.addEventListener('mousedown', (e) => {
			const container = e.target.closest('.packs-selection');
			if (!container) return;
			e.preventDefault();
		});

		wrapper.addEventListener('click', (e) => {
			const container = e.target.closest('.packs-selection');
			if (!container) return;

			const radio = container.querySelector('input[type="radio"][name="group-pack"]');
			if (!radio || radio.disabled) return;

			const prevY = window.scrollY;

			if (!radio.checked) {
				try { radio.focus({ preventScroll: true }); }
				catch { radio.focus(); window.scrollTo(0, prevY); }

				radio.checked = true;
				radio.dispatchEvent(new Event('change', { bubbles: true }));
			}

			syncSelectedClass();
			
			if (window.scrollY !== prevY) window.scrollTo(0, prevY);
		});

		wrapper.addEventListener('keydown', (e) => {
			if (!e.target.matches('input[name="group-pack"]')) return;
			if (e.key === ' ' || e.key === 'Spacebar') e.preventDefault();
		});

		wrapper.addEventListener('change', (e) => {
			if (e.target.matches('input[name="group-pack"]')) syncSelectedClass();
		});
	})();

	document.addEventListener('change', function (e) {
		if (!e.target.matches('input[name="group-pack"]')) return;

		const threshold = parseInt(e.target.value, 10) || 0;
		const container = document.querySelector('.pricing-container');
		if (!container) return;

		container.innerHTML = '<p style="text-align:center;padding:12px">Loading...</p>';

		const params = new URLSearchParams();
		if (threshold) params.append('min_members', String(threshold));
		params.append('limit', '3');
		params.append('sort', 'package_price');
		params.append('dir', 'ASC');

		const url = `api/get_packs_front.php?${params.toString()}`;

		fetch(url, {
			method: 'GET',
			headers: { 'Accept': 'application/json' },
			credentials: 'omit' // no enviar cookies/sesión
		})
		.then(async (res) => {
			const ct  = res.headers.get('content-type') || '';
			const raw = await res.text();

			// Intenta parsear JSON
			let data;
			try {
				data = JSON.parse(raw);
			} catch (e) {
				throw new Error('Respuesta no JSON del servidor');
			}

			if (!res.ok) {
				throw new Error(`HTTP ${res.status}: ${data?.message || 'Request failed'}`);
			}

			return data;
		})
		.then(data => {
			if (!data.success || !Array.isArray(data.packages)) {
				container.innerHTML = `<p style="text-align:center;padding:12px;color:#c00">
					${data.message ? `Error: ${data.message}` : 'Error loading packages.'}
				</p>`;
				return;
			}

			const esc = (s) => String(s ?? '')
			.replace(/&/g,'&amp;').replace(/</g,'&lt;')
			.replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');

			const colorVarByIndex = (i) => {
				const vars = ['--plus-turquoise','--max-blue','--ultra-purple'];
				return vars[i % vars.length];
			};

			// Normaliza SOLO columnas existentes
			const norm = (p) => {
				const m = p.members_limit;
				return {
					id: p.package_id,
					name: p.package_name || 'Package',
					img: p.package_image || null,
					desc: p.package_description || null,
					price: p.package_price ?? null,
					members: (m == null || m === '') ? null : Number(m), // null = ilimitado
					admins: p.admins_limit ?? null,
					branches: p.branch_affiliate_limit ?? null,
					products: p.products_limit ?? null,
					duration: p.package_duration ?? null
				};
			};

			// Fallback: ilimitados o >= threshold
			let list = data.packages.map(norm);
			if (threshold) list = list.filter(pkg => pkg.members == null || pkg.members >= threshold);

			if (!list.length) {
				container.innerHTML = '<p style="text-align:center;padding:12px">No packages match this selection.</p>';
				return;
			}

			const t = window.i18n || {};
			
			const html = list.map((pkg, i) => {
				const cvar = colorVarByIndex(i);

				return `
				<div class="pricing-card">
					<div class="pricing-header">
						<div class="pricing-price" style="color: var(${cvar});">
							${esc(pkg.name)}
						</div>
						${pkg.price != null 
							? `<div class="pricing-price">$${esc(pkg.price)}</div>` 
							: `<h2>${esc(t.contact)}</h2>`
						}
						${pkg.price != null 
							? `<h2>${esc(t.perMonth)}</h2>` 
							: ''
						}
					</div>
					<div class="pricing-header-comp" style="background-color: var(${cvar});"></div>
					<div class="pricing-content">
						<div class="pricing-desc">
							${pkg.desc ? `<p class="pkg-desc">${esc(pkg.desc)}</p>` : ''}
						</div>
						<h2>${esc(t.includes)}</h2>
						<ul>
							${pkg.members != null
								? `<li>${esc(t.maxMembers)}: ${esc(pkg.members)}</li>`
								: `<li>${esc(t.maxMembers)}: ${esc(t.asAgreed)}</li>`
							}

							${pkg.admins != null
								? `<li>${esc(t.maxAdmins)}: ${esc(pkg.admins)}</li>`
								: `<li>${esc(t.maxAdmins)}: ${esc(t.asAgreed)}</li>`
							}

							${pkg.branches != null
								? `<li>${esc(t.maxBranches)}: ${esc(pkg.branches)}</li>`
								: `<li>${esc(t.maxBranches)}: ${esc(t.asAgreed)}</li>`
							}

							${pkg.products == null
								? `<li>${esc(t.maxProducts)}: ${esc(t.asAgreed)}</li>`
								: ''
							}

							${pkg.products == null
								? `<li>${esc(t.shipping)}</li>`
								: ''
							}

							${pkg.members >= 10 || pkg.products == null
								? `<li>${esc(t.priority)}</li>`
								: ''
							}
						</ul>
					</div>
					<!-- <button class="access-btn" data-package-id="${esc(pkg.id)}">Select</button> -->
				</div>`;
			}).join('');

			container.innerHTML = html;
		})
		.catch(err => {
			console.error('Error loading packages:', err);
			container.innerHTML = '<p style="text-align:center;padding:12px;color:#c00">Network or JSON error.</p>';
		});
	});
	
	let r = document.querySelector('input[name="group-pack"]:checked') || document.querySelector('input[name="group-pack"]');
	if (r) {
		r.checked = true;
		r.dispatchEvent(new Event('change', { bubbles: true }));
	}


	// 📌 Manejo del botón de logout
	document.querySelectorAll('.logout-button').forEach((btn) => {
		btn.addEventListener('click', onLogoutClick);
	});

	async function onLogoutClick(e) {
		e.preventDefault();

		try {
			let response = await fetch('api/logout.php', {
				method: 'POST',
				headers: { 'Accept': 'application/json' }
			});

			let data = await response.json();

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			if (data.success) {
				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				
				showBanner(banner);

				setTimeout(() => {
					hideBanner(banner, () => {
						window.location.href = data.redirect_url;
					});
				}, 3000);
			} else {
				alert("Error al cerrar sesión: " + data.message);
			}
		} catch (error) {
			console.error('Error en la solicitud:', error);
			alert("Error al procesar la solicitud.");
		}
	}

	// 📌 Alternar entre login y signup
	const DESKTOP_MIN_WIDTH = 885;

	let formLogin      = document.getElementById("formular-login");
	let formSignup     = document.getElementById("formular-signup");
	let formLoginInfo  = document.getElementById("container-login-info");
	let formSignupInfo = document.getElementById("container-signup-info");

	const showSignup = (isMobile) => {
		if (formLogin)      formLogin.style.display = "none";
		if (formSignup)     formSignup.style.display = "block";
		if (formLoginInfo)  formLoginInfo.style.display = "none";
		if (formSignupInfo) formSignupInfo.style.display = isMobile ? "none" : "block";
		
		document.querySelectorAll(".toggle-link, .close-link").forEach(a => {
			a.textContent = window.i18n.login;
			a.classList.remove("toggle-link");
			a.classList.add("close-link");
		});
	};

	const showLogin = () => {
		if (formSignup)     formSignup.style.display = "none";
		if (formLogin)      formLogin.style.display = "block";
		if (formSignupInfo) formSignupInfo.style.display = "none";
		if (formLoginInfo)  formLoginInfo.style.display = "block";
		
		document.querySelectorAll(".toggle-link, .close-link").forEach(a => {
			a.textContent = window.i18n.signup;
			a.classList.remove("close-link");
			a.classList.add("toggle-link");
		});
	};

	document.addEventListener("click", (e) => {
		const link = e.target.closest(".toggle-link, .close-link");
		if (!link) return;

		e.preventDefault();
		if (typeof scrollToTopIfNeeded === "function") scrollToTopIfNeeded();

		const isMobile = window.matchMedia(`(max-width: ${DESKTOP_MIN_WIDTH - 1}px)`).matches;
		if (isMobile) window.closeMenu?.();

		if (link.classList.contains("toggle-link")) {
			showSignup(isMobile);
		} else {
			showLogin();
		}
	});

	// 📌 Manejo del datos de usuario
	const headerMenu = document.getElementById("header-menu");
	if (headerMenu) {
		try {
			let response = await fetch('api/get_my_info.php', {
				method: 'GET',
				headers: { Accept: "application/json" }
			});

			let data = await response.json();

			let myName = document.getElementById("my-name");
			let hiUser = document.getElementById("hi-user");
			let myData = document.getElementById("my-data");
			let subsc = document.getElementById("subsc");
			let totalSpot = document.getElementById("total-spot");
			const headerProfilePic = document.getElementById("header-profile-pic");

			if (data.success && data.data) {
				let user = data.data;

				if (hiUser) {
					const greeting = window.i18n?.profile_greeting || 'Hi, ';
					const fallbackName = window.i18n?.user_fallback_name || 'User';

					hiUser.innerHTML = `${greeting}${user.name || fallbackName}!`;
				}

				if (myData) {
					myData.innerHTML =
						`<p><strong>ID:</strong> ${user.user_id?.trim() || "-"}</p>` +
						`<p><strong>${window.i18n?.phone || "Phone"}:</strong> ${user.phone?.trim() || "No Phone Number"}</p>` +
						`<p><strong>Email:</strong> ${user.email?.trim() || "No Email"}</p>`;
					;
				}

				if (subsc) {
					subsc.innerHTML = 
						user.package_info && user.package_info.package_id 
							? `
								<p><strong>Pack:</strong> ${user.package_info.package_name || "No Package"}</p>
								<p><strong>${window.i18n?.smallbox_members || "Members"}:</strong> ${user.package_info.members_limit}</p>
								<p><strong>${window.i18n?.branches || "Branches"}:</strong> ${user.package_info.branch_affiliate_limit}</p>
								<p><strong>${window.i18n?.smallbox_products_limit || "Product Limit"}:</strong> ${user.package_info.products_limit}</p>
							` 
							: "0";
				}
		
				if (totalSpot) {
					totalSpot.innerHTML = user.package_info && user.package_info.package_id ? user.package_info.members_limit : "0";
				}

				if (myName) {
					myName.innerHTML = (String(user.name).trim() || "") + " " + (String(user.surname).trim() || "");
				}

				if (headerProfilePic) {
					const hasCustomImage = user.image && user.image.trim() !== "";
				
					headerProfilePic.src = hasCustomImage
						? `../images/profile/${user.image}`
						: "../images/sys-img/NonProfilePic.png";
				
					headerProfilePic.alt = hasCustomImage
						? "User profile picture"
						: "Default profile picture";
				
					headerProfilePic.classList.remove("default-profile-pic", "custom-profile-pic");
					headerProfilePic.classList.add(hasCustomImage ? "custom-profile-pic" : "default-profile-pic");
				}

				// VERIFICA SI EL PAQUETE DE PRUEBA HA EXPIRADO
				const signup = parseDbTimestamp(user.signup_date);
				const days = Number(user.package_info.package_duration) || 0;

				// Fecha de expiración = fecha de alta + días
				const expirateDate = new Date(signup);
				expirateDate.setDate(expirateDate.getDate() + days);

				const today = new Date();
				today.setHours(0,0,0,0);
				const expDay = new Date(expirateDate);
				expDay.setHours(0,0,0,0);

				const expiratedPack = today >= expDay;

				

				if (parseInt(user.package_id) === 1 && expiratedPack) {
					const activatePackForm = document.getElementById('activate-pack-form');
					const popupContent = activatePackForm.querySelector('.formular-frame');
					if (activatePackForm && popupContent) {
						activatePackForm.style.display = 'block';
						activatePackForm.style.opacity = '0';
						activatePackForm.style.transition = 'opacity 0.5s ease';
						setTimeout(() => {
							activatePackForm.style.opacity = '1';
						}, 10);

						popupContent.style.transform = 'scale(0.7)';
						popupContent.style.opacity = '0';
						popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
						setTimeout(() => {
							popupContent.style.transform = 'scale(1)';
							popupContent.style.opacity = '1';
						}, 50);
					}
				}
			} else {
				if (myData) {
					myData.innerHTML = `<p>No user data found.</p>`;
				}
			}
		} catch (error) {
			console.error("Error fetching data:", error);
			const myData = document.getElementById("my-data");
			if (myData) {
				myData.innerHTML = `<p>Error loading user data.</p>`;
			}
		}
	}

	const upgradePackage = document.getElementById('upgrade-package');
	if (upgradePackage) {
		upgradePackage.addEventListener('click', function() {
			const activatePackForm = document.getElementById('activate-pack-form');
			const popupContent = activatePackForm.querySelector('.formular-frame');

			if (activatePackForm && popupContent) {
				activatePackForm.style.transition = 'opacity 300ms ease';
				activatePackForm.style.display = 'block';
				void activatePackForm.offsetWidth;
				activatePackForm.style.opacity = '0';

				activatePackForm.addEventListener('transitionend', () => {
					activatePackForm.style.display = 'none';
					activatePackForm.style.opacity = '';
					activatePackForm.style.transition = '';
				}, { once: true });
			}

			const subscForm = document.getElementById('subsc-form');
			const popupContentMedium = subscForm.querySelector('.formular-medium-frame');
			const subsCancelBtn = document.getElementById('subs-cancel-btn');
			const subsLogoutBtn = document.getElementById('subs-logout-btn');

			if (subscForm && popupContentMedium) {
				scrollToTopIfNeeded();

				subscForm.style.display = 'block';
				subscForm.style.opacity = '0';
				subscForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					subscForm.style.opacity = '1';
				}, 10);

				popupContentMedium.style.transform = 'scale(0.7)';
				popupContentMedium.style.opacity = '0';
				popupContentMedium.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				setTimeout(() => {
					popupContentMedium.style.transform = 'scale(1)';
					popupContentMedium.style.opacity = '1';
				}, 50);

				populatePackages('packs');

				populateExtraServices('extra_pack');

				// handlePopupClose("subsc-form", ".formular-medium-frame", []);

				subsCancelBtn.classList.add('hidden');
				subsLogoutBtn.classList.remove('hidden');
			}
		});
	}

	const profileData = document.getElementById("profile-data");
	if (profileData) {
		try {
			let response = await fetch('api/get_users.php', {
				method: 'GET',
				headers: { Accept: "application/json" }
			});

			let data = await response.json();
			const spot = document.getElementById("spot");
			
			if (data.success && typeof data.count !== 'undefined') {
				// 👇 Sumar +1 para incluir al dueño del plan
				const totalMembers = parseInt(data.count || 0) + 1;
				spot.innerHTML = totalMembers;
			} else {
				spot.innerHTML = "1"; // al menos el dueño
			}
		} catch (error) {
			console.error("Error fetching data:", error);
		}
	}

	// 📌 Al hacer clic en una empresa
	let selectedCompanyId = null;
	document.addEventListener('change', function (e) { // AQUI REVISA ESTO PARA QUE SOLO CAMBIE CUANDO PRECIONAMOS EL BOTON SELECCIONAR
		if (e.target.matches('input[name="company_edit_info"]')) {
			selectedCompanyId = Number(e.target.dataset.company);
			
			if (!isNaN(selectedCompanyId)) {
				loadCompanyOnDashboard(selectedCompanyId);
			} else {
				loadCompanyOnDashboard();
				loadChildUsers();
			}
		}
	});
	if (!selectedCompanyId) {
		loadChildUsers();
		loadCompanyOnDashboard();
	}

	// 📌 Manejo de lo datos de Empresa
	function loadCompanyOnDashboard(companyId) {
		const myCompany = document.getElementById("company-data");
		if (myCompany) {
			let url = 'api/get_company_info.php';
			if (companyId && !isNaN(companyId)) {
				url += `?select_company=${companyId}`;
			}

			fetch(url, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			})
			.then(response => response.json())
			.then(data => {
				if (data.success && data.data) {
					let company = data.data[0];

					let logoHTML = "";
					if (company.company_logo && company.company_logo.trim() !== "") {
						logoHTML = `<p><img src="images/company-logos/${company.company_logo}" alt="Company Logo" style="max-width: 40px; margin: 0 auto; border-radius: 50%; border: 1px solid #000;"></p>`;
					}

					const orgNo = (company.organization_no || "-").toString().trim();
					const compName = (company.company_name || "-").toString().trim();

					myCompany.innerHTML = 
						logoHTML +
						`<p><strong>Org No.:</strong> ${orgNo}</p>` +
						`<p><strong>Name:</strong> ${compName}</p>`;
				} else {
					myCompany.innerHTML = `<div class="warning-message">
						<p>To initialize the system, it is necessary to complete it with your company's data.</p>
					</div>`;
				}
			})
			.catch (error => {
				console.error("Error fetching data:", error);
				document.getElementById("company-data").innerHTML = `<p>Error al cargar los datos de empresa.</p>`;
			});
		}
	}

	// 📌 Manejo de lista de usuarios hijos
	function loadChildUsers(companyId) {
		const userContainer = document.getElementById('child-user-table');
		if (userContainer) {
			let url = 'api/get_users.php';
			if (companyId && !isNaN(companyId)) {
				url += `?select_company=${companyId}`;
			}

			fetch(url, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			})
			.then(response => response.json())
			.then(data => {
				if (data.success && data.count > 0) {
					userContainer.innerHTML = '';

					data.users.forEach(user => {
						let card = document.createElement("div");
						card.classList.add("members-card");

						let profileImage = user.image && user.image.trim() !== "" 
						? `images/profile/${user.image}` 
						: "images/sys-img/NonProfilePic.png";

						let borderColor = getUserBorderColor(user);

						card.innerHTML = `
							<div class="mini-banner">
								<div class="mini-profile" style="border: 2px solid ${borderColor};">
									<img src="${profileImage}" alt="Profile Picture">
								</div>
								<div class="co-worker-position">${user.rank_text || 'Unknown role'}</div>
							</div>
							<div class="card-info">
								<h3>${user.name} ${user.surname}</h3>
								<p><strong>Email:</strong> ${user.email}</p>
								<p><strong>${window.i18n?.phone || "Phone"}:</strong> ${user.phone ? user.phone : "No Phone Number"}</p>
							</div>
							<div class="card-menu">
								<img src="images/sys-img/edit-icon.png" alt="edit-card">
							</div>
						`;

						userContainer.appendChild(card);

						const cardMenuBtn = card.querySelector('.card-menu');
						cardMenuBtn.addEventListener('click', () => {
							openMemberForm(user.user_id);
						});
					});
				} else {
					userContainer.innerHTML = "<p>No members found.</p>";
				}
			})
			.catch(error => {
				console.error('Error fetching data:', error);
				userContainer.innerHTML = `<p>Error loading user data.</p>`;
			});
		}
	}

	async function openMemberForm(userId) {
		scrollToTopIfNeeded();
	
		const addMembersForm = document.getElementById('edit-members-form');
		const popupContent = addMembersForm.querySelector('.formular-frame');
		const formEditMembers = document.getElementById('formEditMembers');
	
		if (addMembersForm && popupContent) {
			
			addMembersForm.style.display = 'block';
			addMembersForm.style.opacity = '0';
			addMembersForm.style.transition = 'opacity 0.5s ease';
			setTimeout(() => {
				addMembersForm.style.opacity = '1';
			}, 10);
	
			popupContent.style.transform = 'scale(0.7)';
			popupContent.style.opacity = '0';
			popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			setTimeout(() => {
				popupContent.style.transform = 'scale(1)';
				popupContent.style.opacity = '1';
			}, 50);

			formEditMembers.setAttribute('data-user-id', userId);
		}

		try {
			let response = await fetch(`api/get_user_by_id.php?user_id=${userId}`);
			let data = await response.json();
	
			if (data.success && data.data) {
				const user = data.data;

				document.getElementById('edit_name').value = user.name || '';
				document.getElementById('edit_surname').value = user.surname || '';
				document.getElementById('edit_birthday').value = user.birthday ? user.birthday.split(" ")[0] : '';
				document.getElementById('edit_phone').value = user.phone || '';
				document.getElementById('edit_email').value = user.email || '';
				// Opcional: Puedes ocultar el campo de contraseña si estás editando
				// document.getElementById('edit_password').value = '';
				document.getElementById("edit_status").checked = user.status === "1" || user.status === 1;

				const selectedKeyFromDB = user.country_code || '';
				await populateCountryPhoneCodes('edit_member_country_code', 'edit_phone', selectedKeyFromDB);

				populateRankSelect('edit_rank', user.rank, '4');

				populateCompanies('edit_company', user.company_id);

				handlePopupClose("edit-members-form", ".formular-frame", []);
			}
		} catch (error) {
			console.error("Error loading user data:", error);
		}
	}

	// 📌 Manejo del formulario de edit usuarios hijos
	const formEditMembers = document.getElementById('formEditMembers');
	if (formEditMembers) {
		formEditMembers.addEventListener('submit', async function (e) {
			e.preventDefault();
	
			const formData = new FormData(this);
			formData.append('edit_user_id', formEditMembers.getAttribute('data-user-id')); // ID del usuario a editar
	
			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				const response = await fetch('api/update_member.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});
	
				const data = await response.json();
	
				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				
				showBanner(banner);
	
				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url || window.location.href;
						});
					}, 3000);
				}
			} catch (error) {
				console.error("Error updating member:", error);
			}
		});
	}

	// 📌 Script para eliminar co-worker
	const deleteBtn = document.getElementById('deleteAccountBtn');
	const formEditMembersToDelete = document.getElementById('formEditMembers');
	if (deleteBtn && formEditMembersToDelete) {
		deleteBtn.addEventListener('click', async () => {
			const userId = formEditMembersToDelete.getAttribute('data-user-id');

			if (!userId) {
				alert("User ID not found.");
				return;
			}

			showConfirmModal("Delete User", "Are you sure you want to delete this user?", async () => {
				const frame = document.querySelector('.formular-frame');
				if (frame) frame.style.display = 'none';

				const formData = new FormData();
				formData.append("user_id", userId);

				try {
					const response = await fetch('api/delete_user.php', {
						method: 'POST',
						body: formData
					});

					const data = await response.json();

					const banner = document.getElementById('status-message');
					const statusText = document.getElementById('status-text');
					const statusImage = document.getElementById('status-image');

					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);

					if (data.success) {
						setTimeout(() => {
							hideBanner(banner, () => {
								window.location.href = data.redirect_url || window.location.href;
							});
						}, 3000);
					}
				} catch (error) {
					console.error("Error deleting user:", error);
					alert("Error deleting user. Check console.");
				}
			});
		});
	}

	// 📌 Boton para cerrar formularios
	let cancelButtons = document.querySelectorAll('.neutral-btn');
	cancelButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			let popup = button.closest('.bg-popup');
			if (popup) {
				popup.style.display = 'none';
			}
		});
	});

	// 📌 script para my info popup
	let editMyDataButton = document.getElementById('edit-my-data');
	if (editMyDataButton) {
		editMyDataButton.addEventListener('click', async function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();
			
			const editMyInfoForm = document.getElementById('edit-my_info-form');
			const popupContent = editMyInfoForm.querySelector('.formular-frame');

			if (editMyInfoForm && popupContent) {
				editMyInfoForm.style.display = 'block';
				editMyInfoForm.style.opacity = '0';
				editMyInfoForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					editMyInfoForm.style.opacity = '1';
				}, 10);

				popupContent.style.transform = 'scale(0.7)';
				popupContent.style.opacity = '0';
				popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				initDragAndDrop('profile-drop-area', 'profile-img', 'profile-pic-preview');

				const info = await loadMyInfo();
				const user = info?.data || {};
				const selectedKeyFromDB = user.country_code || '';

				await populateCountryPhoneCodes('country_code', 'user_phone', selectedKeyFromDB);
			
				handlePopupClose("edit-my_info-form", ".formular-frame", []);
			}
		});
	}

	// 📌 Manejo del formulario de edit my info
	let formEditMyInfo = document.getElementById('formEditMyInfo');
	if (formEditMyInfo) {
		formEditMyInfo.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/update_my_info.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				let data = await response.json();

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				showBanner(banner);

				if (data.success) {
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

	// 📌 script para subscrition popup
	let subscButton = document.getElementById('subsc-button');
	if (subscButton) {
		subscButton.addEventListener('click', async (e) => {
			e.preventDefault();

			scrollToTopIfNeeded();

			const subscForm = document.getElementById('subsc-form');
			const popupContent = subscForm.querySelector('.formular-medium-frame');
			const formSubscription = document.getElementById('formSubscription');

			if (subscForm && popupContent && formSubscription) {
				subscForm.style.display = 'block';
				subscForm.style.opacity = '0';
				subscForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					subscForm.style.opacity = '1';
				}, 10);

				popupContent.style.transform = 'scale(0.7)';
				popupContent.style.opacity = '0';
				popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				try {
					const response = await fetch('/api/get_my_info.php', {
						method: 'GET',
						headers: { Accept: 'application/json' }
					});

					const data = await response.json();

					if (data.success && data.data) {
						const user = data.data;
						const packageId = user.package_id || user.package_info?.package_id || "";

						let currentPackageInput = document.getElementById('current_package_id');

						if (!currentPackageInput) {
							currentPackageInput = document.createElement('input');
							currentPackageInput.type = 'hidden';
							currentPackageInput.id = 'current_package_id';
							formSubscription.appendChild(currentPackageInput);
						}

						currentPackageInput.value = packageId;
					}
				} catch (error) {
					console.error("Error fetching setup data:", error);
				}

				await populatePackages('packs');

				await populateExtraServices('extra_pack');

				assignPackageListeners();
				updateEstimatedCost();

				handlePopupClose("subsc-form", ".formular-medium-frame", []);
			}
		});
	}

	// 📌 script para recojer los datos de la compania
	async function loadMyInfo() {
		try {
			let response = await fetch('api/get_my_info.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
	
			let data = await response.json();
	
			if (data.success && data.data) {
				let user = data.data;
	
				document.getElementById('user_name').value = user.name || "";
				document.getElementById('user_surname').value = user.surname || "";
				document.getElementById('user_birthday').value = user.birthday ? user.birthday.split(' ')[0] : "";
				document.getElementById('user_phone').value = user.phone || "";
				document.getElementById('user_email').value = user.email || "";
	
				const profilePicPreview = document.getElementById('profile-pic-preview');

				if (user.image && user.image.trim() !== "") {
					profilePicPreview.src = `../images/profile/${user.image}`;
					profilePicPreview.style.display = 'block';
					profilePicPreview.style.visibility = 'visible';
					profilePicPreview.style.opacity = '1';
				} else {
					profilePicPreview.src = "";
					profilePicPreview.style.display = 'none';
				}

				return data;
			}
		} catch (error) {
			console.error("Error loading user info:", error);
		}
	}

	// 📌 script para manage company popup
	let manageCompBtn = document.getElementById('manage-comp-button');
	if (manageCompBtn) {
		manageCompBtn.addEventListener('click', async function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const editCompanyForm = document.getElementById('edit-company-form');
			const popupContent = editCompanyForm.querySelector('.formular-medium-frame');

			if (editCompanyForm && popupContent) {
				editCompanyForm.style.display = 'block';
				editCompanyForm.style.opacity = '0';
				editCompanyForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					editCompanyForm.style.opacity = '1';
				}, 10);

				popupContent.style.transform = 'scale(0.7)';
				popupContent.style.opacity = '0';
				popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				initDragAndDrop('company-logo-drop-area', 'company_logo', 'logo-preview');
			
				handlePopupClose("edit-company-form", ".formular-medium-frame", []);
			}
		});
	}

	let originalCompanyData = {};
	let hasChanges = false;

	// 📌 Cargar la lista de empresas
	const affList = document.getElementById('affiliate-list');
	if (affList) {
		try {
			let response = await fetch('api/get_company_info.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});

			let data = await response.json();

			if (data.success && data.data.length > 0) {
				data.data.forEach(company => {
					const uniqueId = `company-db-${company.company_id}`;
					const row = document.createElement('tr');
					row.className = "categoryContainer";
					row.innerHTML = `
						<td width="10%" align="center" valign="middle">
							<div class="list-icon">
								<img src="images/sys-img/element-list.png" alt="">
							</div>
						</td>
						<td width="80%" valign="middle" style="padding-left:10px;">${company.company_name}</td>
						<td width="10%" align="center" valign="middle" style="position: relative;">
							<div class="opcion-radio">
								<input type="radio" id="${uniqueId}" name="company_edit_info" class="category-radio" data-company="${company.company_id}" />
								<label for="${uniqueId}"></label>
							</div>
						</td>
					`;
					affList.appendChild(row);
				});
			}
		} catch (error) {
			console.error("Error loading categories:", error);
		}
	}

	// CARGAR FORMULARIO DE COMPANY
	document.addEventListener('change', function (e) {
		if (e.target.matches('input[name="company_edit_info"]')) {
			const notCompanyForm = document.getElementById('not-company-form');
			const companyForm = document.getElementById('company-form');
			const companyActionBtn = document.getElementById('company-action-btn');
			if (e.target.checked) {
				notCompanyForm.classList.add('hidden');
				companyForm.classList.remove('hidden');
				companyActionBtn.value = "Select Company";
			}

			// initImagePreview('company_logo', 'logo-preview').then((isImage) => {
			// 	if (!isImage) {
			// 		const logoPreview = document.getElementById('logo-preview');
			// 		if (logoPreview) {
			// 			logoPreview.src = '';
			// 			logoPreview.style.display = 'none';
			// 			logoPreview.style.visibility = 'hidden';
			// 			logoPreview.style.opacity = '0';
			// 		}
			// 	}
			// });

			const selectedCompanyId = e.target.dataset.company;
			loadCompanyFormOrData(selectedCompanyId);
		}
	});

	const inputs = document.querySelectorAll('#company-form input[type="text"]');
	const selects = document.querySelectorAll('#company-form select');
	const allFields = [...inputs, ...selects];
	inputs.forEach(input => {
		input.addEventListener('input', () => {
			const field = input.id;
			const currentValue = input.value ?? '';
			const originalValue = originalCompanyData[field] ?? '';
			const companyActionBtn = document.getElementById('company-action-btn');

			if (currentValue !== originalValue) {
				showChangeAlert();
				companyActionBtn.value = "Save Changes";
			} 
			else {
				checkIfAnyChange(allFields);
			}
		});
	});

	selects.forEach(select => {
		select.addEventListener('change', () => {
			const field = select.id;
			const currentValue = select.value ?? '';
			const originalValue = originalCompanyData[field] ?? '';
			const companyActionBtn = document.getElementById('company-action-btn');

			if (currentValue !== originalValue) {
				showChangeAlert();
				companyActionBtn.value = "Save Changes";
			} else {
				checkIfAnyChange(allFields);
			}
		});
	});

	initImagePreview('company_logo', 'logo-preview', () => {
		const companyActionBtn = document.getElementById('company-action-btn');
		showChangeAlert();
		companyActionBtn.value = "Save Changes";
	});

	function showChangeAlert() {
		const banner = document.getElementById('status-message');
		const statusText = document.getElementById('status-text');
		const statusImage = document.getElementById('status-image');

		statusText.innerText = "You have unsaved changes.";
		statusImage.src = "images/sys-img/error.gif";
		showBanner(banner);
	}

	function hideChangeBanner() {
		const banner = document.getElementById('status-message');
		if (banner) {
			hideBanner(banner);
		}
	}

	function checkIfAnyChange(elements) {
		hasChanges = Array.from(elements).some(el => {
			const field = el.id;
			const currentValue = el.value ?? '';
			const originalValue = originalCompanyData[field] ?? '';
			return currentValue !== originalValue;
		});

		if (!hasChanges) hideChangeBanner();
	}

	// 📌 Manejo del formulario de update Company
	let formEditCompany = document.getElementById('formEditCompany');
	if (formEditCompany) {
		formEditCompany.addEventListener('submit', async function (e) {
			e.preventDefault();

			const companyActionBtn = document.getElementById('company-action-btn');
			const isSelecting = companyActionBtn.value === "Select Company";

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			if (isSelecting) {
				const selectedInput = document.querySelector('input[name="company_edit_info"]:checked');
				if (selectedInput) {
					const selectedCompanyId = selectedInput.dataset.company;
					if (selectedCompanyId && !isNaN(selectedCompanyId)) {
						loadChildUsers(selectedCompanyId);
					}
				}

				statusText.innerText = "Company selected successfully.";
				statusImage.src = "images/sys-img/loading1.gif";
				showBanner(banner);

				const companyForm = document.getElementById('edit-company-form');
				const popupContent = document.querySelector('.formular-medium-frame');
				if (companyForm && popupContent) {
					companyForm.style.display = "";
					popupContent.style.display = "";

					setTimeout(() => {
						hideBanner(banner);
					}, 1500);
				}

				return;
			}

			let formData = new FormData(this);

			try {
				let response = await fetch('api/manage_company.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				let data = await response.json();

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				showBanner(banner);

				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);
				}
			} catch (error) {
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	const addAffBtn = document.getElementById('add-aff-btn');
	if (addAffBtn) {
		addAffBtn.addEventListener('click', async function (e) {
			e.preventDefault();

			addAffBtn.disabled = true;
			setTimeout(() => addAffBtn.disabled = false, 1000);

			try {
				const companiesRes = await fetch('api/get_company_info.php');
				const companiesData = await companiesRes.json();
				const currentAffiliatesCount = companiesData.success ? companiesData.count : 0;

				const userInfoRes = await fetch('api/get_my_info.php');
				const userInfo = await userInfoRes.json();
				const rawAllowed = userInfo.success ? userInfo.data.package_info.branch_affiliate_limit : null;
				const allowedAffiliates = rawAllowed !== null && rawAllowed !== "" ? parseInt(rawAllowed) : null;

				if (allowedAffiliates === null || currentAffiliatesCount >= allowedAffiliates) {
					const allowedTitle = (allowedAffiliates === null) ? "You have 0 affiliate slots" : "Maximum allowed affiliates reached";
					const allowedText = "If you want to have the ability to add more affiliates, upgrade your pack.";
					showAlertModal(allowedTitle, allowedText);
					return;
				}

				scrollToTopIfNeeded();

				const notCompanyForm = document.getElementById('not-company-form');
				const companyForm = document.getElementById('company-form');
				const companyActionBtn = document.getElementById('company-action-btn');

				if (notCompanyForm && companyForm && companyActionBtn) {
					notCompanyForm.classList.add('hidden');
					companyForm.classList.remove('hidden');
					companyActionBtn.value = window.i18n?.create || "Create";
				}

				document.querySelectorAll('input[name="company_edit_info"]').forEach(radio => {
					radio.checked = false;

					const logoPreview = document.getElementById('logo-preview');
					if (logoPreview) {
						logoPreview.src = '';
						logoPreview.style.display = 'none';
						logoPreview.style.opacity = '0';
					}
				});

				loadCompanyFormOrData();
			} catch (err) {
				console.error("Error opening add company form:", err);
				alert("An error occurred while trying to open the add company form.");
			}
		});
	}

	// 📌 Manejo del formulario de update Company
	let formAddCompany = document.getElementById('formEditCompany');
	if (formAddCompany) {
		formAddCompany.addEventListener('submit', async function (e) {
			e.preventDefault();

			const companyActionBtn = document.getElementById('company-action-btn');
			const isAdding = companyActionBtn.value === (window.i18n?.create || "Create");

			if (!isAdding) return;

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/manage_company.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				let data = await response.json();

				// let banner = document.getElementById('status-message');
				// let statusText = document.getElementById('status-text');
				// let statusImage = document.getElementById('status-image');
				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner);
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);
				}
			} catch (error) {
				// let banner = document.getElementById('status-message');
				// let statusText = document.getElementById('status-text');
				// let statusImage = document.getElementById('status-image');

				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	// 📌 script para add members popup
	let addMemberButton = document.getElementById('add-members-button');
	if (addMemberButton) {
		addMemberButton.addEventListener('click', async function (e) {
			e.preventDefault();

			try {
				const usersRes = await fetch('api/get_users.php');
				const usersData = await usersRes.json();
				const currentMemberCount = usersData.success ? usersData.count : 0;
	
				const userInfoRes = await fetch('api/get_my_info.php');
				const userInfo = await userInfoRes.json();
				const rawAllowed = userInfo.success ? userInfo.data.package_info.members_limit : null;
				const allowedMembers = rawAllowed !== null && rawAllowed !== "" ? parseInt(rawAllowed) : null;
	
				const totalMembersWithOwner = currentMemberCount + 1;

				if (allowedMembers === null || totalMembersWithOwner >= allowedMembers) {
					const allowedTitle = (allowedMembers === null) ? "You have 0 member slots" : "Maximum allowed members reached";
					const allowedText = "If you want to have the ability to add more members, upgrade your membership.";
					showAlertModal(allowedTitle, allowedText);
					return;
				}

				scrollToTopIfNeeded();

				clearFields(['name', 'surname', 'birthday', 'phone', 'email', 'password']);

				const addMembersForm = document.getElementById('add-members-form');
				const popupContent = addMembersForm.querySelector('.formular-frame');

				if (addMembersForm && popupContent) {
					addMembersForm.style.display = 'block';
					addMembersForm.style.opacity = '0';
					addMembersForm.style.transition = 'opacity 0.5s ease';
					setTimeout(() => {
						addMembersForm.style.opacity = '1';
					}, 10);

					popupContent.style.transform = 'scale(0.7)';
					popupContent.style.opacity = '0';
					popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
					setTimeout(() => {
						popupContent.style.transform = 'scale(1)';
						popupContent.style.opacity = '1';
					}, 50);

					await populateCountryPhoneCodes('member_country_code', 'phone');

					populateRankSelect('rank', '', 4); // Solo roles 4 o superiores

					populateCompanies('company');

					handlePopupClose("add-members-form", ".formular-frame", []);
				}
			} catch (err) {
				console.error("Error validating member limit:", err);
				alert("An error occurred while validating your permission to add members.");
			}
		});
	}

	// 📌 Manejo del formulario de crear miembros
	let formMembers = document.getElementById('formMembers');
	if (formMembers) {
		formMembers.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/create_members.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = data.img_gif;
				showBanner(banner);
			}
		});
	}
//################################################################ END MEMBER #####################################################################
	
//################################################################ PRODUCTS #####################################################################
	let companySelect = document.getElementById('select-company');
	if (companySelect) {
		const addProductBtn = document.getElementById("add-product-btn");
		const addCategoryBtn = document.getElementById("add-category-btn");
		const selectionNotice = document.getElementById("selection-notice");

		// 👇 Desactivar los botones al iniciar
		if (addProductBtn) {
			addProductBtn.disabled = true;
			addProductBtn.classList.add('button-ghost');
		}
		if (addCategoryBtn) {
			addCategoryBtn.disabled = true;
			addCategoryBtn.classList.add('button-ghost');
		}

		if (selectionNotice) {
			selectionNotice.classList.remove('hidden');
		}

		// 👂 Escuchar cambios en el select
		companySelect.addEventListener('change', function () {
			const selectedValue = this.value;
			const isValid = selectedValue && selectedValue.trim() !== "";

			if (isValid) {
				// console.log("Empresa seleccionada:", selectedValue);

				if (addProductBtn) {
					addProductBtn.disabled = false;
					addProductBtn.classList.remove('button-ghost');
				}
				if (addCategoryBtn) {
					addCategoryBtn.disabled = false;
					addCategoryBtn.classList.remove('button-ghost');
				}

				if (selectionNotice) {
					selectionNotice.classList.add('hidden');
				}
			} else {
				// console.log("Ninguna empresa está seleccionada.");

				if (addProductBtn) {
					addProductBtn.disabled = true;
					addProductBtn.classList.add('button-ghost');
				}
				if (addCategoryBtn) {
					addCategoryBtn.disabled = true;
					addCategoryBtn.classList.add('button-ghost');
				}

				if (selectionNotice) {
					selectionNotice.classList.remove('hidden');
				}
			}
		});
	}
	
	// 📌 script para add product popup
	let addProductButton = document.getElementById('add-product-btn');
	if (addProductButton) {
		addProductButton.addEventListener('click', async function (e) {
			e.preventDefault();

			addProductButton.disabled = true;
			setTimeout(() => addProductButton.disabled = false, 1000);

			try {
				const productsRes = await fetch('api/get_products.php');
				const productsData = await productsRes.json();
				const productsCount = productsData.success ? productsData.count : 0;

				const userInfoRes = await fetch('api/get_my_info.php');
				const userInfo = await userInfoRes.json();
				const rawAllowed = userInfo.success ? userInfo.data.package_info.products_limit : null;
				const allowedProducts = rawAllowed !== "" ? parseInt(rawAllowed) : null;

				if (allowedProducts !== null && productsCount >= allowedProducts) {
					const allowedTitle = "Maximum allowed products reached";
					const allowedText = "If you want to have the ability to add more products, upgrade your pack.";
					showAlertModal(allowedTitle, allowedText);
					return;
				}

				scrollToTopIfNeeded();

				const addProductForm = document.getElementById('add-product-form');
				const popupContent = addProductForm.querySelector('.formular-frame');

				if (addProductForm && popupContent) {
					addProductForm.style.display = 'block';
					addProductForm.style.opacity = '0';
					addProductForm.style.transition = 'opacity 0.5s ease';
					setTimeout(() => {
						addProductForm.style.opacity = '1';
					}, 10);

					popupContent.style.transform = 'scale(0.7)';
					popupContent.style.opacity = '0';
					popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
					setTimeout(() => {
						popupContent.style.transform = 'scale(1)';
						popupContent.style.opacity = '1';
					}, 50);

					initDragAndDrop('drop-product-area', 'product_image', 'product-image-preview');

					initUnitTypeControls(
						'unit_type_1',      // radio single
						'unit_type_2',      // radio pack
						'units',            // input unidades
						'weight_unit',      // input peso/unidad
						'total_weight',     // input peso total
						{ decimals: 3, acceptComma: true, minUnits: 1 }
					);

					populateProductTypes('product_type', '', '1', true);

					initCategorySelectors('product_mark', 'product_model', 'product_sub_model', 'select-company');

					populateProductPurpose('product_purpose', '1');

					populateCurrencies('currency');

					handlePopupClose("add-product-form", ".formular-frame", []);
				}
			} catch (err) {
				console.error("Error opening add product form:", err);
				alert("An error occurred while trying to open the add product form.");
			}
		});
	}

	function el(ref) {
		if (!ref) return null;
		return (typeof ref === 'string') ? document.getElementById(ref) : ref;
	}

	// —— cálculo de peso ——
	function calculateWeight(unitsInputRef, weightUnitInputRef, totalWeightInputRef, opts = {}) {
		const unitsInput       = el(unitsInputRef);
		const weightUnitInput  = el(weightUnitInputRef);
		const totalWeightInput = el(totalWeightInputRef);

		if (!unitsInput || !weightUnitInput || !totalWeightInput) return;

		const decimals    = opts.decimals ?? 3;
		const acceptComma = opts.acceptComma ?? true;
		const minUnits    = opts.minUnits ?? 1;

		// Normaliza visualmente: coma → punto (para que el usuario vea siempre ".")
		if (acceptComma && weightUnitInput.value.includes(',')) {
			const pos = weightUnitInput.selectionStart;
			weightUnitInput.value = weightUnitInput.value.replace(/,/g, '.');
			if (pos != null) weightUnitInput.setSelectionRange(pos, pos);
		}

		let units = parseInt(unitsInput.value, 10);
		if (isNaN(units) || units < minUnits) units = minUnits;

		const raw = String(weightUnitInput.value || '').trim(); // ya con puntos
		const w = raw === '' ? NaN : Number(raw);

		if (Number.isFinite(w)) {
			// toFixed usa punto por especificación
			const val = (w * units).toFixed(decimals);
			totalWeightInput.value = val;                 // siempre con punto
			// por si el navegador intentara cambiarlo: refuerza el punto
			if (totalWeightInput.value.includes(',')) {
				totalWeightInput.value = totalWeightInput.value.replace(/,/g, '.');
			}
		} else {
			totalWeightInput.value = '';
		}
	}

	// —— radios + wiring de eventos ——
	function initUnitTypeControls(
		radioUnitRef,        // "Single Unit"
		radioPackRef,        // "Multi Pack"
		unitsInputRef,       // unidades
		weightUnitInputRef,  // peso/unidad
		totalWeightInputRef, // peso total
		opts = {}
	) {
		const radioUnit   = el(radioUnitRef);
		const radioPack   = el(radioPackRef);
		const unitsInput  = el(unitsInputRef);
		const weightInput = el(weightUnitInputRef);
		const totalInput  = el(totalWeightInputRef);

		if (!unitsInput || !weightInput || !totalInput) return;

		const minUnits    = opts.minUnits ?? 1;
		const decimals    = opts.decimals ?? 3;
		const acceptComma = opts.acceptComma ?? true;
		const defaultMode  = opts.defaultMode ?? null;

		// Sugerir formato "inglés"
		weightInput.setAttribute('lang','en');
		weightInput.setAttribute('inputmode','decimal');
		totalInput.setAttribute('lang','en');
		totalInput.setAttribute('inputmode','decimal');

		// 🔒 Forzar siempre punto visual: usa type="text" para evitar localización con coma
		try { totalInput.type = 'text'; } catch(e) {}

		const applyMode = () => {
			if (radioPack && radioPack.checked) {
				unitsInput.disabled = false;
				// unitsInput.value = '';
				// let v = parseInt(unitsInput.value, 10);
				// if (isNaN(v) || v < minUnits) unitsInput.value = minUnits;
				if (unitsInput.value === '') {
					unitsInput.value = minUnits;
				}
			} else {
				unitsInput.disabled = true;
				unitsInput.value = minUnits;
			}
			calculateWeight(unitsInput, weightUnitInputRef, totalWeightInputRef, { decimals, acceptComma, minUnits });
		};

		radioUnit?.addEventListener('change', applyMode);
		radioPack?.addEventListener('change', applyMode);

		unitsInput.addEventListener('input', () => {
			let v = parseInt(unitsInput.value, 10);
			if (isNaN(v) || v < minUnits) v = minUnits;
			unitsInput.value = v;
			calculateWeight(unitsInput, weightUnitInputRef, totalWeightInputRef, { decimals, acceptComma, minUnits });
		});

		weightInput.addEventListener('input', () => {
			// ✅ Validar: permitir solo números y punto
			weightInput.value = weightInput.value.replace(/[^0-9.,]/g, '');

			// Normaliza coma→punto y recalcula
			if (acceptComma && weightInput.value.includes(',')) {
				const pos = weightInput.selectionStart;
				weightInput.value = weightInput.value.replace(/,/g, '.');
				if (pos != null) weightInput.setSelectionRange(pos, pos);
			}
			calculateWeight(unitsInput, weightUnitInputRef, totalWeightInputRef, { decimals, acceptComma, minUnits });
		});

		if (defaultMode === '1') {
			radioUnit.checked = true;
		} else if (defaultMode === '2') {
			radioPack.checked = true;
		}

		// estado inicial
		applyMode();
	}

	
	// 📌 Manejo del formulario para crear Producto
	const formAddProduct = document.getElementById('formAddProduct');
	if (formAddProduct) {
		formAddProduct.addEventListener('submit', async function (e) {
			e.preventDefault();

			const companySelect = document.getElementById("select-company");
			const formData = new FormData(this);

			// ✅ Añadir company_id si está seleccionado
			if (companySelect && companySelect.value.trim() !== "") {
				formData.append("company_id", companySelect.value);
			} else {
				alert("You must select a company.");
				return;
			}

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			const trySubmit = async (isRetry = false) => {
				if (isRetry) {
					formData.append("confirm_update", "true");
				}

				const response = await fetch('api/create_product.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				const data = await response.json();
				
				if (data.needs_confirmation && !isRetry) {
					showConfirmModal(
						"Update Product",
						data.message,
						async () => {
							await trySubmit(true);
						}
					);
					return;
				}

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				showBanner(banner);

				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url || window.location.href;
						});
					}, 3000);
				}
			};

			try {
				await trySubmit();
			} catch (error) {
				statusText.innerText = "Error processing the request.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	// 📌 script para add category popup
	let addCategoryButton = document.getElementById('add-category-btn');
	if (addCategoryButton) {
		addCategoryButton.addEventListener('click', async function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const addProductForm = document.getElementById('add-category-form');
			const popupContent = addProductForm.querySelector('.formular-big-frame');

			if (addProductForm && popupContent) {
				addProductForm.style.display = 'block';
				addProductForm.style.opacity = '0';
				addProductForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					addProductForm.style.opacity = '1';
				}, 10);

				popupContent.style.transform = 'scale(0.7)';
				popupContent.style.opacity = '0';
				popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
			}

			const companySelect = document.getElementById('select-company');
			const selectedCompany = companySelect?.value || "";
			const params = new URLSearchParams();
			if (selectedCompany) params.append('company', selectedCompany);

			try {
				const response = await fetch(`api/get_categories.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				const data = await response.json();
				const markList = document.getElementById('mark-list');

				markList.innerHTML = '';

				if (data.success && data.data.length > 0) {
					data.data.forEach(category => {
						const uniqueId = `mark-db-${category.category_id}`;
						const row = document.createElement('tr');
						row.className = "categoryContainer";
						row.innerHTML = `
							<td width="10%" align="center" valign="middle">
								<div class="list-icon">
									<img src="images/sys-img/element-list.png" alt="">
								</div>
							</td>
							<td width="80%" valign="middle" style="padding-left:10px;">${category.category_name}</td>
							<td width="10%" align="center" valign="middle">
								<div class="opcion-radio">
									<input type="radio" id="${uniqueId}" name="product_mark" class="category-radio" data-mark="${category.category_id}" />
									<label for="${uniqueId}"></label>
								</div>
							</td>
						`;
						markList.appendChild(row);
					});
				}

				handlePopupClose("add-category-form", ".formular-big-frame", []);
			} catch (error) {
				console.error("Error loading categories:", error);
			}
		});
	}

	// 📌 script para crear Marca / categoria
	let addMarkBtn = document.getElementById('add-mark-btn');
	if (addMarkBtn) {
		addMarkBtn.addEventListener('click', function(){
			let clicCreateMark = document.getElementById('clic-create-mark');
			let inputMark = document.getElementById('input-mark');

			clicCreateMark.style.display = 'none';
			inputMark.style.display = 'block';
		});
	}

	const inputProductMark = document.getElementById('input-product-mark');
	const markList = document.getElementById('mark-list');
	const btnCreateMark = document.getElementById('btn-create-mark');

	if (inputProductMark) {
		inputProductMark.addEventListener('input', () => {
			let currentValue = inputProductMark.value;
			
			if (currentValue === currentValue.toUpperCase()) return;

			let words = inputProductMark.value.split(" ");
			words = words.map(word => {
				return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
			});
			inputProductMark.value = words.join(" ");
		});
	}

	if (btnCreateMark) {
		btnCreateMark.addEventListener('click', function (e) {
			e.preventDefault();
			const value = inputProductMark.value.trim();

			if (value !== '') {
				const existingNames = markList.querySelectorAll('tr td:nth-child(2)');
				let exists = false;

				existingNames.forEach(cell => {
					if (cell.textContent.trim().toLowerCase() === value.toLowerCase()) {
						exists = true;
					}
				});

				if (exists) {
					showConfirmModal(
						"Mark Already Exists",
						`The mark "${value}" already exists. Please choose a different name.`,
						() => {
							inputProductMark.focus();
						}
					);
					return;
				}

				const existingRadios = document.querySelectorAll('input[name="product_mark"]');
				existingRadios.forEach(r => r.checked = false);

				const uniqueId = `mark-${Date.now()}`;
				const row = document.createElement('tr');
				row.className = "categoryContainer";
				row.innerHTML = `
					<td width="10%" align="center" valign="middle">
						<div class="list-icon">
							<img src="images/sys-img/element-list.png" alt="">
						</div>
					</td>
					<td width="80%" valign="middle" style="padding-left:10px;">${value}</td>
					<td width="10%" align="center" valign="middle">
						<div class="opcion-radio">
							<input type="radio" id="${uniqueId}" name="product_mark" class="category-radio" data-mark="${value}" checked />
							<label for="${uniqueId}"></label>
						</div>
					</td>
				`;
				markList.appendChild(row);
				inputProductMark.value = '';
			}
		});
	}


	// 📌 script para crear sub-categoria / modelo
	let addModelBtn = document.getElementById('add-model-btn');
	if (addModelBtn) {
		addModelBtn.addEventListener('click', function(){
			let clicCreateMark = document.getElementById('clic-create-model');
			let inputMark = document.getElementById('input-model');

			clicCreateMark.style.display = 'none';
			inputMark.style.display = 'block';
		});
	}

	const inputProductModel = document.getElementById('input-product-model');
	const modelList = document.getElementById('model-list');
	const btnCreateModel = document.getElementById('btn-create-model');
	
	if (inputProductModel) {
		inputProductModel.addEventListener('input', () => {
			let currentValue = inputProductModel.value;
	
			if (currentValue === currentValue.toUpperCase()) return;

			let words = inputProductModel.value.split(" ");
			words = words.map(word => {
				return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
			});
			inputProductModel.value = words.join(" ");
		});
	}

	if (btnCreateModel) {
		btnCreateModel.addEventListener('click', function (e) {
			e.preventDefault();
			const value = inputProductModel.value.trim();

			if (value !== '') {
				const emptyRow = modelList.querySelector('tr[data-empty-message]');
				if (emptyRow) {
					emptyRow.remove();
				}

				const existingRadios = document.querySelectorAll('input[name="product_model"]');
				existingRadios.forEach(r => r.checked = false);

				const uniqueId = `model-${Date.now()}`;
				const row = document.createElement('tr');
				row.className = "categoryContainer";
				row.innerHTML = `
					<td width="10%" align="center" valign="middle">
						<div class="list-icon">
							<img src="images/sys-img/element-list.png" alt="">
						</div>
					</td>
					<td width="80%" valign="middle" style="padding-left:10px;">${value}</td>
					<td width="10%" align="center" valign="middle">
						<div class="opcion-radio">
							<input type="radio" id="${uniqueId}" name="product_model" class="category-radio" data-model="${value}" checked />
							<label for="${uniqueId}"></label>
						</div>
					</td>
				`;
				modelList.appendChild(row);
				inputProductModel.value = '';
			}
		});
	}

	// CARGAR MODELOS (SUB-CATEGORÍAS) DINÁMICAMENTE CUANDO SE SELECCIONA UNA MARCA
	document.addEventListener('change', function (e) {
		if (e.target.matches('input[name="product_mark"]')) {
			if (e.target.checked) {
				addModelBtn.disabled = false;
				addModelBtn.classList.remove('disabled');
			}

			const selectedMarkId = e.target.dataset.mark;

			const companySelect = document.getElementById('select-company');
			const selectedCompany = companySelect?.value || "";
			const params = new URLSearchParams();
			if (selectedCompany) params.append('company', selectedCompany);

			if (!isNaN(selectedMarkId)) {
				modelList.innerHTML = '';

				fetch(`api/get_sub_categories.php?mark_id=${selectedMarkId}&${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				})
				.then(res => res.json())
				.then(data => {
					if (data.success && data.data.length > 0) {
						data.data.forEach(model => {
							const uniqueId = `model-db-${model.category_id}`;
							const row = document.createElement('tr');
							row.className = "categoryContainer";
							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="list-icon">
										<img src="images/sys-img/element-list.png" alt="">
									</div>
								</td>
								<td width="80%" valign="middle" style="padding-left:10px;">${model.category_name}</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-radio">
										<input type="radio" id="${uniqueId}" name="product_model" class="category-radio" data-model="${model.category_id}" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;
							modelList.appendChild(row);
						});
					} else {
						modelList.innerHTML = `<tr data-empty-message><td colspan="3" style="text-align: center; padding:15px 0;">${window.i18n?.no_models_found || 'No models found for this brand.'}</td></tr>`;
					}
				})
				.catch(error => {
					console.error("Error loading subcategories:", error);
				});
			}
		}
	});


	// 📌 script para crear sub-modelo
	let addSubmodelBtn = document.getElementById('add-submodel-btn');
	if (addSubmodelBtn) {
		addSubmodelBtn.addEventListener('click', function(){
			let clicCreateMark = document.getElementById('clic-create-submodel');
			let inputMark = document.getElementById('input-submodel');

			clicCreateMark.style.display = 'none';
			inputMark.style.display = 'block';
		});
	}

	const inputSubmodel = document.getElementById('input-product-submodel');
	const submodelList = document.getElementById('submodel-list');
	const btnCreateSubmodel = document.getElementById('btn-create-submodel');

	if (inputSubmodel) {
		inputSubmodel.addEventListener('input', () => {
			let currentValue = inputSubmodel.value;
	
			if (currentValue === currentValue.toUpperCase()) return;

			let words = inputSubmodel.value.split(" ");
			words = words.map(word => {
				return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
			});
			inputSubmodel.value = words.join(" ");
		});
	}

	if (inputSubmodel) {
		btnCreateSubmodel.addEventListener('click', function (e) {
			e.preventDefault();
			const value = inputSubmodel.value.trim();

			if (value !== '') {
				const emptyRow = submodelList.querySelector('tr[data-empty-message]');
				if (emptyRow) {
					emptyRow.remove();
				}

				const existingRadios = document.querySelectorAll('input[name="product_sub_model"]');
				existingRadios.forEach(r => r.checked = false);

				const uniqueId = `submodel-${Date.now()}`;
				const row = document.createElement('tr');
				row.className = "categoryContainer";
				row.innerHTML = `
					<td width="10%" align="center" valign="middle">
						<div class="list-icon">
							<img src="images/sys-img/element-list.png" alt="">
						</div>
					</td>
					<td width="80%" valign="middle" style="padding-left:10px;">${value}</td>
					<td width="10%" align="center" valign="middle">
						<div class="opcion-radio">
							<input type="radio" id="${uniqueId}" name="product_sub_model" class="category-radio" data-submodel="${value}" checked />
							<label for="${uniqueId}"></label>
						</div>
					</td>
				`;
				submodelList.appendChild(row);
				inputSubmodel.value = '';
			}
		});
	}

	// DETECTAR CAMBIO EN MODELO Y CARGAR SUB-MODELOS
	document.addEventListener('change', function (e) {
		if (e.target.matches('input.category-radio[name="product_model"]')) {
			if (e.target.checked) {
				addSubmodelBtn.disabled = false;
				addSubmodelBtn.classList.remove('disabled');
			}

			const selectedModelId = e.target.dataset.model;

			const companySelect = document.getElementById('select-company');
			const selectedCompany = companySelect?.value || "";
			const params = new URLSearchParams();
			if (selectedCompany) params.append('company', selectedCompany);

			if (!isNaN(selectedModelId)) {
				submodelList.innerHTML = '';

				fetch(`api/get_sub_models.php?model_id=${selectedModelId}&${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				})
				.then(res => res.json())
				.then(data => {
					if (data.success && data.data.length > 0) {
						data.data.forEach(submodel => {
							const uniqueId = `submodel-db-${submodel.category_id}`;
							const row = document.createElement('tr');
							row.className = "categoryContainer";
							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="list-icon">
										<img src="images/sys-img/element-list.png" alt="">
									</div>
								</td>
								<td width="80%" valign="middle" style="padding-left:10px;">${submodel.category_name}</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-radio" style="display: none;"> <!-- oculto con display none -->
										<input type="radio" id="${uniqueId}" name="product_sub_model" class="category-radio" data-submodel="${submodel.category_id}" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;
							submodelList.appendChild(row);
						});
					} else {
						submodelList.innerHTML = `<tr data-empty-message><td colspan="3" style="text-align: center; padding:15px 0;">${window.i18n?.no_submodels_found || 'No submodels found for this model.'}</td></tr>`;
					}
				})
				.catch(error => {
					console.error("Error loading submodels:", error);
				});
			}
		}
	});

	// 📌 JavaScript para enviar datos de creación de marca, modelo o submodelo
	const formAddCategory = document.getElementById("formAddCategory");
	if (formAddCategory) {
		formAddCategory.addEventListener("submit", async function (e) {
			e.preventDefault();

			const selectedMark = document.querySelector('input[name="product_mark"]:checked');
			const selectedModel = document.querySelector('input[name="product_model"]:checked');
			const selectedSubmodel = document.querySelector('input[name="product_sub_model"]:checked');
			const companySelect = document.getElementById("select-company");

			let name = "";
			let cat_parent_sub = null;
			let sub_parent = null;

			if (selectedSubmodel) {
				// ✅ Ingresar submodelo
				name = selectedSubmodel.dataset.submodel;
				cat_parent_sub = selectedMark ? parseInt(selectedMark.dataset.mark) : null;
				sub_parent = selectedModel ? parseInt(selectedModel.dataset.model) : null;

			} else if (selectedModel) {
				// ✅ Ingresar modelo
				name = selectedModel.dataset.model;
				cat_parent_sub = selectedMark ? parseInt(selectedMark.dataset.mark) : null;

			} else if (selectedMark) {
				// ✅ Ingresar marca
				name = selectedMark.dataset.mark;

			} else {
				alert("You must select a Mark, Model, or Submodel.");
				return;
			}

			const formData = new FormData();
			formData.append("category_name", name);
			formData.append("cat_parent_sub", cat_parent_sub ?? "");
			formData.append("sub_parent", sub_parent ?? "");

			// ✅ Añadir company_id si está seleccionado
			if (companySelect && companySelect.value.trim() !== "") {
				formData.append("company_id", companySelect.value);
			} else {
				alert("You must select a company.");
				return;
			}

			try {
				const response = await fetch("api/create_category.php", {
					method: "POST",
					body: formData,
					headers: { Accept: "application/json" },
				});

				const data = await response.json();

				let banner = document.getElementById("status-message");
				let statusText = document.getElementById("status-text");
				let statusImage = document.getElementById("status-image");

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				banner.style.display = "block";
				banner.style.opacity = "1";

				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = "0";
						setTimeout(() => {
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				console.error("Error submitting category:", error);
			}
		});
	}

	const container = document.getElementById('product-list');
	const searchField = document.getElementById('searchField');

	async function fetchAndRenderProducts() {
		if (!container) return;

		markSelect = document.getElementById('search_product_mark');
		modelSelect = document.getElementById('search_product_model');
		submodelSelect = document.getElementById('search_product_sub_model');
		companySelect = document.getElementById('select-company');

		const searchText = searchField?.value.trim() || "";
		let selectedMark = markSelect?.value || "";
		let selectedModel = modelSelect?.value || "";
		let selectedSubmodel = submodelSelect?.value || "";
		let selectedCompany = companySelect?.value || "";

		const params = new URLSearchParams();
		if (searchText) params.append('search', searchText);
		if (selectedMark) params.append('mark', selectedMark);
		if (selectedModel) params.append('model', selectedModel);
		if (selectedSubmodel) params.append('submodel', selectedSubmodel);
		
		if (
			selectedCompany &&
			selectedCompany !== "0" &&
			selectedCompany !== "all" &&
			selectedCompany !== "null" &&
			selectedCompany !== "undefined"
		) {
			params.append('company', selectedCompany);
		}

		try {
			const res = await fetch(`api/get_products.php?${params.toString()}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();
			
			const userRes = await fetch(`api/get_my_info.php`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const userData = await userRes.json();

			container.innerHTML = "";

			if (data.success && data.data.length > 0 && userData.success && userData.data) {
				let userCompanyId = userData.data.company_id;

				data.data.forEach(product => {
					const card = document.createElement('div');
					card.className = 'product-card';

					if (product.sale_unit_type === "1" || product.sale_unit_type === null) {
						unitImg = "images/sys-img/papel-box.png";
						
						const raw = product?.total_weight;
						const w = raw == null ? NaN : Number(String(raw).trim().replace(',', '.'));

						if (Number.isFinite(w) && w > 0) {
							prodDetail = `
								<tr valign="baseline">
									<td colspan="6" style="height: 10px;">
										<table width="100%" align="center" cellspacing="0">
											<tr valign="baseline">
												<td colspan="6" align="center" style="height: 10px; border-top: 1px solid var(--border-light);">
													<p>${window.i18n?.total_weight}<br><strong>${product.total_weight ? product.total_weight + ' kg' : ''}</strong></p>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							`;
						} else {
							prodDetail = '';
						}
					} else {
						unitImg = "images/sys-img/wooden-box.png";
						prodDetail = `
							<tr valign="baseline">
								<td colspan="6" style="height: 10px;">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td style="width: 25%; height: 10px; border-top: 1px solid var(--border-light);">
												<p>${window.i18n?.units}<br><strong>${product.units_per_pack || ''}</strong></p>
											</td>
											<td style="width: 40%; height: 10px; border-top: 1px solid var(--border-light);">
												<p>${window.i18n?.weight_unit}<br><strong>${product.weight_per_unit ? product.weight_per_unit + ' kg' : ''}</strong></p>
											</td>
											<td style="width: 35%; height: 10px; border-top: 1px solid var(--border-light);">
												<p>${window.i18n?.total_weight}<br><strong>${product.total_weight ? product.total_weight + ' kg' : ''}</strong></p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						`;
					}
					
					let isDefaultImage = !product.product_image || product.product_image.trim() === "";
					let productImage = isDefaultImage 
					? unitImg
					: `images/products/${product.product_image}`;

					let imageClass = isDefaultImage ? "grayscale-img" : "";
					
					let minQty = (product.quantity !== null && product.min_quantity !== null && 
					!isNaN(product.quantity) && !isNaN(product.min_quantity) &&
					Number(product.quantity) <= Number(product.min_quantity))
					? "min-qty" : "";

					const markName = product.mark_name || '';
					const modelName = product.model_name || '';

					const hasMark = product.product_mark !== null &&
						product.product_mark !== undefined &&
						String(product.product_mark) !== '' &&
						String(product.product_mark) !== '0';

					const hasModel = product.product_model !== null &&
						product.product_model !== undefined &&
						String(product.product_model) !== '' &&
						String(product.product_model) !== '0';

					const markText = hasMark
						? `<strong>${markName}</strong>`
						: markName;

					const modelText = hasModel
						? `<strong>${modelName}</strong>`
						: modelName;

					card.innerHTML = `
					<div class="product-pic">
						<img src="${productImage}" alt="${product.product_name}" class="${imageClass}" />
					</div>
					<div class="product-desc">
						<table width="90%" align="center" cellspacing="0">
							<tr valign="baseline">
								<td colspan="6" style="height: 10px;">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td style="width: 60%; height: 10px;">
												<p style="margin: 10px 0 0;">${product.product_name}</p>
												<p class="mini-title" style="margin: 0;">${product.hs_code || ''}</p>
											</td>
											<td style="width: 40%; height: 10px;" align="right">
												<p style="margin: 10px 0 0;">${window.i18n?.qty}: <strong class="${minQty}">${product.quantity || ''}</strong></p>
												<p class="mini-title" style="margin: 0;">${product.purpose_text || ''}</p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr valign="baseline">
								<td colspan="6" style="height: 10px;">
									<p>${markText} - ${modelText}</p>
								</td>
							</tr>
							<tr valign="baseline">
								<td colspan="6" style="height: 10px;">
									${product.submodel_name || ''}
								</td>
							</tr>
							${prodDetail}
							<tr valign="baseline">
								<td style="width: 50%; border-top: 1px solid var(--border-light);">
									<p>${window.i18n?.year}<br><strong>${product.product_year == 0 || product.product_year == null ? 'N/E' : product.product_year}</strong></p>
								</td>
								<td style="width: 50%; border-top: 1px solid var(--border-light);">
									<p>${window.i18n?.price}<br><strong>${product.price ? '$' + product.price + ' ' + product.currency : ''}</strong></p>
								</td>
							</tr>
						</table>
						<div class="product-menu">
							<img src="images/sys-img/menu-icon.png" alt="product-menu">
						</div>
					</div>
					`;
					container.appendChild(card);

					const cardMenuBtn = card.querySelector('.product-menu');
					cardMenuBtn.addEventListener('click', () => {
						openProductForm(product.product_id, userCompanyId);

						handlePopupClose("product-options", ".formular-frame", []);
					});
				});
			} else {
				container.innerHTML = `
					<p class="isNotLinkedToCompany hidden" style="text-align: center; color: var(--warning-orange);">To activate this section you must complete the company details <a href="profile.php">here.</a></p>
					<p style="text-align:center;">No products found</p>
				`;
			}
		} catch (error) {
			console.error("Error loading products:", error);
			container.innerHTML = `<p style="text-align:center;">Error loading products</p>`;
		}
	}

	searchField?.addEventListener('keyup', fetchAndRenderProducts);
	document.addEventListener('change', (e) => {
		const id = e.target?.id;
		if (["search_product_mark", "search_product_model", "search_product_sub_model", "select-company"].includes(id)) {
			fetchAndRenderProducts();
		}
	});

	// 📌 JavaScript para recoger datos de los select del formulario de busqueda(search)
	initCategorySelectors('search_product_mark', 'search_product_model', 'search_product_sub_model', 'select-company');

	populateCompanies('select-company');

	fetchAndRenderProducts();

	async function openProductForm(productId, userCompanyId = null) {
		scrollToTopIfNeeded();
	
		const productOptions = document.getElementById('product-options');
		const popupContent = productOptions.querySelector('.formular-frame');
		const productName = document.getElementById('product-name');
	
		if (!productId) return;

		const companySelect = document.getElementById('select-company');
  		const selectedCompany = companySelect?.value || '';

		try {
			const params = new URLSearchParams({ product_id: String(productId) });
    		if (selectedCompany) params.append('company', selectedCompany);

			const res = await fetch(`api/get_products.php?${params.toString()}`);
			const data = await res.json();

			if (productOptions && popupContent) {
				resetPopupView(['product-menu-buttons'], [
					'assign-sale-section',
					'edit-product-modal',
					'delete-product-modal'
				]);

				const requestProductBtn = document.getElementById('requestProductBtn');
				const editBtn = document.getElementById('editProductBtn');
				const deleteBtn = document.getElementById('deleteProductBtn');

				let product = undefined;
				if (data?.success) {
					if (Array.isArray(data.data)) {
						const pid = String(productId);
						product = data.data.find(p => String(p.product_id) === pid);
					} else if (data.data && typeof data.data === 'object') {
						product = (String(data.data.product_id) === String(productId))
							? data.data : undefined;
					}
				}

				if (product && productName) {
					productName.innerHTML =
						(product.product_name || 'Unnamed Product') + '<br>' +
						((product.mark_name || product.model_name)
							? (product.mark_name || 'undefined') + ' - ' + (product.model_name || 'undefined') 
							: '');
				}

				if (product.company_id === userCompanyId) {
					requestProductBtn.classList.add('hidden');
				} else {
					requestProductBtn.classList.remove('hidden');
				}
				

				productOptions.style.display = 'block';
				productOptions.style.opacity = '0';
				productOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					productOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
				

				// Botón: Request Product
				if (requestProductBtn) {
					requestProductBtn.onclick = () => {
						requestProductBtn.setAttribute('data-product-id', productId);
						if (!productId) {
							alert("Product ID not found.");
							return;
						}
						// console.log("Request sent ", productId);
						showConfirmModal("Request this Product", "Are you sure you want request this product?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("product_id", productId);
				
							try {
								const response = await fetch('api/request_product.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											window.location.href = data.redirect_url || window.location.href;
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error requesting product:", error);
								alert("Error requesting product. Check console.");
							}
						});
					};
				}
				
				// Botón: Edit product
				if (editBtn) {
					editBtn.setAttribute('data-product-id', productId);
					editBtn.onclick = () => {
						const menuDiv = document.getElementById('product-menu-buttons');
						const editDiv = document.getElementById('edit-product-modal');

						const productId = editBtn.getAttribute('data-product-id');
						if (!productId) return;

						openEditProductForm(productId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					}
				}

				// Botón: Delete product
				if (deleteBtn) {
					deleteBtn.onclick = () => {
						deleteBtn.setAttribute('data-product-id', productId);
						
						if (!productId) {
							alert("Product ID not found.");
							return;
						}

						showConfirmModal(window.i18n?.delete_product_title || "Delete Product", window.i18n?.confirm_delete_product || "Are you sure you want to delete this product?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("product_id", productId);
				
							try {
								const response = await fetch('api/delete_product.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											window.location.href = data.redirect_url || window.location.href;
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting product:", error);
								alert("Error deleting product. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading product info:", error);
		}
	}

	async function openEditProductForm(productId) {
		const formEditProduct = document.getElementById('formEditProduct');
		if (!formEditProduct) return;
	
		formEditProduct.setAttribute('data-product-id', productId);

		const companySelect = document.getElementById('select-company');
		const selectedCompany = companySelect?.value || "";

		const params = new URLSearchParams();
		params.append('product_id', productId);

		if (selectedCompany) params.append('company', selectedCompany);
	
		try {
			const response = await fetch(`api/get_products.php?${params.toString()}`);
			const data = await response.json();
	
			if (data.success && data.data.length > 0) {
				const product = data.data.find(p => p.product_id == productId);
				
				if (!product) return;
				
				// Llenar campos del formulario
				document.getElementById('edit_units').value = product.units_per_pack || '';
				document.getElementById('edit_weight_unit').value = product.weight_per_unit || '';
				document.getElementById('edit_total_weight').value = product.total_weight || '';
				document.getElementById('edit_product_name').value = product.product_name || '';
				document.getElementById('edit_hs_code').value = product.hs_code || '';
				document.getElementById('edit_product_year').value = product.product_year || '';
				document.getElementById('edit_price').value = product.price || '';
				document.getElementById('edit_quantity').value = product.quantity || '';
				document.getElementById('edit_min_quantity').value = product.min_quantity || '';
				document.getElementById('edit_description').value = product.description || '';
	
				const preview = document.getElementById('edit-product-image-preview');
				if (preview) {
					if (product.product_image && product.product_image.trim() !== "") {
						preview.src = `images/products/${product.product_image}`;
						preview.style.display = 'block';
						preview.style.visibility = 'visible';
						preview.style.opacity = '1';
					} else {
						preview.src = '';
						preview.style.display = 'none';
					}
				}

				initUnitTypeControls(
					'edit_unit_type_1',      // radio single
					'edit_unit_type_2',      // radio pack
					'edit_units',            // input unidades
					'edit_weight_unit',      // input peso/unidad
					'edit_total_weight',     // input peso total
					{ decimals: 3, acceptComma: true, minUnits: 1, defaultMode: product.sale_unit_type}
				);
				
				initDragAndDrop('edit-drop-product-area', 'edit_Product_image', 'edit-product-image-preview');

				await populateProductTypes('edit_product_type', product.product_type, product.company_id, true);

				await populateCurrencies('edit_currency', product.currency);
	
				await initCategorySelectors('edit_product_mark', 'edit_product_model', 'edit_product_sub_model', 'select-company');
				
				document.getElementById('edit_product_mark').value = product.product_mark || '';

				await loadModels(product.product_mark, 'edit_product_model', product.product_model);

				await loadSubModels(product.product_model, 'edit_product_sub_model', product.product_sub_model);

				await populateProductPurpose('edit_product_purpose', product.purpose);
				
				handlePopupClose("product-options", ".formular-frame", []);
			}
		} catch (error) {
			console.error("Error loading product data:", error);
		}
	}

	async function loadModels(markId, modelSelectId, selectedModel = '') {
		const modelSelect = document.getElementById(modelSelectId);
		if (!modelSelect || !markId) return;
	
		modelSelect.innerHTML = `<option value="">Select Model</option>`;
		modelSelect.disabled = true;
	
		try {
			const res = await fetch(`api/get_sub_categories.php?mark_id=${markId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();
			
			if (data.success && data.data.length > 0) {
				data.data.forEach(model => {
					const option = document.createElement('option');
					option.value = model.category_id;
					option.textContent = model.category_name;
					if (String(option.value) === String(selectedModel)) {
						option.selected = true;
					}
					modelSelect.appendChild(option);
				});
				modelSelect.disabled = false;
			} else {
				modelSelect.innerHTML += `<option value="">No models found</option>`;
			}
		} catch (error) {
			console.error("Error loading models:", error);
		}
	}

	async function loadSubModels(modelId, submodelSelectId, selectedSubmodel = '') {
		const submodelSelect = document.getElementById(submodelSelectId);
		if (!submodelSelect || !modelId) return;
	
		submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;
		submodelSelect.disabled = true;
	
		try {
			const res = await fetch(`api/get_sub_models.php?model_id=${modelId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();
	
			if (data.success && data.data.length > 0) {
				data.data.forEach(submodel => {
					const option = document.createElement('option');
					option.value = submodel.category_id;
					option.textContent = submodel.category_name;
					if (String(option.value) === String(selectedSubmodel)) {
						option.selected = true;
					}
					submodelSelect.appendChild(option);
				});
				submodelSelect.disabled = false;
			} else {
				submodelSelect.innerHTML += `<option value="">No submodels found</option>`;
			}
		} catch (error) {
			console.error("Error loading submodels:", error);
		}
	}

	const formEditProduct = document.getElementById('formEditProduct');
	if (formEditProduct) {
		formEditProduct.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);
			formData.append('edit_product_id', formEditProduct.getAttribute('data-product-id'));

			try {
				const response = await fetch('api/update_product.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				const data = await response.json();

				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');

				if (banner && statusText && statusImage) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "images/sys-img/loading.gif";
					showBanner(banner);
				}

				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url || window.location.href;
						});
					}, 3000);
				}
			} catch (error) {
				console.error("Error updating product:", error);
			}
		});
	}

	setupBackToMenuButton(
		'.back-to-menu-btn', 
		['assign-sale-section', 'receive-as-initial', 'edit-product-modal'], 
		'product-menu-buttons', 
		'product-options'
	);
	//################################################################ END PRODUCTS ##################################################################

	//################################################################ STORAGE #####################################################################
	const storageSidebarTable = document.getElementById('storageTable');
	const storageDetails = document.getElementById('storageDetails');
	const searchFieldStorage = document.getElementById('searchFieldStorage');

	if (storageSidebarTable && searchFieldStorage) {
		async function fetchAndRenderStorages() {
			try {
				const searchTerm = (searchFieldStorage?.value || '').trim().toLowerCase();
				const hasSearch = searchTerm !== '';
				const params = new URLSearchParams();
				
				if (!hasSearch) {
					storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">No result yet</p></td></tr>`;
					storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">No result yet</p>`;
				}

				if (hasSearch) params.append('search', searchTerm);
				const res = await fetch(`api/get_storages.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await res.json();
				
				if (data.success) {
					renderStoragesTable(data.data, null, hasSearch);
				} else {
					storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">No result yet</p></td></tr>`;
					storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">No result yet</p>`;
				}
			} catch (err) {
				console.error("Error loading storages:", err);
				storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">Error loading storages</p></td></tr>`;
				storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">Error loading storages</p>`;
			}
		}

		// Inicializar búsqueda
		fetchAndRenderStorages();
		searchFieldStorage.addEventListener('keyup', fetchAndRenderStorages);
	}

	// 🔹 Función para renderizar la tabla de storages (reutilizable)
	function renderStoragesTable(data, selectedId = null, hasSearch = false) {
		storageSidebarTable.innerHTML = '';
		storageDetails.innerHTML = '';

		const payload = Array.isArray(data) ? data[0] : data;
		const slots = payload?.slots || [];
		const storages = payload?.storages || [];
		const products = payload?.products || [];

		if (!hasSearch) {
			storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">No result yet</p></td></tr>`;
			storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">No result yet</p>`;
			return;
		}

		if (!Array.isArray(products) || products.length === 0) {
			storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">No products found</p>`;
		} else {
			renderStorageDetails({
				type: 'product-search',
				products: products,
				storages: storages
			}, null);
		}

		if (!Array.isArray(slots) || slots.length === 0) {
			storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">No slots found.</p></td></tr>`;
			return;
		}

		slots.forEach(slot => {
			const slotStatus = parseInt(slot.status, 10);

			const statusConfig = {
				0: { color: 'red', text: 'Disabled' },
				1: { color: 'green', text: 'In Use' }
			};

			const {
				color: statusColor,
				text: statusText
			} = statusConfig[slotStatus] || { color: 'gray', text: 'Unknown' };

			const row = document.createElement('tr');
			row.setAttribute('data-id', slot.slot_id);

			row.innerHTML = `
				<td class="slot-click-area" width="85%" align="left" valign="top" style="cursor:pointer;">
					<div style="padding: 0 5px;">
						Slot Name: <strong>${slot.slot_name || '—'}</strong><br>
						<p>Status: <strong style="color:${statusColor};">${statusText}</strong></p>
					</div>
				</td>
				<td width="15%" align="left" valign="top">
					<div class="shipping-menu" id="slotMenuBtn" >
						<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
					</div>
				</td>
			`;

			const clickableCell = row.querySelector('.slot-click-area');

			if (clickableCell) {
				clickableCell.addEventListener('click', () => {
					localStorage.setItem("selectedStorageId", slot.slot_id);
					renderStorageDetails({
						type: 'slot',
						slot: slot,
						storages: storages,
						products: products
					}, row);
				});
			}

			storageSidebarTable.appendChild(row);

			if (String(slot.slot_id) === String(selectedId)) {
				renderStorageDetails({
					type: 'slot',
					slot: slot,
					storages: storages,
					products: products
				}, row);
				row.style.backgroundColor = 'var(--clr-white)';
			}

			const slotMenuBtn = row.querySelector('#slotMenuBtn');
			if (slotMenuBtn) {
				slotMenuBtn.addEventListener('click', (e) => {
					openStorageSlotMenu(slot.slot_id);
					
					handlePopupClose("slot-options", ".formular-frame", []);
				});
			}
		});
	}

	async function openStorageSlotMenu(slotId) {
		scrollToTopIfNeeded();

		const slotOptions = document.getElementById('slot-options');
		const popupContent = slotOptions.querySelector('.formular-frame');
		const slotName = document.getElementById('slot-name');

		if (!slotId) return;

		try {
			const res = await fetch(`api/get_slot_info.php`);
			const data = await res.json();

			if (slotOptions && popupContent) {
				resetPopupView(['slot-menu-buttons'], [
					'edit-slot-modal'
				]);

				// const editSlotBtn = document.getElementById('editSlotBtn');
				const deleteSlotBtn = document.getElementById('deleteSlotBtn');

				let slot = null;
				if (data?.success && Array.isArray(data.data)) {
					const sid = String(slotId);
					slot = data.data.find(item => String(item.slot_id) === sid);
				}

				if (slotName) {
					slotName.textContent = slot.slot_name || 'Unnamed slot';
				}

				slotOptions.style.display = 'block';
				slotOptions.style.opacity = '0';
				slotOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					slotOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				// if (editSlotBtn) {
				// 	editSlotBtn.setAttribute('data-slot-id', slotId);
				// 	editSlotBtn.onclick = async () => {
				// 		const menuDiv = document.getElementById('slot-menu-buttons');
				// 		const editDiv = document.getElementById('edit-slot-modal');
					
				// 		if (editDiv) {
				// 			editDiv.style.display = 'none';
				// 		}

				// 		const slotId = editSlotBtn.getAttribute('data-slot-id');
				// 		if (!slotId) return;

				// 		const formFrame = document.getElementById('formular-medium-frame-2');
				// 		if (formFrame) {
				// 			formFrame.classList.add('expanded-medium');
				// 		}

				// 		openEditSlotForm(slotId);

				// 		animateHeightChange(popupContent, editDiv, () => {
				// 			fadeOutAndHide(menuDiv, () => {
				// 				showWithFadeIn(editDiv);
				// 			});
				// 		});
				// 	};
				// }

				if (deleteSlotBtn) {
					deleteSlotBtn.setAttribute('data-slot-id', slotId);
					deleteSlotBtn.onclick = () => {
						// deleteSlotBtn.setAttribute('data-slot-id', slotId);
						
						if (!slotId) {
							alert("Slot ID not found.");
							return;
						}

						showConfirmModal("Delete Slot", "Are you sure you want to delete this Slot?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("slot_id", slotId);
				
							try {
								const response = await fetch('api/delete_slot.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											window.location.href = data.redirect_url || window.location.href;
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting slot:", error);
								alert("Error deleting slot. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading slot info:", error);
		}
	}

	// async function openEditSlotForm(slotId) {

	// }

	async function renderStorageDetails(payload, clickedRow) {
		const allRows = storageSidebarTable.querySelectorAll('.clickable-row');
		allRows.forEach(row => row.style.backgroundColor = '');

		if (clickedRow) {
			clickedRow.style.backgroundColor = 'var(--clr-white)';
		}

		function buildProductCard(product) {
			if (!product) {
				return `
					<div class="notification-detail-card">
						<div class="notification-product-desc">
							<p>Product data not available.</p>
						</div>
					</div>
				`;
			}

			let unitImg = "";
			let prodDetail = "";

			if (product.sale_unit_type === "1" || product.sale_unit_type === null) {
				unitImg = "images/sys-img/papel-box.png";

				const raw = product?.total_weight;
				const w = raw == null ? NaN : Number(String(raw).trim().replace(',', '.'));

				if (Number.isFinite(w) && w > 0) {
					prodDetail = `
						<tr valign="baseline">
							<td colspan="6" style="height: 10px;">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline">
										<td colspan="6" align="center" style="height: 10px; border-top: 1px solid var(--border-light);">
											<p>Total Weight<br><strong>${product.total_weight ? product.total_weight + ' kg' : ''}</strong></p>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					`;
				}
			} else {
				unitImg = "images/sys-img/wooden-box.png";
				prodDetail = `
					<tr valign="baseline">
						<td colspan="6" style="height: 10px;">
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline">
									<td style="width: 25%; height: 10px; border-top: 1px solid var(--border-light);">
										<p>Units<br><strong>${product.units_per_pack || ''}</strong></p>
									</td>
									<td style="width: 40%; height: 10px; border-top: 1px solid var(--border-light);">
										<p>Weight/unit<br><strong>${product.weight_per_unit ? product.weight_per_unit + ' kg' : ''}</strong></p>
									</td>
									<td style="width: 35%; height: 10px; border-top: 1px solid var(--border-light);">
										<p>Total Weight<br><strong>${product.total_weight ? product.total_weight + ' kg' : ''}</strong></p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				`;
			}

			const isDefaultImage = !product.product_image || product.product_image.trim() === "";
			const productImage = isDefaultImage
				? unitImg
				: `images/products/${product.product_image}`;

			const imageClass = isDefaultImage ? "grayscale-img" : "";

			const minQty = (
				product.quantity !== null &&
				product.min_quantity !== null &&
				!isNaN(product.quantity) &&
				!isNaN(product.min_quantity) &&
				Number(product.quantity) <= Number(product.min_quantity)
			) ? "min-qty" : "";

			return `
				<div class="notification-detail-card">
					<div class="notification-product-pic">
						<img src="${productImage}" alt="${product.product_name || ''}" class="${imageClass}" />
					</div>
					<div class="notification-product-desc">
						<table width="90%" align="center" cellspacing="0">
							<tr valign="baseline">
								<td style="width: 50%; height: 20px;">
									<p style="margin: 10px 0 0;">${product.product_name || ''}</p>
								</td>
								<td style="width: 50%; height: 20px;" align="right">
									<p style="margin: 10px 0 0;">Qty: <strong class="${minQty}">${product.quantity ?? ''}</strong></p>
								</td>
							</tr>
							<tr valign="baseline">
								<td colspan="2" style="height: 20px;">
									<h3><strong>${(product.mark_name || '') + (product.model_name ? ' - ' + product.model_name : '')}</strong></h3>
								</td>
							</tr>
							<tr valign="baseline">
								<td colspan="2" style="height: 20px;">
									${product.submodel_name || ''}
								</td>
							</tr>
							${prodDetail}
							<tr valign="baseline">
								<td style="width: 50%; border-top: 1px solid var(--border-light);">
									<p>Year<br><strong>${product.product_year || ''}</strong></p>
								</td>
								<td style="width: 50%; border-top: 1px solid var(--border-light);">
									<p>Price<br><strong>${product.price ? '$' + product.price + ' ' + product.currency : ''}</strong></p>
								</td>
							</tr>
						</table>
					</div>
				</div>
			`;
		}

		// Caso 1: mostrar productos automáticamente
		if (payload?.type === 'product-search') {
			const products = payload.products || [];

			const uniqueSlotIds = [...new Set(
				products
					.flatMap(p => Array.isArray(p.slot_ids) ? p.slot_ids : (p.slot_id != null ? [p.slot_id] : []))
					.filter(v => v !== null && v !== undefined && v !== '')
			)].sort((a, b) => Number(a) - Number(b));

			const uniqueSlotNames = [...new Set(
				products
					.flatMap(p => Array.isArray(p.slot_names) ? p.slot_names : (p.slot_name ? [p.slot_name] : []))
					.filter(v => v !== null && v !== undefined && v !== '')
			)].sort((a, b) => String(a).localeCompare(String(b)));

			const sharedSlotName =
				uniqueSlotIds.length === 1 && uniqueSlotNames.length === 1
					? uniqueSlotNames[0]
					: uniqueSlotNames.join(', ');

			storageDetails.innerHTML = `
				<div class="storage-header">
					<table width="100%" align="center" cellspacing="0">
						<tr valign="baseline" class="form_height">
							<td width="47%" align="left" valign="middle">
								<p class="mini-title">Slot Name:</p>
								<strong>${sharedSlotName || '—'}</strong>
							</td>
							<td width="50%" align="center" valign="middle"></td>
							<td width="3%" align="center" valign="middle">
								<div class="shipping-menu" id="shippingMenuBtn" style="display: none;"> // QUITA EL NONE
									<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
								</div>
							</td>
						</tr>
						<tr class="form_height">
							<td colspan="2" align="left" valign="middle">
								<p class="mini-title">Products found:</p>
								<strong>${products.length}</strong>
							</td>
						</tr>
					</table>
				</div>
				<div class="storage-list">
					${products.map(product => buildProductCard(product)).join('')}
				</div>
			`;

			return;
		}

		// Caso 2: clic sobre slot
		if (payload?.type === 'slot') {
			const slot = payload.slot || {};
			const storages = payload.storages || [];

			const relatedStorages = storages.filter(
				s => String(s.slot_id) === String(slot.slot_id)
			);

			storageDetails.innerHTML = `
				<div class="storage-header">
					<table width="100%" align="center" cellspacing="0">
						<tr valign="baseline">
							<td width="50%" align="left" valign="middle">
								<p class="mini-title">Slot Name:</p>
								<strong>${slot.slot_name || '—'}</strong>
							</td>
							<td width="50%" align="center" valign="middle"></td>
						</tr>
						<tr valign="baseline">
							<td width="100%" align="left" valign="middle"></td>
						</tr>
						<tr valign="baseline">
							<td width="100%" align="left" valign="middle">
								<p class="mini-title">Description:</p>
								${slot.slot_description || '—'}
							</td>
						</tr>
					</table>
				</div>
				<div class="storage-list">
					${relatedStorages.length > 0
						? relatedStorages.map(storage => buildProductCard(storage.product)).join('')
						: `<p>No storages linked to this slot.</p>`}
				</div>
			`;
			
			return;
		}
	}

	const storageMenuBtns = document.getElementById('storageMenuBtns');
	if (storageMenuBtns) {
		storageMenuBtns.addEventListener('click', () => {
			openStorageForm();

			handlePopupClose("storage-options", ".formular-frame", []);
		});
	}

	document.addEventListener('change', function (e) {
		if (e.target.matches('input[name="slot_edit_info"]')) {
			const notSlotForm = document.getElementById('not-slot-form');
			const slotForm = document.getElementById('slot-form');
			const slotActionBtn = document.getElementById('slot-action-btn');
			if (e.target.checked) {
				notSlotForm.classList.add('hidden');
				slotForm.classList.remove('hidden');
				slotActionBtn.value = "Select Slot";
			}

			const selectedSlotId = e.target.dataset.slot;
			loadSlotFormOrData(selectedSlotId);
		}
	});

	const slotFormFields = document.querySelectorAll(
		'#slot-form input:not([type="hidden"]), #slot-form textarea, #slot-form select'
	);

	const allSlotFields = [...slotFormFields];
	slotFormFields.forEach(field => {
		const eventType = field.type === 'checkbox' || field.tagName === 'SELECT'
			? 'change'
			: 'input';

		field.addEventListener(eventType, () => {
			const fieldKey = field.id;
			const currentValue = field.type === 'checkbox'
				? (field.checked ? '1' : '0')
				: (field.value ?? '');

			const originalValue = field.type === 'checkbox'
				? String(originalSlotData[fieldKey] ?? '0')
				: String(originalSlotData[fieldKey] ?? '');

			const slotActionBtn = document.getElementById('slot-action-btn');

			if (currentValue !== originalValue) {
				showChangeAlert();
				slotActionBtn.value = "Save Changes";
			} else {
				checkIfAnySlotChange(allSlotFields);
			}
		});
	});

	function checkIfAnySlotChange(elements) {
		hasChanges = Array.from(elements).some(el => {
			const field = el.id;
			const currentValue = el.value ?? '';
			const originalValue = originalSlotData[field] ?? '';
			return currentValue !== originalValue;
		});

		if (!hasChanges) hideSlotChangeBanner();
	}

	function hideSlotChangeBanner() {
		const banner = document.getElementById('status-message');
		if (banner) {
			hideBanner(banner);
		}
	}

	async function openStorageForm() {
		scrollToTopIfNeeded();

		const storageOptions = document.getElementById('storage-options');
		const popupContent = storageOptions.querySelector('.formular-frame');

		try {
			if (storageOptions && popupContent) {
				resetPopupView(['storage-menu-buttons'], [
					'manage-storage-modal',
					'manage-slot-modal'
				]);

				const manageSlotBtn = document.getElementById('manageSlotBtn');
				const manageStorageBtn = document.getElementById('manageStorageBtn');

				storageOptions.style.display = 'block';
				storageOptions.style.opacity = '0';
				storageOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					storageOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				if (manageSlotBtn) {
					manageSlotBtn.onclick = async () => {
						const menuDiv = document.getElementById('storage-menu-buttons');
						const manageDiv = document.getElementById('manage-slot-modal');
						
						if (manageDiv) {
							manageDiv.style.display = 'none';
						}

						initSlotList({
							listId: 'slot-list',
							searchId: 'input-search-slot',
							radioName: 'slot_edit_info'
						});

						const formFrame = document.getElementById('formular-medium-frame');
						if (formFrame) {
							formFrame.classList.add('expanded-medium');
						}
						
						animateHeightChange(popupContent, manageDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(manageDiv);
							});
						});
					}
				}

				if (manageStorageBtn) {
					manageStorageBtn.onclick = () => {
						const menuDiv = document.getElementById('storage-menu-buttons');
						const manageDiv = document.getElementById('manage-storage-modal');

						if (manageDiv) {
							manageDiv.style.display = 'none';
						}

						initSlotList({
							listId: 'storages-list',
							searchId: 'input-search-storage',
							radioName: 'storages_info'
						});

						initProductList({
							listId: 'products-list',
							searchId: 'input-search-product',
							checkName: 'products_info[]'
						});
			
						const formFrame = document.getElementById('formular-medium-frame');
						if (formFrame) {
							formFrame.classList.add('expanded-medium');
						}
						
						animateHeightChange(popupContent, manageDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(manageDiv);
							});
						});
					}
				}
			}
		} catch (error) {
			console.error("Error loading storage management:", error);
		}
	}

	// 📌 Cargar la lista de slot
	async function initSlotList({
		listId,
		searchId = null,
		radioName = 'slot_edit_info',
		emptyMessage = 'No slots found.'
	}) {
		const slotList = document.getElementById(listId);
		const inputSearchSlot = searchId ? document.getElementById(searchId) : null;

		if (!slotList) return;

		const renderSlots = async () => {
			try {
				const searchSlot = inputSearchSlot?.value.trim() || '';
				const params = new URLSearchParams();

				if (searchSlot) {
					params.append('search', searchSlot);
				}

				const response = await fetch(`api/get_slot_info.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				const data = await response.json();

				slotList.innerHTML = '';

				if (data.success && Array.isArray(data.data) && data.data.length > 0) {
					data.data.forEach(slot => {
						const uniqueId = `${radioName}-${slot.slot_id}`;
						const row = document.createElement('tr');
						row.className = 'categoryContainer';

						row.innerHTML = `
							<td width="10%" align="center" valign="middle">
								<div class="list-icon">
									<img src="images/sys-img/element-list.png" alt="">
								</div>
							</td>
							<td width="80%" valign="middle" style="padding-left:10px;">
								${slot.slot_name || ''}
							</td>
							<td width="10%" align="center" valign="middle" style="position: relative;">
								<div class="opcion-radio">
									<input
										type="radio"
										id="${uniqueId}"
										name="${radioName}"
										class="category-radio"
										value="${slot.slot_id}"
										data-slot="${slot.slot_id}"
									/>
									<label for="${uniqueId}"></label>
								</div>
							</td>
						`;

						slotList.appendChild(row);
					});
				} else {
					slotList.innerHTML = `
						<tr>
							<td colspan="3" align="center" valign="middle">
								${emptyMessage}
							</td>
						</tr>
					`;
				}
			} catch (error) {
				console.error('Error loading slots:', error);
				slotList.innerHTML = `
					<tr>
						<td colspan="3" align="center" valign="middle">
							Error loading slots.
						</td>
					</tr>
				`;
			}
		};

		await renderSlots();

		if (inputSearchSlot && !inputSearchSlot.dataset.boundSlotSearch) {
			inputSearchSlot.addEventListener('input', renderSlots);
			inputSearchSlot.dataset.boundSlotSearch = '1';
		}
	}

	// 📌 Cargar la lista de productos
	async function initProductList({
		listId,
		searchId = null,
		checkName = 'products_info[]',
		emptyMessage = 'No products found.',
		selectedProductIds = []
	}) {
		const productList = document.getElementById(listId);
		const inputSearchProduct = searchId ? document.getElementById(searchId) : null;

		if (!productList) return;

		const selectedSet = new Set((selectedProductIds || []).map(String));

		const renderProducts = async () => {
			try {
				const searchProduct = inputSearchProduct?.value.trim() || '';
				const params = new URLSearchParams();

				if (searchProduct) {
					params.append('search', searchProduct);
				}

				const response = await fetch(`api/get_products.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				const data = await response.json();

				productList.innerHTML = '';

				if (data.success && Array.isArray(data.data) && data.data.length > 0) {
					data.data.forEach(product => {
						const uniqueId = `${checkName.replace(/[\[\]]/g, '')}-${product.product_id}`;

						const productImage = product.product_image && product.product_image.trim() !== ''
							? `images/products/${product.product_image}`
							: `images/sys-img/wooden-box.png`;

						const isChecked = selectedSet.has(String(product.product_id));

						const row = document.createElement('tr');
						row.className = 'categoryContainer';

						row.innerHTML = `
							<td width="10%" align="center" valign="middle">
								<div class="list-icon">
									<img src="${productImage}" alt="${product.product_name || ''}" width="32" height="32">
								</div>
							</td>
							<td width="80%" valign="middle" style="padding-left:10px;">
								<strong>${product.product_name || ''}</strong><br>
								<small>
									${product.mark_name || ''}
									${product.model_name ? ' - ' + product.model_name : ''}
									${product.submodel_name ? ' - ' + product.submodel_name : ''}
								</small>
							</td>
							<td width="10%" align="center" valign="middle" style="position: relative;">
								<div class="opcion-checkbox">
									<input
										type="checkbox"
										id="${uniqueId}"
										name="${checkName}"
										class="category-checkbox"
										value="${product.product_id}"
										data-product="${product.product_id}"
										${isChecked ? 'checked' : ''}
									/>
									<label for="${uniqueId}"></label>
								</div>
							</td>
						`;

						productList.appendChild(row);
					});
				} else {
					productList.innerHTML = `
						<tr>
							<td colspan="3" align="center" valign="middle">
								${emptyMessage}
							</td>
						</tr>
					`;
				}

				updateStorageActionButtonState();
			} catch (error) {
				console.error('Error loading products:', error);
				productList.innerHTML = `
					<tr>
						<td colspan="3" align="center" valign="middle">
							Error loading products.
						</td>
					</tr>
				`;
			}
		};

		await renderProducts();

		if (inputSearchProduct && !inputSearchProduct.dataset.boundProductSearch) {
			inputSearchProduct.addEventListener('input', renderProducts);
			inputSearchProduct.dataset.boundProductSearch = '1';
		}
	}

	const addSlotBtn = document.getElementById('add-slot-btn');
	if (addSlotBtn) {
		addSlotBtn.addEventListener('click', async function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			addSlotBtn.disabled = true;
			setTimeout(() => addSlotBtn.disabled = false, 1000);

			try {
				const notSlotForm = document.getElementById('not-slot-form');
				const slotForm = document.getElementById('slot-form');
				const slotActionBtn = document.getElementById('slot-action-btn');
				const slotRadios = document.querySelectorAll('input[type="radio"][name="slot_edit_info"]');

				slotRadios.forEach(radio => {
					radio.checked = false;
				});

				if (notSlotForm && slotForm && slotActionBtn) {
					notSlotForm.classList.add('hidden');
					slotForm.classList.remove('hidden');
					slotActionBtn.value = "Add Slot";
				}

				loadSlotFormOrData();
			} catch (err) {
				console.error("Error opening add company form:", err);
			}
		});
	}

	// 📌 Manejo del formulario de crear slot
	let formManageSlot = document.getElementById('formManageSlot');
	if (formManageSlot) {
		formManageSlot.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/create_slot.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error processing request.";
				statusImage.src = data.img_gif;
				showBanner(banner);
			}
		});
	}

	function updateStorageActionButtonState() {
		const storageActionBtn = document.getElementById('storage-action-btn');
		if (!storageActionBtn) return;

		const hasSlotSelected = !!document.querySelector('input[name="storages_info"]:checked');
		const hasProductSelected = !!document.querySelector('input[name="products_info[]"]:checked');

		if (hasSlotSelected || hasProductSelected) {
			storageActionBtn.value = "Save Changes";
		} else {
			storageActionBtn.value = "Add Storage";
		}
	}

	document.addEventListener('change', async function (e) {
		if (
			e.target.matches('input[name="storages_info"]') ||
			e.target.matches('input[name="products_info[]"]')
		) {
			updateStorageActionButtonState();
		}

		if (e.target.matches('input[name="storages_info"]')) {
			const selectedSlotId = e.target.checked ? e.target.dataset.slot : null;
			await syncProductsFromSelectedSlot(selectedSlotId);
		}
	});

	function showStorageSelectionMessage(message) {
		const banner = document.getElementById('status-message');
		const statusText = document.getElementById('status-text');
		const statusImage = document.getElementById('status-image');

		if (!banner || !statusText || !statusImage) {
			alert(message);
			return;
		}

		statusText.innerText = message;
		statusImage.src = "../images/sys-img/error.gif";
		showBanner(banner);
	}

	const formManageStorage = document.getElementById('formManageStorage');
	if (formManageStorage) {
		formManageStorage.addEventListener('submit', async function (e) {
			e.preventDefault();

			const selectedSlot = document.querySelector('input[name="storages_info"]:checked');
			const selectedProducts = document.querySelectorAll('input[name="products_info[]"]:checked');
			
			if (!selectedSlot && selectedProducts.length === 0) {
				showStorageSelectionMessage("You have not selected a slot or product.");
				return;
			}

			if (!selectedSlot) {
				showStorageSelectionMessage("You have not selected a slot.");
				return;
			}

			if (selectedProducts.length === 0) {
				showStorageSelectionMessage("You have not selected any product.");
				return;
			}

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/create_storage.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error processing request.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	setupBackToMenuButton(
		'.back-to-slot-menu-btn', 
		['edit-slot-modal'], 
		'slot-menu-buttons', 
		'slot-options'
	);

	setupBackToMenuButton(
		'.back-to-storage-menu-btn', 
		['manage-slot-modal', 'manage-storage-modal'], 
		'storage-menu-buttons', 
		'storage-options'
	);
	//################################################################ END STORAGE ##################################################################

	//################################################################ CUSTOMERS #####################################################################
	const customerContainer = document.getElementById('customers-list');
	const searchCustomerField = document.getElementById('customersSearchField');

	if (customerContainer || searchCustomerField) {
		async function fetchAndRenderCustomers() {
			try {
				const searchTerm = searchCustomerField?.value.trim() || "";

				const params = new URLSearchParams();
				if (searchTerm) params.append('search', searchTerm);

				const res = await fetch(`api/get_customers.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await res.json();

				customerContainer.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(customer => {
						const row = document.createElement('div');
						row.className = 'customer-row';

						const profileImg = customer.image && customer.image.trim() !== ""
							? `images/customers/${customer.image}`
							: `images/sys-img/NonProfilePic.png`;

						row.innerHTML = `
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td width="5%" align="center" valign="middle">
										<div class="customers-profile">
											<img src="${profileImg}" alt="profile picture">
										</div>
									</td>
									<td width="25%" align="left" valign="middle">
										${customer.full_name}
									</td>
									<td width="15%" align="left" valign="middle">
										<p class="mini-title">${customer.document_type}:</p>
										${customer.document_no}
									</td>
									<td width="40%" align="left" valign="middle">
										<p class="mini-title">Address:</p>
										${customer.address}
									</td>
									<td width="10%" align="center" valign="middle">
										${customer.status}
									</td>
									<td width="5%" align="center" valign="middle">
										<div class="customers-menu">
											<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
										</div>
									</td>
								</tr>
							</table>
						`;

						customerContainer.appendChild(row);

						const customersMenuBtn = row.querySelector('.customers-menu');
						customersMenuBtn.addEventListener('click', () => {
							openCusomersForm(customer.customer_id);

							handlePopupClose("customers-options", ".formular-frame", []);
						});
					});
				} else {
					customerContainer.innerHTML = `
						<p class="isNotLinkedToCompany hidden" style="text-align: center; color: var(--warning-orange);">To activate this section you must complete the company details <a href="profile.php">here.</a></p>
						<p style="text-align:center;">No customers found.</p>
					`;
				}
			} catch (err) {
				console.error("Error loading customers:", err);
				customerContainer.innerHTML = `<p style="text-align:center;">Error loading customers</p>`;
			}
		}

		searchCustomerField?.addEventListener('keyup', fetchAndRenderCustomers);

		fetchAndRenderCustomers();
	}


	// 📌 script para add customers popup
	let addCustomerButton = document.getElementById('add-customers-button');
	if (addCustomerButton) {
		addCustomerButton.addEventListener('click', async function (e) {
			scrollToTopIfNeeded();
			
			const addCustomersForm = document.getElementById('add-customers-form');
			const popupContent = addCustomersForm.querySelector('.formular-frame');

			if (addCustomersForm && popupContent) {
			    addCustomersForm.style.display = 'block';
			    addCustomersForm.style.opacity = '0';
			    addCustomersForm.style.transition = 'opacity 0.5s ease';
			    setTimeout(() => {
			        addCustomersForm.style.opacity = '1';
			    }, 10);

			    popupContent.style.transform = 'scale(0.7)';
			    popupContent.style.opacity = '0';
			    popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			    setTimeout(() => {
			        popupContent.style.transform = 'scale(1)';
			        popupContent.style.opacity = '1';
			    }, 50);
			}

			initDragAndDrop('customer-drop-area', 'customer_image', 'customer-pic-preview');

			populateDocumentTypes('customer_document_type');

			populateCustomerTypes('customer_type', 1);

			await populateCountryPhoneCodes('customer_country_code', 'customer_phone');

			await populateCountryPhoneCodes('references_1_country_code', 'references_1_phone');

			await populateCountryPhoneCodes('references_2_country_code', 'references_2_phone');

			handlePopupClose("add-customers-form", ".formular-frame", []);
		});
	}

	// 📌 script para customers form menu
	const dataTab = document.getElementById('tab-customer-data');
	const referenceTab = document.getElementById('tab-customer-reference');

	const dataSection = document.getElementById('customer-data');
	const referenceSection = document.getElementById('customer-reference');

	// Mostrar por defecto la sección de "data"
	if (dataTab && referenceTab && dataSection && referenceSection) {
		activateTab(dataTab, referenceTab, dataSection, referenceSection);

		dataTab.addEventListener('click', () => {
			activateTab(dataTab, referenceTab, dataSection, referenceSection);
		});

		referenceTab.addEventListener('click', () => {
			activateTab(referenceTab, dataTab, referenceSection, dataSection);
		});
	}


	async function populateDocumentTypes(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Document Type';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch('api/get_global_array.php?key=documentTypes');
			const data = await res.json();
	
			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No document types found</option>`;
			}
		} catch (error) {
			console.error("Error loading document types:", error);
			select.innerHTML += `<option value="">Error loading document types</option>`;
		}
	}

	// 📌 Manejo del formulario para crear Customers
	const formAddCustomer = document.getElementById('formCustomers');
	if (formAddCustomer) {
		formAddCustomer.addEventListener('submit', async function (e) {
			e.preventDefault();
	
			const formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				const response = await fetch('api/create_customer.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});
	
				const data = await response.json();
	
				statusText.innerText = data.message || 'No message';
				statusImage.src = data.img_gif || '../images/sys-img/info.gif';
				showBanner(banner);
	
				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url || window.location.href;
						});
					}, 3000);
				}
			} catch (error) {
				console.error("Error submitting customer form:", error);
				statusText.innerText = "Error processing the request.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	async function openCusomersForm(customerId) {
		scrollToTopIfNeeded();
	
		const customersOptions = document.getElementById('customers-options');
		const popupContent = customersOptions.querySelector('.formular-frame');
		const customerName = document.getElementById('customers-name');
	
		if (!customerId) return;

		try {
			const res = await fetch(`api/get_customers.php?customer_id=${customerId}`);
			const data = await res.json();

			if (data.success && data.data.length > 0) {
				const customers = data.data.find(p => p.customer_id == customerId);
				if (customers && customerName) {
					customerName.textContent = customers.customer_name + ' ' + customers.customer_surname;
				}
			}

			if (customersOptions && popupContent) {
				resetPopupView(['customers-menu-buttons', 'sale-menu-buttons'], [
					'edit-customers-modal', 
					'assign-sale-section', 
					'edit-sales-modal'
				]);

				customersOptions.style.display = 'block';
				customersOptions.style.opacity = '0';
				customersOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					customersOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
		
				// Botón: Assign to sale
				const assignBtn = document.getElementById('assignCustomerSaleBtn');
				if (assignBtn) {
					assignBtn.onclick = () => {
						const menuDiv = document.getElementById('product-menu-buttons');
						const assignDiv = document.getElementById('assign-sale-section');
				
						animateHeightChange(popupContent, assignDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(assignDiv);
							});
						});
					};
				}
				
				// Botón: Edit Customer
				const editBtn = document.getElementById('editCustomerBtn');
				if (editBtn) {

					editBtn.setAttribute('data-customer-id', customerId);

					editBtn.onclick = () => {
						const menuDiv = document.getElementById('customers-menu-buttons');
						const editDiv = document.getElementById('edit-customers-modal');

						const customerId = editBtn.getAttribute('data-customer-id');
						if (!customerId) return;

						openEditCustomerForm(customerId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					}
				}

				// Botón: Delete Customer
				const deleteBtn = document.getElementById('deleteCustomerBtn');
				if (deleteBtn) {
					deleteBtn.onclick = () => {

						deleteBtn.setAttribute('data-customer-id', customerId);
						
						if (!customerId) {
							alert("Customer ID not found.");
							return;
						}

						showConfirmModal("Delete Customer", "Are you sure you want to delete this cusomer?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("customer_id", customerId);
				
							try {
								const response = await fetch('api/delete_customer.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											window.location.href = data.redirect_url || window.location.href;
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting product:", error);
								alert("Error deleting product. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading product info:", error);
		}
	}

	async function openEditCustomerForm(customerId) {
		const formEditCustomer = document.getElementById('formEditCustomer');
		if (!formEditCustomer) return;
	
		formEditCustomer.setAttribute('data-customer-id', customerId);
	
		try {
			const response = await fetch(`api/get_customers.php?customer_id=${customerId}`);
			const data = await response.json();
	
			if (data.success && data.data.length > 0) {
				const customer = data.data.find(c => c.customer_id == customerId);
				if (!customer) return;
	
				// Llenar campos del formulario
				document.getElementById('edit_customer_name').value = customer.customer_name || '';
				document.getElementById('edit_customer_surname').value = customer.customer_surname || '';
				document.getElementById('edit_customer_email').value = customer.customer_email || '';
				document.getElementById('edit_customer_address').value = customer.customer_address || '';
				document.getElementById('edit_customer_country_code').value = customer.cu_country_code || '';
				document.getElementById('edit_customer_phone').value = customer.customer_phone || '';
				document.getElementById('edit_customer_birthday').value = customer.customer_birthday ? customer.customer_birthday.split(" ")[0] : '';
				document.getElementById('edit_customer_document_no').value = customer.customer_document_no || '';
				document.getElementById('edit_references_1').value = customer.references_1 || '';
				document.getElementById('edit_references_1_country_code').value = customer.r1_country_code || '';
				document.getElementById('edit_references_1_phone').value = customer.references_1_phone || '';
				document.getElementById('edit_references_2').value = customer.references_2 || '';
				document.getElementById('edit_references_2_country_code').value = customer.r2_country_code || '';
				document.getElementById('edit_references_2_phone').value = customer.references_2_phone || '';
				document.getElementById("edit_customer_status").checked = customer.customer_status === "1" || customer.customer_status === 1;
	
				// Imagen de perfil
				const preview = document.getElementById('edit-customer-pic-preview');
				if (preview) {
					if (customer.customer_image && customer.customer_image.trim() !== '') {
						preview.src = `images/customers/${customer.customer_image}`;
						preview.style.display = 'block';
						preview.style.visibility = 'visible';
						preview.style.opacity = '1';
					} else {
						preview.src = '';
						preview.style.display = 'none';
					}
				}
	
				// Cargar select de tipo de documento, tipo de cliente y estatus
				await populateDocumentTypes('edit_customer_document_type', customer.customer_document_type);
				await populateCustomerTypes('edit_customer_type', customer.customer_type);
	
				// Inicializar drag and drop
				initDragAndDrop('edit-customer-drop-area', 'edit_customer_image', 'edit-customer-pic-preview');

				const selectedCuCcFromDB = customer.cu_country_code || '';
				await populateCountryPhoneCodes('edit_customer_country_code', 'edit_customer_phone', selectedCuCcFromDB);

				const selectedr1CcFromDB = customer.r1_country_code || '';
				await populateCountryPhoneCodes('edit_references_1_country_code', 'edit_references_1_phone', selectedr1CcFromDB);

				const selectedr2CcFromDB = customer.r1_country_code || '';
				await populateCountryPhoneCodes('edit_references_2_country_code', 'edit_references_2_phone', selectedr2CcFromDB);

				handlePopupClose("customers-options", ".formular-frame", []);
			}
		} catch (error) {
			console.error("Error loading customer data:", error);
		}
	}

	const formEditCustomer = document.getElementById('formEditCustomer');
	if (formEditCustomer) {
		formEditCustomer.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);
			const customerId = formEditCustomer.getAttribute('data-customer-id');
			formData.append('edit_customer_id', customerId);

			try {
				const response = await fetch('api/update_customer.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				const data = await response.json();

				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');

				if (banner && statusText && statusImage) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "images/sys-img/loading.gif";
					showBanner(banner);
				}

				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url || window.location.href;
						});
					}, 3000);
				}
			} catch (error) {
				console.error("Error updating customer:", error);
			}
		});
	}

	setupBackToMenuButton(
		'.edit-back-to-menu-btn',
		['assign-customers-sale-section', 'edit-customers-modal'],
		'customers-menu-buttons',
		'customers-options'
	);

	// 📌 script para edit customers form menu
	const editDataTab = document.getElementById('tab-edit-customer-data');
	const editReferenceTab = document.getElementById('tab-edit-customer-reference');

	const editDataSection = document.getElementById('edit-customer-data');
	const editReferenceSection = document.getElementById('edit-customer-reference');

	// Mostrar por defecto la sección de "edit data"
	if (editDataTab && editReferenceTab && editDataSection && editReferenceSection) {
		activateTab(editDataTab, editReferenceTab, editDataSection, editReferenceSection);

		editDataTab.addEventListener('click', () => {
			activateTab(editDataTab, editReferenceTab, editDataSection, editReferenceSection);
		});

		editReferenceTab.addEventListener('click', () => {
			activateTab(referenceTab, editDataTab, editReferenceSection, editDataSection);
		});
	}

	//############################################################# END CUSTOMERS ##################################################################

	//############################################################# SALES ##################################################################

	const salesContainer = document.getElementById('sales-list');
	const searchSalesField = document.getElementById('salesSearchField');

	if (salesContainer || searchSalesField) {
		async function fetchAndRenderSales() {
			try {
				const searchTerm = searchSalesField?.value.trim() || "";

				const params = new URLSearchParams();
				if (searchTerm) params.append('search', searchTerm);

				const res = await fetch(`api/get_sales.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				const data = await res.json();
				salesContainer.innerHTML = "";
				
				if (data.success && data.data.length > 0) {
					data.data.forEach(sale => {
						const row = document.createElement('div');
						row.className = 'sale-row';

						const customerImg = sale.customer.image?.trim() !== ''
							? `images/customers/${sale.customer.image}`
							: 'images/sys-img/NonProfilePic.png';

						const paymentDateFormatted = sale.payment_date
							? `Every ${new Date(sale.payment_date).getDate()}th`
							: '-';

						let productsHtml = '';
						if (!sale.products || sale.products.length === 0) {
							productsHtml = `
								<tr valign="baseline" class="form_height">
									<td width="100%" align="center" valign="middle">
										<p>No products in this sale...</p>
									</td>
								</tr>
							`;
						} else if (sale.products.length === 1) {
							const product = sale.products[0];

							let unitImg = '';
							if (product.sale_unit_type === "1" || product.sale_unit_type === null) {
								unitImg = "images/sys-img/papel-box.png";
							} else {
								unitImg = "images/sys-img/wooden-box.png";
							}

							let isDefaultImage = !product.image || product.image.trim() === "";
							const productImg = isDefaultImage
								? unitImg
								: `images/products/${product.image}`;

							let imageClass = isDefaultImage ? "grayscale-img" : "";
						
							productsHtml = `
								<tr valign="baseline" class="form_height">
									<td width="5%" align="center" valign="top">
										<p class="mini-title">Qty</p>
										${product.quantity}
									</td>
									<td width="25%" align="left" valign="middle">
										<div class="sale-product-pic">
											<img src="${productImg}" alt="product picture" class="${imageClass}" />
										</div>
									</td>
									<td width="80%" align="left" valign="middle">
										<p class="mini-title">Product No:</p>
										${product.name}
										<h3><strong>${product.mark_name} - ${product.model_name ? product.model_name : ''}</strong></h3>
										<p>${product.submodel_name ? product.submodel_name : ''}</p>
										<p class="mini-title">Year</p>
										<strong>${product.year}</strong>
									</td>
								</tr>
							`;
						} else {
							sale.products.forEach(product => {
								let unitImg = '';

								if (product.sale_unit_type === "1" || product.sale_unit_type === null) {
									unitImg = "images/sys-img/papel-box.png";
								} else {
									unitImg = "images/sys-img/wooden-box.png";
								}

								const isDefaultImage = !product.image || product.image.trim() === "";
								const productImg = isDefaultImage
									? unitImg
									: `images/products/${product.image}`;
						
								const imageClass = isDefaultImage ? "grayscale-img" : "";

								productsHtml += `
									<tr valign="baseline" class="form_height">
										<td width="3%" align="center" valign="middle">
											<p class="mini-title">Qty</p>
											${product.quantity}
										</td>
										<td width="15%" align="left" valign="middle">
											<div class="sale-list-product-pic">
												<img src="${productImg}" alt="product picture" class="${imageClass}" />
											</div>
										</td>
										<td width="40%" align="left" valign="middle">
											<h3 style="margin: 0; padding: 0;"><strong>${product.mark_name} - ${product.model_name ? product.model_name : ''}</strong></h3>
											<p style="margin: 0; padding: 0;">${product.submodel_name ? product.submodel_name : ''}</p>
										</td>
										<td width="10%" align="left" valign="middle">
											<p class="mini-title">Year</p>
											<strong>${product.year}</strong>
										</td>
										<td width="12%" align="left" valign="middle">
											<p class="mini-title">Price</p>
											<strong>${product.price}</strong>
										</td>
										<td width="20%" align="left" valign="middle">
											<p class="mini-title">Product No:</p>
											${product.name}
										</td>
									</tr>
								`;
							});
						}

						row.innerHTML = `
						<table width="100%" style="border-bottom: 1px solid var(--border-light);" align="center" cellspacing="0">
							<tr valign="baseline" class="form_height">
								<td width="10%" align="left" valign="middle">
									<p class="mini-title">Ord. No:</p>
									${sale.ord_no}
								</td>
								<td width="87%" align="center" valign="middle"></td>
								<td width="3%" align="center" valign="middle">
									<div class="sale-menu">
										<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
									</div>
								</td>
							</tr>
						</table>
						<div class="flex" style="width: 100%; margin-top: 5px;">
							<div style="width: 30%;">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline" class="form_height">
										<td width="30%" align="left" valign="middle">
											<div class="sale-profile">
												<img src="${customerImg}" alt="profile picture">
											</div>
										</td>
										<td width="70%" align="left" valign="middle">
											<h3><strong>${sale.customer.full_name}</strong></h3>
											<p class="mini-title">${sale.customer.document_type}:</p>
											${sale.customer.document_no}<br><br>
											<p class="mini-title">Phone:</p>
											${sale.customer.phone}
										</td>
									</tr>
								</table>
							</div>
							<table width="40%" style="border-left: 1px solid var(--border-light); border-right: 1px solid var(--border-light);" align="center" cellspacing="0">
								${productsHtml}
							</table>
							<div style="width: 30%;">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline" class="form_height">
										<td colspan="2" style="padding-left: 7px;" align="left" valign="middle"><strong>Method of Payment</strong></td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td width="35%" align="right">Price :</td><td width="65%" style="padding-left: 5px;">${sale.price_sum}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">Initial :</td><td style="padding-left: 5px;">${sale.initial}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">Delivery date :</td><td style="padding-left: 5px;">${sale.delivery_date}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">Remaining :</td><td style="padding-left: 5px;">${sale.remaining}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">Interest :</td><td style="padding-left: 5px;">${sale.total_interest}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">Installments / month :</td><td style="padding-left: 5px;">${sale.no_installments} / ${sale.payments}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">Payment date :</td><td style="padding-left: 5px;">${paymentDateFormatted}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">Due :</td><td style="padding-left: 5px;">${sale.due}</td>
									</tr>
								</table>
							</div>
						</div>`;

						salesContainer.appendChild(row);

						const salesMenuBtn = row.querySelector('.sale-menu');
						salesMenuBtn.addEventListener('click', () => {
							openEditSalesForm(sale.sales_id);

							handlePopupClose("sale-options", ".formular-frame", []);
						});
					});
				} else {
					salesContainer.innerHTML = `
						<p class="isNotLinkedToCompany hidden" style="text-align: center; color: var(--warning-orange);">To activate this section you must complete the company details <a href="profile.php">here.</a></p>
						<p style="text-align:center;">No sales found.</p>
					`;
				}
			} catch (err) {
				console.error("Error loading sales:", err);
				salesContainer.innerHTML = `<p style="text-align:center;">Error loading sales</p>`;
			}
		}

		searchSalesField?.addEventListener('keyup', fetchAndRenderSales);
		fetchAndRenderSales();
	}

	// 📌 script para add sale popup
	let addSaleBtn = document.getElementById('add-sale-btn');
	if (addSaleBtn) {
		addSaleBtn.addEventListener('click', function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const addSaleForm = document.getElementById('add-sale-form');
			const popupContent = addSaleForm.querySelector('.formular-big-frame');

			if (addSaleForm && popupContent) {
				addSaleForm.style.display = 'block';
				addSaleForm.style.opacity = '0';
				addSaleForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					addSaleForm.style.opacity = '1';
				}, 10);

				popupContent.style.transform = 'scale(0.7)';
				popupContent.style.opacity = '0';
				popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
			}

			populatePaymentTerms('installments_month');

			populateCurrencies('currency');

			handlePopupClose("add-sale-form", ".formular-big-frame", []);
		});
	}

	const searchCustomerInput = document.getElementById('search-customer');
	const customerListTable = document.getElementById('select-customers-list');

	if (searchCustomerInput && customerListTable) {
		async function fetchAndRenderCustomers(search = "") {
			try {
				const params = new URLSearchParams();
				if (search.trim() !== "") {
					params.append('search', search.trim());
				}

				const response = await fetch(`api/get_customers.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await response.json();
				customerListTable.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(customer => {
						const uniqueId = `customer-${customer.customer_id}`;
						const row = document.createElement('tr');
						row.className = "sales-customer-row";

						const profileImg = customer.image && customer.image.trim() !== ""
							? `images/customers/${customer.image}`
							: `images/sys-img/NonProfilePic.png`;

						row.innerHTML = `
							<td width="10%" align="center" valign="middle">
								<div class="customers-profile">
									<img src="${profileImg}" alt="">
								</div>
							</td>
							<td width="80%" valign="middle" style="padding-left:10px;">
								<strong>${customer.full_name}</strong>
								<p class="mini-title" style="color: #000;">${customer.document_type}: <strong>${customer.document_no}</strong></p>
							</td>
							<td width="10%" align="center" valign="middle">
								<div class="opcion-radio">
									<input type="radio" id="${uniqueId}" name="customer_select" class="category-radio" data-id="${customer.customer_id}" />
									<label for="${uniqueId}"></label>
								</div>
							</td>
						`;

						// 🟢 Seleccionar al hacer clic en toda la fila
						row.addEventListener('click', () => {
							const radio = row.querySelector('input[type="radio"]');
							if (!radio.disabled) {
								radio.checked = true;

								// Desmarcar visualmente otros clientes
								document.querySelectorAll('.sales-customer-row').forEach(r => r.classList.remove('selected-customer'));

								// Marcar visualmente este
								row.classList.add('selected-customer');

								// Simular evento de selección (por si tienes una función para manejarlo)
								if (typeof handleCustomerSelect === "function") {
									handleCustomerSelect({ target: radio });
								}
							}
						});
						customerListTable.appendChild(row);
					});
				} else {
					customerListTable.innerHTML = `
						<tr><td colspan="3" style="text-align:center; padding: 10px;">No customers found.</td></tr>
					`;
				}
			} catch (error) {
				console.error("Error loading customers:", error);
				customerListTable.innerHTML = `
					<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading customers</td></tr>
				`;
			}
		}

		searchCustomerInput.addEventListener('input', () => {
			fetchAndRenderCustomers(searchCustomerInput.value);
		});

		fetchAndRenderCustomers();
	}

	const searchProductInput = document.getElementById('search-product-purchase');
	const saleMarkSelect = document.getElementById('search-product-mark');
	const productListTable = document.getElementById('select-product-list');

	if ((searchProductInput || saleMarkSelect) && productListTable) {
		async function fetchAndRenderProducts(purpose = "", search = "", mark = "") {
			try {
				const params = new URLSearchParams();
				if (search.trim() !== "") {
					params.append('search', search.trim());
				}
				if (mark && mark !== "") {
					params.append('mark', mark);
				}
				if (purpose && purpose !== "") {
					params.append('purpose', purpose);
				}

				const response = await fetch(`api/get_products.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await response.json();
				productListTable.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(product => {
						let unitImg = '';

						if (product.sale_unit_type === "1" || product.sale_unit_type === null) {
							unitImg = "images/sys-img/papel-box.png";
						} else {
							unitImg = "images/sys-img/wooden-box.png";
						}

						const uniqueId = `product-${product.product_id}`;
						const productImg = product.product_image && product.product_image.trim() !== ''
							? `images/products/${product.product_image}`
							: unitImg;

						const row = document.createElement('tr');
						row.className = "sales-product-row";
						row.innerHTML = `
							<td width="10%" align="center" valign="middle">
								<div class="list-icon">
									<img src="${productImg}" alt="product image" width="32" height="32">
								</div>
							</td>
							<td width="75%" valign="middle" style="padding-left:10px;">
								${product.product_name}<br>
								<small>${product.mark_name || ''} - ${product.model_name || ''} ${product.submodel_name || ''}</small>
							</td>
							<td width="5%" align="left" valign="middle">
								<input type="number" id="qty-${uniqueId}" class="form-mini-input-style" value="1" min="1" disabled />
							</td>
							<td width="10%" align="center" valign="middle">
								<div class="opcion-checkbox">
									<input type="checkbox" id="${uniqueId}" name="product_selection[]" value="${product.product_id}" data-price="${product.price}" class="product-checkbox" />
									<label for="${uniqueId}"></label>
								</div>
							</td>
						`;
						productListTable.appendChild(row);

						const checkbox = document.getElementById(uniqueId);
						const quantityInput = document.getElementById(`qty-${uniqueId}`);
						const OutOfStock = product.quantity <= 0;

						if (OutOfStock) {
							checkbox.disabled = true;
							checkbox.checked = false;
							quantityInput.disabled = true;
							quantityInput.value = 0;
						} else {
							checkbox.addEventListener('change', function () {
								if (this.checked) {
									quantityInput.disabled = false;
									quantityInput.focus(); 
									calculatePriceSum();
								} else {
									quantityInput.disabled = true;
									quantityInput.value = 1;
									calculatePriceSum();
								}
							});
						}

						quantityInput.addEventListener('input', function () {
							if (parseInt(this.value) <= 0 || isNaN(parseInt(this.value))) {
								this.value = 1;
							}
							calculatePriceSum();
						});

						document.getElementById(uniqueId).addEventListener('change', calculatePriceSum);
					});
				} else {
					productListTable.innerHTML = `
						<tr><td colspan="3" style="text-align:center; padding: 10px;">No products found.</td></tr>
					`;
				}
			} catch (error) {
				console.error("Error loading products:", error);
				productListTable.innerHTML = `
					<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading products</td></tr>
				`;
			}
		}

		searchProductInput.addEventListener('input', () => {
			fetchAndRenderProducts('1', searchProductInput.value, saleMarkSelect.value);
		});
		saleMarkSelect.addEventListener('change', () => {
			fetchAndRenderProducts('1', searchProductInput.value, saleMarkSelect.value);
		});

		loadMarksForSearch(saleMarkSelect).then(() => fetchAndRenderProducts('1'));
	}

	function calculatePriceSum() {
		const checkboxes = document.querySelectorAll('.product-checkbox:checked');
		let total = 0;
	
		checkboxes.forEach(cb => {
			const price = parseFloat(cb.getAttribute('data-price')) || 0;
			const qtyInput = document.getElementById(`qty-${cb.id}`);
			const quantity = parseInt(qtyInput.value) || 1;
			total += price * quantity;
		});
	
		document.getElementById('price_sum').value = total.toFixed(2);

		calculateRemaining();
		calculateInterest();
	}

	function calculateRemaining() {
		const priceSum = parseFloat(document.getElementById('price_sum').value.replace(/,/g, '')) || 0;
		const initial = parseFloat(document.getElementById('initial').value.replace(/,/g, '')) || 0;
		const remaining = priceSum - initial;
	
		document.getElementById('remaining').value = remaining.toFixed(2);
		calculateDue();
	}

	function calculateInterest() {
		const priceSum = parseFloat(document.getElementById('remaining').value.replace(/,/g, '')) || 0;
		const interestPercent = parseFloat(document.getElementById('interest').value) || 0;
	
		const totalInterest = (priceSum * interestPercent) / 100;
		document.getElementById('total_interest').value = totalInterest.toFixed(2);
		calculateDue();
	}

	function calculateDue() {
		const remaining = parseFloat(document.getElementById('remaining').value.replace(/,/g, '')) || 0;
		const totalInterest = parseFloat(document.getElementById('total_interest').value.replace(/,/g, '')) || 0;
		const due = remaining + totalInterest;
	
		document.getElementById('due').value = due.toFixed(2);
	}

	const initialInput = document.getElementById('initial');
	if (initialInput) {
		initialInput.addEventListener('input', calculateRemaining);
	}

	const interestInput = document.getElementById('interest');
	if (interestInput) {
		interestInput.addEventListener('input', calculateInterest);
	}

	const formAddSale = document.querySelector('#formAddSale');
	if (formAddSale) {
		formAddSale.addEventListener('submit', function (e) {
			e.preventDefault();
			(async () => {
				try {
					const formatDecimal = val => parseFloat((val || '').toString().replace(',', '').trim()) || 0;

					const customerId = document.querySelector('input[name="customer_select"]:checked')?.dataset.id;
					const priceSum = formatDecimal(document.getElementById('price_sum').value);
					const initial = formatDecimal(document.getElementById('initial').value);
					const deliveryDate = document.getElementById('delivery_date').value;
					const remaining = formatDecimal(document.getElementById('remaining').value);
					const interest = parseInt(document.getElementById('interest').value) || 0;
					const installmentsMonth = parseInt(document.getElementById('installments_month').value) || 0;
					const noInstallments = installmentsMonth;
					const paymentDate = document.getElementById('payment_date').value;
					const due = formatDecimal(document.getElementById('due').value);
			
					// Validación mínima
					if (!customerId) throw new Error("Select a customer");
			
					const products = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => {
						const productId = cb.value;
						const price = parseFloat(cb.dataset.price) || 0;
			
						const quantityInput = document.getElementById(`qty-product-${productId}`);
						const quantity = parseInt(quantityInput?.value) || 1;

						return {
							product_id: productId,
							price: price,
							quantity: quantity,
							discount: 0,
							total: price * quantity
							// total: Math.max(0, (price - discount)) * quantity
						};
					});
			
					if (products.length === 0) throw new Error("Select at least one product");
			
					const payload = {
						customer_id: parseInt(customerId),
						price_sum: priceSum,
						initial: initial,
						delivery_date: deliveryDate,
						remaining: remaining,
						interest: interest,
						installments_month: installmentsMonth,
						no_installments: noInstallments,
						payment_date: paymentDate,
						due: due,
						products: products
					};
			
					const res = await fetch('api/create_sale.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify(payload)
					});
			
					const data = await res.json();

					const banner = document.getElementById('status-message');
					const statusText = document.getElementById('status-text');
					const statusImage = document.getElementById('status-image');

					if (banner && statusText && statusImage) {
						statusText.innerText = data.message || "Unknown response";
						statusImage.src = data.img_gif || "images/sys-img/success.gif";
						showBanner(banner);
					}
			
					if (data.success) {
						setTimeout(() => {
							hideBanner(banner, () => {
								window.location.href = data.redirect_url || window.location.href;
							});
						}, 3000);
					} else {
						alert("Failed: " + data.message);
					}
				} catch (error) {
					alert("Error: " + error.message);
				}
			})();
		});
	}

	async function openEditSalesForm(SaleId) {
		scrollToTopIfNeeded();
	
		const saleOptions = document.getElementById('sale-options');
		const popupContent = saleOptions.querySelector('.formular-frame');
		const ordNo = document.getElementById('ord-no');
	
		if (!SaleId) return;
	
		try {
			const res = await fetch(`api/get_sales.php?sale_id=${SaleId}`);
			const data = await res.json();
	
			if (data.success && data.data.length > 0) {
				const sale = data.data.find(s => s.sales_id == SaleId);
				if (sale && ordNo) {
					ordNo.textContent = `Order #${sale.ord_no} - ${sale.customer.full_name}`;
				}
			}
	
			if (saleOptions && popupContent) {
				resetPopupView(['customers-menu-buttons', 'sale-menu-buttons'], [
					'edit-customers-modal', 
					'assign-sale-section', 
					'edit-sales-modal'
				]);
	
				saleOptions.style.display = 'block';
				saleOptions.style.opacity = '0';
				saleOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					saleOptions.style.opacity = '1';
				}, 10);
	
				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
	

				// Botón: Edit Sale
				const editBtn = document.getElementById('editSaleBtn');
				if (editBtn) {
					editBtn.setAttribute('data-sale-id', SaleId);
					editBtn.onclick = () => {
						const menuDiv = document.getElementById('sale-menu-buttons');
						const editDiv = document.getElementById('edit-sales-modal');
						
						if (editDiv) {
							editDiv.style.display = 'none';
						}

						const saleId = editBtn.getAttribute('data-sale-id');
						if (!saleId) return;

						const formFrame = document.getElementById('formular-frame');
						if (formFrame) {
							formFrame.classList.add('expanded');
						}

						openEditSaleForm(SaleId);

						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					}
				}
	
				const deleteSaleBtn = document.getElementById('deleteSaleBtn'); 
				if (deleteSaleBtn) {
					deleteSaleBtn.setAttribute('data-sale-id', SaleId); 
					deleteSaleBtn.onclick = async () => {
						const saleId = deleteSaleBtn.getAttribute('data-sale-id');

						if (!saleId) {
							alert("Sale ID not found.");
							return;
						}

						showConfirmModal("Delete Sale", "Are you sure you want to delete this sale and all associated products?", async () => {
							const formData = new FormData();
							formData.append("sale_id", saleId);

							try {
								const response = await fetch('api/delete_sale.php', {
									method: 'POST',
									body: formData
								});

								const data = await response.json();

								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);

								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											window.location.href = data.redirect_url || window.location.href;
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting sale:", error);
								alert("Error deleting sale. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading sale info:", error);
			alert("Failed to load sale information.");
		}
	}

	async function populatePaymentTerms(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;

		select.innerHTML = '';

		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Payment Term';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_global_array.php?key=paymentTerms');
			const data = await res.json();

			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">Select Installments</option>`;
			}
		} catch (error) {
			console.error("Error loading payment terms:", error);
			select.innerHTML += `<option value="">Error loading payment terms</option>`;
		}
	}

	async function openEditSaleForm(saleId) {
		const formEditSale = document.getElementById('formEditSale');
		if (!formEditSale) return;

		formEditSale.setAttribute('data-sale-id', saleId);

		try {
			const response = await fetch(`api/get_sales.php?sale_id=${saleId}`);
			const data = await response.json();

			if (data.success && data.data.length > 0) {
				const sale = data.data.find(s => s.sales_id == saleId);
				if (!sale) return;
				
				// Inicializar selección de clientes
				const searchCustomerInput = document.getElementById('search-customer-for-edit');
				const customerListTable = document.getElementById('select-customers-list-for-edit');

				if (searchCustomerInput && customerListTable) {
					async function fetchAndRenderCustomersForEdit(search = '') {
						try {
							const params = new URLSearchParams();
							if (search.trim() !== '') {
								params.append('search', search.trim());
							}

							const response = await fetch(`api/get_customers.php?${params.toString()}`, {
								method: 'GET',
								headers: { 'Accept': 'application/json' }
							});
							const data = await response.json();
							customerListTable.innerHTML = '';

							if (data.success && data.data.length > 0) {
								data.data.forEach(customer => {
									const uniqueId = `edit-customer-${customer.customer_id}`;
									const profileImg = customer.image && customer.image.trim() !== '' ? `images/customers/${customer.image}` : `images/sys-img/NonProfilePic.png`;

									const row = document.createElement('tr');
									row.className = 'sales-customer-row';
									row.innerHTML = `
										<td width='10%' align='center' valign='middle'>
											<div class='customers-profile'>
												<img src='${profileImg}' alt=''>
											</div>
										</td>
										<td width='80%' valign='middle' style='padding-left:10px;'>
											<strong>${customer.full_name}</strong>
											<p class='mini-title' style='color: #000;'>${customer.document_type}: <strong>${customer.document_no}</strong></p>
										</td>
										<td width='10%' align='center' valign='middle'>
											<div class='opcion-radio'>
												<input type='radio' id='${uniqueId}' name='customer_select' class='category-radio' data-id='${customer.customer_id}' />
												<label for='${uniqueId}'></label>
											</div>
										</td>
									`;
									customerListTable.appendChild(row);

									if (String(customer.customer_id) === String(sale.customer.customer_id)) {
										const customerRadio = document.getElementById(uniqueId);
										if (customerRadio) customerRadio.checked = true;
									}
								});
							} else {
								customerListTable.innerHTML = `
									<tr><td colspan='3' style='text-align:center; padding: 10px;'>No customers found.</td></tr>
								`;
							}
						} catch (error) {
							console.error('Error loading customers:', error);
							customerListTable.innerHTML = `
								<tr><td colspan='3' style='text-align:center; padding: 10px;'>Error loading customers</td></tr>
							`;
						}
					}

					searchCustomerInput.addEventListener('input', () => {
						fetchAndRenderCustomersForEdit(searchCustomerInput.value);
					});

					await fetchAndRenderCustomersForEdit();
				}

				// Cargar productos seleccionados
				const searchProductInputForEdit = document.getElementById('search-product-purchase-for-edit');
				const saleMarkSelectForEdit = document.getElementById('search-product-mark-for-edit');
				const productListTableForEdit = document.getElementById('select-product-list-for-edit');

				if ((searchProductInputForEdit || saleMarkSelectForEdit) && productListTableForEdit) {
					async function fetchAndRenderProducts(search = "", mark = "") {
						try {
							const params = new URLSearchParams();
							if (search.trim() !== "") {
								params.append('search', search.trim());
							}
							if (mark && mark !== "") {
								params.append('mark', mark);
							}

							const response = await fetch(`api/get_products.php?${params.toString()}`, {
								method: 'GET',
								headers: { 'Accept': 'application/json' }
							});
							const data = await response.json();
							productListTableForEdit.innerHTML = "";

							if (data.success && data.data.length > 0) {
								data.data.forEach(product => {
									const uniqueId = `edit-product-${product.product_id}`;
									const productImg = product.product_image && product.product_image.trim() !== ''
										? `images/products/${product.product_image}`
										: `images/sys-img/wooden-box.png`;

									const row = document.createElement('tr');
									row.className = "sales-product-row";
									row.innerHTML = `
										<td width="10%" align="center" valign="middle">
											<div class="list-icon">
												<img src="${productImg}" alt="product image" width="32" height="32">
											</div>
										</td>
										<td width="75%" valign="middle" style="padding-left:10px;">
											${product.product_name}<br>
											<small>${product.mark_name || ''} - ${product.model_name || ''} ${product.submodel_name || ''}</small>
										</td>
										<td width="5%" align="left" valign="middle">
											<input type="number" id="qty-${uniqueId}" class="form-mini-input-style" value="1" min="1" disabled />
										</td>
										<td width="10%" align="center" valign="middle">
											<div class="opcion-checkbox">
												<input type="checkbox" id="${uniqueId}" name="product_selection[]" value="${product.product_id}" data-price="${product.price}" class="edit-product-checkbox" />
												<label for="${uniqueId}"></label>
											</div>
										</td>
									`;
									productListTableForEdit.appendChild(row);

									const checkbox = document.getElementById(uniqueId);
									const quantityInput = document.getElementById(`qty-${uniqueId}`);
									const selectedProduct = sale.products.find(p => p.product_id === product.product_id);
									const OutOfStock = product.quantity <= 0;

									if (sale.products.some(p => p.product_id === product.product_id)) {
										checkbox.checked = true;
										quantityInput.disabled = false;
										quantityInput.value = selectedProduct.quantity;
									}

									if (OutOfStock) {
										if (checkbox.checked) {
											checkbox.addEventListener('change', function () {
												if (!this.checked) {
													quantityInput.disabled = true;
													quantityInput.value = 0;
													checkbox.disabled = true;
													editCalculatePriceSum();
												}
											});
										} else {
											checkbox.disabled = true;
											quantityInput.disabled = true;
											quantityInput.value = 0;
										}
									} else {
										checkbox.addEventListener('change', function () {
											if (this.checked) {
												quantityInput.disabled = false;
												quantityInput.focus();
												editCalculatePriceSum();
											} else {
												quantityInput.disabled = true;
												quantityInput.value = 1;
												editCalculatePriceSum();
											}
										});
									}

									quantityInput.addEventListener('input', function () {
										if (parseInt(this.value) <= 0 || isNaN(parseInt(this.value))) {
											this.value = 1;
										}
										editCalculatePriceSum();
									});

									document.getElementById(uniqueId).addEventListener('change', editCalculatePriceSum);
								});
							} else {
								productListTableForEdit.innerHTML = `
									<tr><td colspan="3" style="text-align:center; padding: 10px;">No products found.</td></tr>
								`;
							}
						} catch (error) {
							console.error("Error loading products:", error);
							productListTableForEdit.innerHTML = `
								<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading products</td></tr>
							`;
						}
					}

					searchProductInputForEdit.addEventListener('input', () => {
						fetchAndRenderProducts(searchProductInputForEdit.value, saleMarkSelectForEdit.value);
					});
					saleMarkSelectForEdit.addEventListener('change', () => {
						fetchAndRenderProducts(searchProductInputForEdit.value, saleMarkSelectForEdit.value);
					});

					loadMarksForSearch(saleMarkSelectForEdit).then(() => fetchAndRenderProducts());
					// fetchAndRenderProducts();
				}

				// Llenar campos del formulario
				function editCalculatePriceSum() {
					const checkboxes = document.querySelectorAll('.edit-product-checkbox:checked');
					let total = 0;
				
					checkboxes.forEach(cb => {
						const price = parseFloat(cb.getAttribute('data-price')) || 0;
						const qtyInput = document.getElementById(`qty-${cb.id}`);
						const quantity = parseInt(qtyInput.value) || 1;
						total += price * quantity;
					});
				
					document.getElementById('edit_price_sum').value = total.toFixed(2);

					editCalculateRemaining();
					editCalculateInterest();
				}

				function editCalculateRemaining() {
					const priceSum = parseFloat(document.getElementById('edit_price_sum').value.replace(/,/g, '')) || 0;
					const initial = parseFloat(document.getElementById('edit_initial').value.replace(/,/g, '')) || 0;
					const remaining = priceSum - initial;
				
					document.getElementById('edit_remaining').value = remaining.toFixed(2);
					editCalculateDue();
				}

				function editCalculateInterest() {
					const priceSum = parseFloat(document.getElementById('edit_remaining').value.replace(/,/g, '')) || 0;
					const interestPercent = parseFloat(document.getElementById('edit_interest').value) || 0;
				
					const totalInterest = (priceSum * interestPercent) / 100;
					document.getElementById('edit_total_interest').value = totalInterest.toFixed(2);
					editCalculateDue();
				}

				function editCalculateDue() {
					const remaining = parseFloat(document.getElementById('edit_remaining').value.replace(/,/g, '')) || 0;
					const totalInterest = parseFloat(document.getElementById('edit_total_interest').value.replace(/,/g, '')) || 0;
					const due = remaining + totalInterest;
				
					document.getElementById('edit_due').value = due.toFixed(2);
				}

				const initialInput = document.getElementById('edit_initial');
				if (initialInput) {
					initialInput.addEventListener('input', editCalculateRemaining);
				}

				const interestInput = document.getElementById('edit_interest');
				if (interestInput) {
					interestInput.addEventListener('input', editCalculateInterest);
				}

				document.getElementById('edit_price_sum').value = sale.price_sum || '';
				document.getElementById('edit_initial').value = sale.initial || '';
				document.getElementById('edit_delivery_date').value = sale.delivery_date || '';
				document.getElementById('edit_remaining').value = sale.remaining || '';
				document.getElementById('edit_interest').value = sale.interest || '';
				document.getElementById('edit_total_interest').value = sale.total_interest || '';
				document.getElementById('edit_due').value = sale.due || '';
				document.getElementById('edit_payment_date').value = sale.payment_date || '';

				// Cargar el select de cuotas
				await populatePaymentTerms('edit_installments_month', sale.installments_month);

				handlePopupClose("sale-options", ".formular-frame", []);
			}
		} catch (error) {
			console.error("Error loading sale data:", error);
		}
	}
 
	const formEditSale = document.getElementById('formEditSale');
	if (formEditSale) {
		formEditSale.addEventListener('submit', async function (e) {
			e.preventDefault();

			try {
				const customerId = document.querySelector('input[name="customer_select"]:checked')?.dataset.id;
				if (!customerId) throw new Error("Select a customer");

				const saleId = parseInt(formEditSale.getAttribute('data-sale-id'));

				const formData = new FormData();
				formData.append('sale_id', saleId);
				formData.append('customer_id', customerId);

				const fields = ['edit_price_sum', 'edit_initial', 'edit_delivery_date', 'edit_remaining', 
								'edit_interest', 'edit_installments_month', 'edit_payment_date', 'edit_due'];

				fields.forEach(field => {
					const value = document.getElementById(field).value;
					formData.append(field, value);
				});

				const products = Array.from(document.querySelectorAll('.edit-product-checkbox:checked')).map(cb => {
					const productId = cb.value;
					const quantity = document.getElementById(`qty-${cb.id}`).value;
					const price = parseFloat(cb.dataset.price) || 0;
					const total = price * quantity;

					return {
						product_id: parseInt(productId),
						price: price,
						quantity: parseInt(quantity),
						discount: 0,
						total: price * quantity
						// total: Math.max(0, (price - discount)) * quantity
					};
				});

				formData.append('products', JSON.stringify(products));

				const response = await fetch('api/update_sale.php', {
					method: 'POST',
					body: formData
				});

				const data = await response.json();

				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');

				if (banner && statusText && statusImage) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "images/sys-img/success.gif";
					showBanner(banner);
				}

				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url || window.location.href;
						});
					}, 3000);
				} else {
					alert("Failed: " + data.message);
				}
			} catch (error) {
				alert("Error: " + error.message);
			}
		});
	}

	setupBackToMenuButton(
		'.back-to-sale-menu-btn', 
		['edit-sales-modal', 'sale-2'], 
		'sale-menu-buttons', 
		'sale-options'
	);

	//############################################################# END SALES ##################################################################

	//############################################################# PAYMENTS ##################################################################

	const paymentsContainer = document.getElementById('payments-list');
	const searchPaymentField = document.getElementById('paymentsSearchField');

	if (paymentsContainer || searchPaymentField) {
		async function fetchAndRenderPayments() {
			try {
				const searchTerm = searchPaymentField?.value.trim() || "";

				const params = new URLSearchParams();
				if (searchTerm) params.append('search', searchTerm);

				const res = await fetch(`api/get_payments.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await res.json();
				
				paymentsContainer.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(payment => {
						const row = document.createElement('div');
						row.className = 'payment-row';

						row.innerHTML = `
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td width="8%" align="center" valign="middle">
										<p class="mini-title">Payment no:</p>
										${payment.payment_no || ''}
									</td>
									<td width="8%" align="center" valign="middle">
										<p class="mini-title">Ord no:</p>
										${payment.ord_no || ''}
									</td>
									<td width="13%" align="left" valign="middle" style="padding-left:2%;">
										<p class="mini-title">Name:</p>
										${payment.full_name || ''}
									</td>
									<td width="10%" align="center" valign="middle">
										<p class="mini-title">${payment.document_type}:</p>
										${payment.document_no || ''}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">Payment method:</p>
										${payment.payment_method || ''}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">Amount:</p>
										${parseFloat(payment.amount).toFixed(2)}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">Interest:</p>
										- ${parseFloat(payment.interest).toFixed(2)}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">Due:</p>
										${parseFloat(payment.due).toFixed(2)}
									</td>
									<td width="10%" align="center" valign="middle">
										<p class="mini-title">Payment Date:</p>
										${payment.payment_date || ''}
									</td>
									<td width="5%" align="center" valign="middle">
										<div class="payments-menu">
											<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
										</div>
									</td>
								</tr>
							</table>
						`;

						paymentsContainer.appendChild(row);

						const paymentsMenuBtn = row.querySelector('.payments-menu');
						paymentsMenuBtn.addEventListener('click', () => {
							openPaymentsForm(payment.payment_id);

							handlePopupClose("payments-options", ".formular-frame", []);
						});
					});
				} else {
					paymentsContainer.innerHTML = `
						<p class="isNotLinkedToCompany hidden" style="text-align: center; color: var(--warning-orange);">To activate this section you must complete the company details <a href="profile.php">here.</a></p>
						<p style="text-align:center;">No payments found.</p>
					`;
				}
			} catch (err) {
				console.error("Error loading payments:", err);
				paymentsContainer.innerHTML = `<p style="text-align:center;">Error loading payments</p>`;
			}
		}

		searchPaymentField?.addEventListener('keyup', fetchAndRenderPayments);

		fetchAndRenderPayments();
	}

	async function openPaymentsForm(paymentId) {
		scrollToTopIfNeeded();
		
		const paymentsOptions = document.getElementById('payments-options');
		const popupContent = paymentsOptions.querySelector('.formular-frame');
		const ordNoName = document.getElementById('ord-no-name');
	
		if (!paymentId) return;

		try {
			const res = await fetch(`api/get_payments.php?payment_id=${paymentId}`);
			const data = await res.json();

			if (data.success && data.data.length > 0) {
				const payment = data.data.find(p => p.payment_id == paymentId);
				if (payment && ordNoName) {
					ordNoName.textContent = payment.payment_no + ' - ' + payment.full_name;
				}
			}

			if (paymentsOptions && popupContent) {
				resetPopupView(['customers-menu-buttons', 'sale-menu-buttons'], [
					'edit-customers-modal', 
					'assign-sale-section', 
					'edit-sales-modal'
				]);

				paymentsOptions.style.display = 'block';
				paymentsOptions.style.opacity = '0';
				paymentsOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					paymentsOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
				
				// Botón: Edit Customer
				const editBtn = document.getElementById('editPaymentBtn');
				if (editBtn) {

					editBtn.setAttribute('data-customer-id', paymentId);

					editBtn.onclick = () => {
						console.log("Edit Payment button clicked");
						// const menuDiv = document.getElementById('payments-menu-buttons');
						// const editDiv = document.getElementById('edit-payments-modal');

						// const paymentId = editBtn.getAttribute('data-payment-id');
						// if (!paymentId) return;

						// openEditCustomerForm(paymentId);
			
						// animateHeightChange(popupContent, editDiv, () => {
						// 	fadeOutAndHide(menuDiv, () => {
						// 		showWithFadeIn(editDiv);
						// 	});
						// });
					}
				}

				// Botón: Delete Payment
				const deleteBtn = document.getElementById('deletePaymentBtn');
				if (deleteBtn) {
					deleteBtn.onclick = () => {

						deleteBtn.setAttribute('data-payment-id', paymentId);
						
						if (!paymentId) {
							alert("Payment ID not found.");
							return;
						}

						showConfirmModal("Delete Payment", "Are you sure you want to delete this Payment?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("payment_id", paymentId);
				
							try {
								const response = await fetch('api/delete_payment.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											window.location.href = data.redirect_url || window.location.href;
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting product:", error);
								alert("Error deleting product. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading product info:", error);
		}
	}

	// 📌 script para add payments popup
	let addPaymentsButton = document.getElementById('add-payments-btn');
	if (addPaymentsButton) {
		addPaymentsButton.addEventListener('click', async function (e) {
			scrollToTopIfNeeded();
			
			const addPaymentForm = document.getElementById('add-payment-form');
			const popupContent = addPaymentForm.querySelector('.formular-frame');

			if (addPaymentForm && popupContent) {
			    addPaymentForm.style.display = 'block';
			    addPaymentForm.style.opacity = '0';
			    addPaymentForm.style.transition = 'opacity 0.5s ease';
			    setTimeout(() => {
			        addPaymentForm.style.opacity = '1';
			    }, 10);

			    popupContent.style.transform = 'scale(0.7)';
			    popupContent.style.opacity = '0';
			    popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			    setTimeout(() => {
			        popupContent.style.transform = 'scale(1)';
			        popupContent.style.opacity = '1';
			    }, 50);
			}

			populateCurrencies('currency');

			populatePaymentMethods('payment_method');

			populateDocumentTypes('payer_document_type');

			handlePopupClose("add-payment-form", ".formular-frame", []);
		});
	}

	const ordNoInput = document.getElementById('ord_no');
	const amountInput = document.getElementById('amount');
	const payInterestInput = document.getElementById('interest');
	let currentOrderInterest = 0;
	if (ordNoInput && amountInput) {
		ordNoInput.addEventListener('input', async () => {
			const ordNo = ordNoInput.value.trim();
			if (!ordNo || isNaN(ordNo)) return;

			try {
				const res = await fetch(`api/get_order_info.php?ord_no=${ordNo}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await res.json();
				
				if (data.success && data.data) {
					const order = data.data;

					currentOrderInterest = parseFloat(order.interest) || 0;

					// 🔁 Llena los campos del formulario con los datos encontrados
					document.getElementById('customer').value = order.customer_name || '';
					document.getElementById('payer_document_no').value = order.document_no || '';
					document.getElementById('payer_phone').value = order.phone || '';
					document.getElementById('customer_email').value = order.email || '';

					// Selecciona en los <select> si hay valores
					if (order.currency) {
						document.getElementById('currency').value = order.currency;
					}
					if (order.payment_method) {
						document.getElementById('payment_method').value = order.payment_method;
					}
					if (order.document_type) {
						document.getElementById('payer_document_type').value = order.document_type;
					}

					if (amountInput.value && !isNaN(amountInput.value)) {
						const amount = parseFloat(amountInput.value);
						const interestAmount = amount * currentOrderInterest / 100;
						payInterestInput.value = interestAmount.toFixed(2);
					}
				}
			} catch (error) {
				console.error("Error loading order data:", error);
			}
		});

		amountInput.addEventListener('input', () => {
			const amount = parseFloat(amountInput.value);
			if (isNaN(amount) || currentOrderInterest <= 0) {
				payInterestInput.value = '';
				return;
			}
			const interestAmount = amount * currentOrderInterest / 100;
			payInterestInput.value = interestAmount.toFixed(2);
		});
	}

	const ordSuggestions = document.getElementById('ord-no-suggestions');
	if (ordNoInput && ordSuggestions) {
		let debounceTimeout;

		ordNoInput.addEventListener('input', () => {
			clearTimeout(debounceTimeout);
			const search = ordNoInput.value.trim();

			if (search.length === 0) {
				ordSuggestions.style.display = 'none';
				return;
			}

			debounceTimeout = setTimeout(async () => {
				try {
					const res = await fetch(`api/get_ordnos.php?search=${encodeURIComponent(search)}`);
					const data = await res.json();

					ordSuggestions.innerHTML = '';
					if (data.success && data.data.length > 0) {
						data.data.forEach(sale => {
							const item = document.createElement('div');
							item.textContent = `${sale.ord_no} - ${sale.full_name}`;
							item.addEventListener('click', () => {
								ordNoInput.value = sale.ord_no;
								ordNoInput.dispatchEvent(new Event('input'));
								ordSuggestions.style.display = 'none';
							});
							ordSuggestions.appendChild(item);
						});
						ordSuggestions.style.display = 'block';
					} else {
						ordSuggestions.style.display = 'none';
					}
				} catch (err) {
					console.error("Error fetching order suggestions:", err);
				}
			}, 300);
		});

		// Ocultar sugerencias si se hace clic fuera
		document.addEventListener('click', (e) => {
			if (!ordSuggestions.contains(e.target) && e.target !== ordNoInput) {
				ordSuggestions.style.display = 'none';
			}
		});
	}

	const formAddPayment = document.getElementById('formAddPayment');
	if (formAddPayment) {
		formAddPayment.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);

			// Asegurarse de que el campo "interest" deshabilitado también se incluya
			const interestInput = document.getElementById('interest');
			if (interestInput && interestInput.disabled) {
				formData.append('interest', interestInput.value || '0');
			}

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				const response = await fetch('api/create_payment.php', {
					method: 'POST',
					headers: {
						Accept: 'application/json'
					},
					body: formData
				});

				const data = await response.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif || "images/sys-img/success.gif";
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url || window.location.href;
						});
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif || "images/sys-img/error.gif";
					showBanner(banner);
				}
			} catch (error) {
				console.error("Request failed:", error);
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = "images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}
	//############################################################# END PAYMENTS ##################################################################

	//############################################################### SHIPPING ####################################################################
	const shippingTable = document.getElementById('shippingTable');
	const shippingDetails = document.getElementById('shippingDetails');
	const searchShippingField = document.getElementById('searchShippingField');
	const shippingSummary = document.getElementById('shippingSummary');

	if (shippingTable && searchShippingField) {
		async function fetchAndRenderShippings() {
			try {
				const searchTerm = (searchShippingField?.value || '').trim().toLowerCase();
				const params = new URLSearchParams();
				if (searchTerm) params.append('search', searchTerm);

				const res = await fetch(`api/get_shippings.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await res.json();

				if (data.success) {
					renderShippingsTable(data.data);
				} else {
					shippingTable.innerHTML = `<tr><td><p style="text-align:center;">No shippings found.</p></td></tr>`;
				}
			} catch (err) {
				console.error("Error loading shippings:", err);
				shippingTable.innerHTML = `<tr><td><p style="text-align:center;">Error loading shippings</p></td></tr>`;
			}
		}

		// Inicializar búsqueda
		fetchAndRenderShippings();
		searchShippingField.addEventListener('keyup', fetchAndRenderShippings);
	}

	// 🔹 Función para renderizar la tabla de shippings (reutilizable)
	function renderShippingsTable(shippings, selectedId = null) {
		shippingTable.innerHTML = '';
		shippingDetails.innerHTML = '';

		if (!Array.isArray(shippings) || shippings.length === 0) {
			shippingTable.innerHTML = `<tr><td><p style="text-align:center;">No shippings found.</p></td></tr>`;
			return;
		}

		shippings.forEach(shipping => {
			const shippingMethod = shipping.shipping_method === '2'
				? '<img src="images/sys-img/air-shipping.png" alt="Air Shipping">'
				: '<img src="images/sys-img/gnd-shipping.png" alt="Ground Shipping">';

			let statusColor = '';
			switch (parseInt(shipping.status)) {
				case 0: statusColor = 'red'; break;
				case 1: statusColor = 'orange'; break;
				case 2: statusColor = 'green'; break;
				case 3: statusColor = 'deepskyblue'; break;
				default: statusColor = 'gray'; break;
			}

			const shippingTracking = shipping.tracking?.checkpoint_name || '';

			const row = document.createElement('tr');
			row.className = 'clickable-row';
			row.setAttribute('data-id', shipping.shippings_id);
			row.innerHTML = `
				<td width="20%" align="center" valign="middle">
					<div class="shipping-profile">${shippingMethod}</div>
				</td>
				<td width="65%" align="left" valign="top">
					<div style="padding: 0 5px;">
						Shipping No.: <strong>${shipping.shipping_no || '—'}</strong><br>
						<p>Status: <strong style="color:${statusColor};">${shipping.status_text || ''}</strong></p>
						<p class="mini-title">${shippingTracking}</p>
					</div>
				</td>
				<td width="15%" align="left" valign="top">
					${formatNotificationDate(shipping.created_at)}
				</td>
			`;

			row.addEventListener('click', () => {
				localStorage.setItem("selectedShippingId", shipping.shippings_id);
				renderShippingDetails(shipping, row)
			});
			shippingTable.appendChild(row);

			// 🧠 Si hay un shipping seleccionado, mostrarlo automáticamente
			if (String(shipping.shippings_id) === String(selectedId)) {
				renderShippingDetails(shipping, row);
				row.style.backgroundColor = 'var(--gray-300)';
			}
		});
	}
		
	async function renderShippingDetails(shipping, clickedRow) {
		const allRows = shippingTable.querySelectorAll('.clickable-row');
		allRows.forEach(row => row.style.backgroundColor = '');

		if (clickedRow) {
			clickedRow.style.backgroundColor = 'var(--gray-300)';
		}

		const shippingTracking = shipping.tracking?.checkpoint_name || "";
		const hasTracking = shippingTracking.trim() !== "";

		shippingDetails.innerHTML = `
			<div class="shipping-header">
				<table width="100%" align="center" cellspacing="0">
					<tr valign="baseline" class="form_height">
						<td width="47%" align="left" valign="middle">
							<p class="mini-title">Shipping No.:</p>
							<strong>${shipping.shipping_no}</strong>
						</td>
						<td width="50%" align="center" valign="middle"></td>
						<td width="3%" align="center" valign="middle">
							<div class="shipping-menu" id="shippingMenuBtn">
								<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
							</div>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="47%" align="left" valign="middle">
							<p class="mini-title">Destination:</p>
							${shipping.destination || '—'}
						</td>
						<td width="50%" align="left" valign="middle">
							<p class="mini-title">Tracking Status:</p>
							${hasTracking 
								? `<span class="tracking-btn" id="openTrackingBtn">${shippingTracking}</span>` 
								: `<span>${shippingTracking || "No tracking available yet"}</span>`
							}
						</td>
						<td width="3%" align="center" valign="middle"></td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="left" valign="middle">
							<p class="mini-title">Description:</p>
							${shipping.description || '—'}
						</td>
					</tr>
				</table>
			</div>
			<div class="loads-list">
				${renderLoads(shipping.loads || [])}
			</div>
		`;

		shippingSummary.innerHTML = renderShippingSummary(shipping.product_summary || [], shipping);

		const shippingMenuBtn = document.getElementById('shippingMenuBtn');
		if (shippingMenuBtn) {
			shippingMenuBtn.addEventListener('click', () => {
				openShippingForm(shipping.shippings_id);

				handlePopupClose("shipping-options", ".formular-frame", []);
			});
		}

		const loadCards = document.querySelectorAll('.loads');
		loadCards.forEach((card, index) => {
			const loadMenuBtn = card.querySelector('.load-menu');
			if (!loadMenuBtn) return;

			loadMenuBtn.addEventListener('click', () => {
				const loadData = shipping.loads[index];
				if (!loadData) return;

				openLoadForm(loadData.load_id);

				handlePopupClose("load-options", ".formular-frame", []);
			});
		});

		const openTrackingBtn = document.getElementById('openTrackingBtn');
		if (openTrackingBtn) {
			openTrackingBtn.addEventListener('click', () => {
				scrollToTopIfNeeded();
				
				openTrackingInfo(shipping.shippings_id);

				const trackingInfo = document.getElementById('tracking-info');
				const popupContent = trackingInfo.querySelector('.formular-frame');

				if (trackingInfo && popupContent) {
					trackingInfo.style.display = 'block';
					trackingInfo.style.opacity = '0';
					trackingInfo.style.transition = 'opacity 0.5s ease';
					setTimeout(() => {
						trackingInfo.style.opacity = '1';
					}, 10);

					popupContent.style.transform = 'scale(0.7)';
					popupContent.style.opacity = '0';
					popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
					setTimeout(() => {
						popupContent.style.transform = 'scale(1)';
						popupContent.style.opacity = '1';
					}, 50);
				}

				handlePopupClose("tracking-info", ".formular-frame", []);
			});
		}
	}

	// 🔁 Función para refrescar el shipping seleccionado después de editar/agregar
	async function refreshSelectedShipping() {
		try {
			const selectedShippingId = localStorage.getItem("selectedShippingId");
			if (!selectedShippingId) return;

			const res = await fetch(`api/get_shippings.php`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();

			if (data.success) {
				renderShippingsTable(data.data, selectedShippingId);

				const shipping = data.data.find(s => String(s.shippings_id) === String(selectedShippingId));
				if (shipping) {
					const row = document.querySelector(`.clickable-row[data-id="${selectedShippingId}"]`);
					renderShippingDetails(shipping, row);
				}
			}
		} catch (err) {
			console.error("Error refreshing selected shipping:", err);
		}
	}
		
	function renderLoads(loads) {
		if (loads.length === 0) return '<p style="margin-left: 10px;">No loads found.</p>';

		return loads.map(load => `
			<div class="loads">
				<h4>Load No.: ${load.load_no}</h4>
				<p>Customer: <strong>${load.customer?.full_name || '—'}</strong></p>
				<p class="mini-title">
					${load.price_total || 0} ${load.from_currency || ''} 
					(Inc. ${(Number(load.taxes) ?? 0).toFixed(1)}% Taxes
					${load.discount && Number(load.discount) > 0 
						? ` & ${(Number(load.discount)).toFixed(1)} ${load.from_currency || ''} Disc.` 
						: ''})
				</p>
				<p style="margin-top:-4px;"><strong>${load.price_total_exchanged || 0} ${load.to_currency || ''}</strong></p>
				<div style="margin-top: 10px;">
					${renderProducts(load.products || [])}
				</div>
				<div class="load-menu">
					<img src="images/sys-img/menu-icon.png" alt="load-menu">
				</div>
			</div>
			
		`).join('');
	}

	function renderProducts(products) {
		if (products.length === 0) return '<p>No products</p>';

		return `
			<table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 10px;">
				<thead>
					<tr>
						<th style="border-bottom: 1px solid var(--clr-light-border); padding-bottom: 5px;" align="left">Product</th>
						<th style="border-bottom: 1px solid var(--clr-light-border); padding-bottom: 5px;" align="center">Qty</th>
						<th style="border-bottom: 1px solid var(--clr-light-border); padding-bottom: 5px;" align="center">Weight/Unit</th>
						<th style="border-bottom: 1px solid var(--clr-light-border); padding-bottom: 5px;" align="center">Weight</th>
						<th style="border-bottom: 1px solid var(--clr-light-border); padding-bottom: 5px;" align="center">Price/Kg</th>
					</tr>
				</thead>
				<tbody style="font-size: 11px; color: var(--clr-neutral-dark);">
					${products.map(p =>`
						<tr>
							<td style="padding-top: 7px;">${p.name || ''} <br><small>${p.mark_name || ''}${p.model_name ? ' - ' + p.model_name : ''}</small></td>
							<td style="padding-top: 7px;" align="center">${p.quantity ?? 0}</td>
							<td style="padding-top: 7px;" align="center">${(p.weight_per_unit ?? 0).toFixed(2)} kg</td>
							<td style="padding-top: 7px;" align="center">${(p.total_kg ?? 0).toFixed(2)} kg</td>
							<td style="padding-top: 7px;" align="center">$${(p.total_price_exchanged ?? 0).toFixed(2)}</td>
						</tr>
					`).join('')}
				</tbody>
			</table>
		`;
	}

	function renderShippingSummary(summary, shippingInfo = {}) {
		if (!Array.isArray(summary) || summary.length === 0) {
			return `<p style="text-align: center; margin-top: 10px;">No product summary available</p>`;
		}

		// 🧮 Totales generales
		const totalQty = summary.reduce((sum, p) => sum + (p.quantity ?? 0), 0);
		const totalOriginal = summary.reduce((sum, p) => sum + (p.total_price ?? 0), 0);
		const totalConverted = summary.reduce((sum, p) => sum + (p.total_exchanged ?? 0), 0);
		const totalWeight = summary.reduce((sum, p) => sum + (p.total_weight ?? 0), 0);

		const shippingNumber = shippingInfo.shipping_no || '—';
		const destination = shippingInfo.destination || '—';
		const createdAt = shippingInfo.created_at ? new Date(shippingInfo.created_at).toLocaleDateString() : '—';
		const deliveryDate = shippingInfo.delivery_date || '—';
		const shippingImage = shippingInfo.shipping_img 
			? `<img src="../images/shippings-code/${shippingInfo.shipping_img}" alt="Shipping Code" style="width: 50%; margin-top: 10px;">` 
			: '';

		return `
			<h3 style="text-align: center; margin: 10px 0;">Summary</h3>

			<table width="90%" cellspacing="0" cellpadding="5" style="margin: 0 auto;">
				<thead>
					<tr style="background: #f5f5f5;">
						<th style="border-bottom: 1px solid #ccc;" align="left">Product</th>
						<th style="border-bottom: 1px solid #ccc;" align="center">Qty</th>
						<th style="border-bottom: 1px solid #ccc;" align="center">Weight</th>
						<th style="border-bottom: 1px solid #ccc;" align="center">Total $</th>
					</tr>
				</thead>
				<tbody style="font-size: 11px; color: var(--clr-neutral-dark);">
					${summary.map(p => `
						<tr>
							<td>${p.name || ''} <br><small>${p.mark_name || ''}${p.model_name ? ' - ' + p.model_name : ''}</small></td>
							<td align="center">${p.quantity ?? 0}</td>
							<td align="center">${(p.total_weight ?? 0).toFixed(2)} kg</td>
							<td align="center">$${(p.total_exchanged ?? 0).toFixed(2)}</td>
						</tr>
					`).join('')}
				</tbody>
				<tfoot>
					<tr style="font-weight: bold;">
						<td style="border-top: 1px solid #ccc;">Total</td>
						<td style="border-top: 1px solid #ccc;" align="center">${totalQty}</td>
						<td style="border-top: 1px solid #ccc;" align="center">${totalWeight.toFixed(2)} kg</td>
						<td style="border-top: 1px solid #ccc;" align="center">$${totalConverted.toFixed(2)}</td>
					</tr>
				</tfoot>
				
			</table>
			<div style="text-align: center; margin-bottom: 10px;">
				<p>Destination: ${destination}</p>
				<p>Created: ${createdAt}</p>
				<p>Estimate Arrival: ${deliveryDate}</p>
				${shippingImage}
				<p><strong>${shippingNumber}</strong></p>
			</div>
		`;
	}

	async function openTrackingInfo(shippings_id) {
		try {
			let response = await fetch(`api/get_shippings.php?shipping_id=${shippings_id}`, {
				method: "GET",
				headers: { "Accept": "application/json" }
			});

			let data = await response.json();

			if (!data.success || !data.data || data.data.length === 0) {
				console.warn("No tracking history available.");
				return;
			}

			// tomamos el shipping encontrado
			const shipping = data.data.find(s => s.shippings_id == shippings_id);

			if (!shipping) {
				console.warn("Shipping not found.");
				return;
			}
			
			const trackingHistory = (shipping.all_tracking || []).slice().reverse();

			let trackingHTML = "";

			if (trackingHistory.length === 0) {
				trackingHTML = "<p>No tracking history available.</p>";
			} else {
				trackingHTML = trackingHistory.map((t, index) => {
					const isLatest = index === trackingHistory.length - 1; 
                	const dotColor = isLatest ? "var(--agree-green)" : "var(--clr-neutral-dark)";
                	const lineColor = "var(--clr-neutral-dark)";

					return`
					<tr valign="baseline">
						<td width="10%" style="position: relative; padding: 5px 0 0;" align="center" valign="middle">
							<!-- Dot -->
							${
								isLatest
                                    ? `<div style="
											width: 12px;
											height: 12px;
											background:${dotColor};
											border-radius: 50%;
											margin: 0 auto;
											position: relative;
											top: -3px;
											z-index: 2;
										"></div>`
									: `<div style="
											width: 12px;
											height: 12px;
											background:${dotColor};
											border-radius: 50%;
											margin: 0 auto;
											position: relative;
											top: 2px;
											z-index: 2;
										"></div>`
							}

                            <!-- Line below (only if not last item) -->
                            ${
                                index < trackingHistory.length - 1
                                    ? `<div style="
                                            width: 2px;
                                            height: 8px;
                                            background:${lineColor};
                                            margin: 0 auto;
                                            position: relative;
                                            top: 8px;
                                            z-index: 1;
                                       "></div>`
                                    : ""
                            }
						</td>
						<td width="65%" style="padding: 10px 0;" valign="middle">${t.checkpoint_name}</td>
						<td width="25%" style="padding: 10px 0;" valign="middle">${formatFullDateTime(t.created_at)}</td>
					</tr>
				`}).join("");
			}

			document.getElementById("tracking-info-body").innerHTML = `
				<table class="tracking-table" cellspacing="0" cellpadding="0">
					<thead>
						<tr valign="baseline">
							<th colspan="6" style="text-align: center; padding-bottom: 10px; font-size: 14px;">
								Tracking History
							</th>
						</tr>
						<tr valign="baseline">
							<th width="10%" style="padding-bottom: 10px; border-bottom: 1px solid var(--clr-neutral-dark);" align="left"></th>
							<th width="65%" style="padding-bottom: 10px; border-bottom: 1px solid var(--clr-neutral-dark);" align="left">Location</th>
							<th width="25%" style="padding-bottom: 10px; border-bottom: 1px solid var(--clr-neutral-dark);" align="left">Date</th>
						</tr>
					</thead>
					<tbody>
						${trackingHTML}
					</tbody>
				</table>
			`;

		} catch (error) {
			console.error("Error loading tracking:", error);
		}
	}

	// 🔹 Función para obtener configuraciones generales de la compañía
	async function getCompanySettings(companyId) {
		try {
			const res = await fetch(`api/get_general_config.php?company_id=${companyId}`);
			const result = await res.json();

			if (result && result.data) {
				return result.data;
			}
			return {};
		} catch (err) {
			console.error('Error loading company settings:', err);
			return {};
		}
	}

	// 📌 script para add shipping popup
	let addShippingBtn = document.getElementById('add-shipping-btn');
	if (addShippingBtn) {
		addShippingBtn.addEventListener('click', async function (e) {
			scrollToTopIfNeeded();
			
			const addShippingForm = document.getElementById('add-shipping-form');
			const popupContent = addShippingForm.querySelector('.formular-frame');

			if (addShippingForm && popupContent) {
			    addShippingForm.style.display = 'block';
			    addShippingForm.style.opacity = '0';
			    addShippingForm.style.transition = 'opacity 0.5s ease';
			    setTimeout(() => {
			        addShippingForm.style.opacity = '1';
			    }, 10);

			    popupContent.style.transform = 'scale(0.7)';
			    popupContent.style.opacity = '0';
			    popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			    setTimeout(() => {
			        popupContent.style.transform = 'scale(1)';
			        popupContent.style.opacity = '1';
			    }, 50);
			}

			// populateCompanies('shipping_company_id');

			handlePopupClose("add-shipping-form", ".formular-frame", []);
		});
	}

	// 📌 Manejo del formulario de crear shipping
	let formAddShipping = document.getElementById('formAddShipping');
	if (formAddShipping) {
		formAddShipping.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				let response = await fetch('api/create_shipping.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, () => {
							window.location.href = data.redirect_url;
						});
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = data.img_gif;
				showBanner(banner);
			}
		});
	}

	async function openShippingForm(shippingsId) {
		scrollToTopIfNeeded();
	
		const shippingOptions = document.getElementById('shipping-options');
		const popupContent = shippingOptions.querySelector('.formular-frame');
		const shippingNo = document.getElementById('shipping-no');
	
		if (!shippingsId) return;

		try {
			const res = await fetch(`api/get_shippings.php`);
			const data = await res.json();

			if (shippingOptions && popupContent) {
				resetPopupView(['shipping-menu-buttons'], [
					'add-load-modal',
					'edit-shipping-modal'
				]);

				const editShippingBtn = document.getElementById('editShippingBtn');
				const addLoadBtn = document.getElementById('addLoadBtn');
				const printLabelBtn = document.getElementById('printShippingLabelBtn');
				const deleteShippingBtn = document.getElementById('deleteShippingBtn');

				let shipping = null;
				if (data?.success && Array.isArray(data.data)) {
					const sid = String(shippingsId);
					shipping = data.data.find(item => String(item.shippings_id) === sid);
				}

				if (!shipping) {
					console.warn("Shipping not found for ID:", shippingsId);
					return;
				}

				if (shipping?.company_id) {
					localStorage.setItem('selectedCompanyId', shipping.company_id);
				}

				// console.log("Opening shipping options for Shipping ID:", shippingsId, shipping);

				if (shippingNo) {
					shippingNo.textContent = shipping.shipping_no || 'Unnamed shipping';
				}

				shippingOptions.style.display = 'block';
				shippingOptions.style.opacity = '0';
				shippingOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					shippingOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				// Botón: Receive as initial
				if (editShippingBtn) {
					editShippingBtn.setAttribute('data-shipping-id', shippingsId);
					editShippingBtn.onclick = () => {
						const menuDiv = document.getElementById('shipping-menu-buttons');
						const editDiv = document.getElementById('edit-shipping-modal');

						if (editDiv) {
							editDiv.style.display = 'none';
						}

						const shippingsId = editShippingBtn.getAttribute('data-shipping-id');
						if (!shippingsId) return;

						openEditShippingForm(shippingsId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					};
				}
				
				// Botón: Add load to shipping
				if (addLoadBtn) {
					addLoadBtn.setAttribute('data-shipping-id', shippingsId);
					addLoadBtn.onclick = async () => {
						const menuDiv = document.getElementById('shipping-menu-buttons');
						const addDiv = document.getElementById('add-load-modal');

						if (addDiv) {
							addDiv.style.display = 'none';
						}

						const shippingsId = addLoadBtn.getAttribute('data-shipping-id');
						if (!shippingsId) return;

						const formFrame = document.getElementById('formular-frame');
						if (formFrame) {
							formFrame.classList.add('expanded');
						}

						openAddLoadForm(shippingsId);

						const companyId = localStorage.getItem('selectedCompanyId');
						let companyCurrency = '';
						let shippingKgPrice = '';

						if (companyId) {
							const settings = await getCompanySettings(companyId);
							companyCurrency = settings.company_currency || '';
							shippingKgPrice = settings.shipping_kg_price || '';
						
							populateCurrencies('shipping_from_currency', companyCurrency);

							const shippingPrice = document.getElementById('shipping_price');
							if (shippingPrice && shippingKgPrice !== null) {
								shippingPrice.value = shippingKgPrice || '';
							}
						}

						populateCurrencies('shipping_to_currency', 'USD');
			
						animateHeightChange(popupContent, addDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(addDiv);
							});
						});
					}
				}

				if (printLabelBtn) {
					printLabelBtn.onclick = () => {
						printLabelBtn.setAttribute('data-shipping-id', shippingsId);

						const id = printLabelBtn.getAttribute('data-shipping-id');
						if (!id) {
							alert("❌ Missing shipping ID.");
							return;
						}

						const url = `shipping_label.php?shipping_id=${encodeURIComponent(id)}`;
						window.open(url, '_blank', 'width=800,height=600');
					};
				}

				// Botón: Delete product
				if (deleteShippingBtn) {
					deleteShippingBtn.onclick = () => {
						deleteShippingBtn.setAttribute('data-shipping-id', shippingsId);
						
						if (!shippingsId) {
							alert("Shipping ID not found.");
							return;
						}

						showConfirmModal("Delete Shipping", "Are you sure you want to delete this Shipping?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("shippings_id", shippingsId);
				
							try {
								const response = await fetch('api/delete_shipping.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											window.location.href = data.redirect_url || window.location.href;
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting shipping:", error);
								alert("Error deleting shipping. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading shipping info:", error);
		}
	}

	async function openLoadForm(loadId) {
		scrollToTopIfNeeded();
	
		const loadOptions = document.getElementById('load-options');
		const popupContent = loadOptions.querySelector('.formular-frame');
		const loadNo = document.getElementById('load-no');
	
		if (!loadId) return;

		try {
			const res = await fetch(`api/get_shippings.php`);
			const data = await res.json();

			if (!data?.success || !Array.isArray(data.data)) {
				throw new Error("No shippings found.");
			}

			let shipping = null;
			let foundLoad = null;

			for (const item of data.data) {
				const match = item.loads.find(ld => String(ld.load_id) === String(loadId));
				if (match) {
					shipping = item;
					foundLoad = match;
					break;
				}
			}

			if (!shipping || !foundLoad) {
				console.warn("No shipping found for load ID:", loadId);
				return;
			}

			// console.log("Found Shipping:", shipping);
			// console.log("Found Load:", foundLoad);

			if (loadOptions && popupContent) {
				resetPopupView(['load-menu-buttons'], [
					'edit-load-modal',
					// 'edit-shipping-modal'
				]);

				const editLoadBtn = document.getElementById('editLoadBtn');
				const deleteLoadBtn = document.getElementById('deleteLoadBtn');

				if (loadNo) {
					loadNo.textContent = 'Load No: ' + foundLoad.load_no || 'Unnamed load';
				}
				
				loadOptions.style.display = 'block';
				loadOptions.style.opacity = '0';
				loadOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					loadOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				// Botón: Edit Load
				if (editLoadBtn) {
					editLoadBtn.setAttribute('data-load-id', loadId);
					editLoadBtn.onclick = () => {
						const menuDiv = document.getElementById('load-menu-buttons');
						const editDiv = document.getElementById('edit-load-modal');

						if (editDiv) {
							editDiv.style.display = 'none';
						}

						const shippingsId = editLoadBtn.getAttribute('data-load-id');
						if (!shippingsId) return;

						const formFrame2 = document.getElementById('formular-frame-2');
						if (formFrame2) {
							formFrame2.classList.add('expanded');
						}

						openEditLoadForm(shippingsId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					};
				}

				// Botón: Delete Load
				if (deleteLoadBtn) {
					deleteLoadBtn.onclick = () => {
						deleteLoadBtn.setAttribute('data-load-id', loadId);
						
						if (!loadId) {
							alert("Shipping ID not found.");
							return;
						}

						showConfirmModal("Delete Load", "Are you sure you want to delete this Load?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("load_id", loadId);
				
							try {
								const response = await fetch('api/delete_load.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(() => {
										hideBanner(banner, () => {
											const loadOptions = document.getElementById('load-options');
											if (loadOptions) fadeOutAndHide(loadOptions);

											const shippingOptions = document.getElementById('shipping-options');
											if (shippingOptions) fadeOutAndHide(shippingOptions);

											refreshSelectedShipping();
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting load:", error);
								alert("Error deleting load. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading load info:", error);
		}
	}

	async function openEditShippingForm(shippingsId) {
		const formEditShipping = document.getElementById('formEditShipping');
		if (!formEditShipping) return;
	
		formEditShipping.setAttribute('data-shipping-id', shippingsId);

		const params = new URLSearchParams();
		params.append('shippings_id', shippingsId);
	
		try {
			const response = await fetch(`api/get_shippings.php?${params.toString()}`);
			const data = await response.json();
	
			if (data.success && data.data.length > 0) {
				const shipping = data.data.find(p => p.shippings_id == shippingsId);
				if (!shipping) return;
				
				// Llenar campos del formulario
				document.getElementById('edit_destination').value = shipping.destination || '';
				document.getElementById('edit_delivery_date').value = shipping.delivery_date || '';
				document.getElementById('edit_description').value = shipping.description || '';
				document.getElementById("edit_status").checked = shipping.status === "1" || shipping.status === 1;

				// ✅ Seleccionar el radio correcto directamente
				const method = String(shipping.shipping_method || "1");
				const methodRadio = document.querySelector(`input[name="edit_shipping_method"][value="${method}"]`);
				if (methodRadio) methodRadio.checked = true;

				// ✅ Iniciar control visual y lógica con solo el radio
				initShippingMethod("edit_shipping_method", null, {activeClass: 'selected'});

				handlePopupClose("shipping-options", ".formular-frame", []);
			}
		} catch (error) {
			console.error("Error loading product data:", error);
		}
	}

	function initShippingMethod(radioName, onChangeCallback, options = {}) {
		const radios = document.getElementsByName(radioName);
		if (radios.length === 0) return;

		const activeClass = options.activeClass || 'selected';

		const applySelection = () => {
			const selected = Array.from(radios).find(r => r.checked);
			if (!selected) return;

			// Aplicar clase visual si hay etiquetas vinculadas
			radios.forEach(radio => {
				const label = document.querySelector(`label[for="${radio.id}"]`);
				if (label) {
					label.classList.toggle(activeClass, radio.checked);
				}
			});

			// Callback personalizado
			if (typeof onChangeCallback === 'function') {
				onChangeCallback(selected.value);
			}
		};

		// Eventos
		radios.forEach(radio => {
			radio.addEventListener('change', applySelection);
		});

		// Inicializar estado
		applySelection();
	}

	const formEditShipping = document.getElementById('formEditShipping');
	if (formEditShipping) {
		formEditShipping.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);
			formData.append('edit_shipping_id', formEditShipping.getAttribute('data-shipping-id'));

			try {
				const response = await fetch('api/update_shipping.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				const data = await response.json();

				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');

				if (banner && statusText && statusImage) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "images/sys-img/loading.gif";
					showBanner(banner);
				}

				if (data.success) {
					setTimeout(() => {
						hideBanner(banner,() => {
							formEditShipping.reset();
							
							const editShippingModal = document.getElementById('edit-shipping-modal');
							if (editShippingModal) fadeOutAndHide(editShippingModal);

							const shippingOptions = document.getElementById('shipping-options');
							if (shippingOptions) fadeOutAndHide(shippingOptions);

							refreshSelectedShipping();
						});
					}, 3000);
				}
			} catch (error) {
				console.error("Error updating product:", error);
			}
		});
	}

	async function openAddLoadForm(shippingsId) {
		const formAddLoad = document.getElementById('formAddLoad');
		if (!formAddLoad) return;

		formAddLoad.setAttribute('data-shipping-id', shippingsId);	
		// Inicializar selección de clientes
		const searchCustomerInput = document.getElementById('search-shipping-customer');
		const customerListTable = document.getElementById('select-shipping-customers-list');

		if (searchCustomerInput && customerListTable) {
			async function fetchAndRenderCustomersForShipping(search = '') {
				try {
					const params = new URLSearchParams();
					if (search.trim() !== '') {
						params.append('search', search.trim());
					}

					const response = await fetch(`api/get_customers.php?${params.toString()}`, {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});
					const data = await response.json();
					customerListTable.innerHTML = '';

					if (data.success && data.data.length > 0) {
						data.data.forEach(customer => {
							const uniqueId = `shipping-customer-${customer.customer_id}`;
							const profileImg = customer.image && customer.image.trim() !== '' ? `images/customers/${customer.image}` : `images/sys-img/NonProfilePic.png`;

							const row = document.createElement('tr');
							row.className = 'categoryContainer';
							row.innerHTML = `
								<td width='10%' align='center' valign='middle'>
									<div class='customers-profile'>
										<img src='${profileImg}' alt=''>
									</div>
								</td>
								<td width='80%' valign='middle' style='padding-left:10px;'>
									<strong>${customer.full_name}</strong>
									<p class='mini-title' style='color: #000;'>${customer.document_type}: <strong>${customer.document_no}</strong></p>
								</td>
								<td width='10%' align='center' valign='middle'>
									<div class='opcion-radio'>
										<input type='radio' id='${uniqueId}' name='customer_select' class='category-radio' data-id='${customer.customer_id}' />
										<label for='${uniqueId}'></label>
									</div>
								</td>
							`;
							customerListTable.appendChild(row);
						});
					} else {
						customerListTable.innerHTML = `
							<tr><td colspan='3' style='text-align:center; padding: 10px;'>No customers found.</td></tr>
						`;
					}
				} catch (error) {
					console.error('Error loading customers:', error);
					customerListTable.innerHTML = `
						<tr><td colspan='3' style='text-align:center; padding: 10px;'>Error loading customers</td></tr>
					`;
				}
			}

			searchCustomerInput.addEventListener('input', () => {
				fetchAndRenderCustomersForShipping(searchCustomerInput.value);
			});

			fetchAndRenderCustomersForShipping();
		}

		// Cargar productos para shipping
		const searchProductInputForShipping = document.getElementById('search-product-for-shipping');
		const shippingMarkSelect = document.getElementById('search-product-mark-for-shipping');
		const shippingProductListTable = document.getElementById('select-product-list-for-shipping');

		if ((searchProductInputForShipping || shippingMarkSelect) && shippingProductListTable) {
			async function fetchAndRenderProductsForShipping(search = "", mark = "") {
				try {
					const params = new URLSearchParams();
					if (search.trim() !== "") {
						params.append('search', search.trim());
					}
					if (mark && mark !== "") {
						params.append('mark', mark);
					}

					const response = await fetch(`api/get_products.php?${params.toString()}`, {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});
					const data = await response.json();
					shippingProductListTable.innerHTML = "";

					if (data.success && data.data.length > 0) {
						data.data.forEach(product => {
							const uniqueId = `edit-product-${product.product_id}`;
							const productImg = product.product_image && product.product_image.trim() !== ''
								? `images/products/${product.product_image}`
								: `images/sys-img/wooden-box.png`;

							const row = document.createElement('tr');
							row.className = "productContainer";
							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="list-icon">
										<img src="${productImg}" alt="product image" width="32" height="32">
									</div>
								</td>
								<td width="75%" valign="middle" style="padding-left:10px;">
									${product.product_name} <span class="mini-title">(${product.purpose_text})</span><br>
									<small>${product.mark_name || ''} - ${product.model_name || ''} ${product.submodel_name || ''}</small>
								</td>
								<td width="5%" align="left" valign="middle">
									<input type="number" id="qty-${uniqueId}" class="form-mini-input-style" value="1" min="1" disabled />
								</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-checkbox">
										<input type="checkbox" id="${uniqueId}" name="product_selection[]" value="${product.product_id}" data-price="${product.price}" data-weight="${product.total_weight}" class="shipping-product-checkbox" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;
							shippingProductListTable.appendChild(row);

							const checkbox = document.getElementById(uniqueId);
							const quantityInput = document.getElementById(`qty-${uniqueId}`);
							const OutOfStock = product.quantity <= 0;

							if (OutOfStock) {
								checkbox.disabled = true;
								quantityInput.disabled = true;
								quantityInput.value = 0;
							} else {
								checkbox.addEventListener('change', function () {
									if (this.checked) {
										quantityInput.disabled = false;
										quantityInput.focus();
									} else {
										quantityInput.disabled = true;
										quantityInput.value = 1;
									}
									sumByWeight();
								});
							}

							quantityInput.addEventListener('input', function () {
								if (parseInt(this.value) <= 0 || isNaN(parseInt(this.value))) {
									this.value = 1;
								}
								sumByWeight();
							});

							document.getElementById(uniqueId).addEventListener('change', sumByWeight);
						});
					} else {
						shippingProductListTable.innerHTML = `
							<tr><td colspan="3" style="text-align:center; padding: 10px;">No products found.</td></tr>
						`;
					}
				} catch (error) {
					console.error("Error loading products:", error);
					shippingProductListTable.innerHTML = `
						<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading products</td></tr>
					`;
				}
			}

			searchProductInputForShipping.addEventListener('input', () => {
				fetchAndRenderProductsForShipping(searchProductInputForShipping.value, shippingMarkSelect.value);
			});
			shippingMarkSelect.addEventListener('change', () => {
				fetchAndRenderProductsForShipping(searchProductInputForShipping.value, shippingMarkSelect.value);
			});

			loadMarksForSearch(shippingMarkSelect).then(() => fetchAndRenderProductsForShipping());
		}

		// Llenar campos del formulario
		function sumByWeight() {
			const checkboxes = document.querySelectorAll('.shipping-product-checkbox:checked');
			let total = 0;
		
			checkboxes.forEach(cb => {
				const weight = parseFloat(cb.getAttribute('data-weight')) || 0;
				const qtyInput = document.getElementById(`qty-${cb.id}`);
				const quantity = parseInt(qtyInput.value) || 1;
				total += weight * quantity;
			});
		
			document.getElementById('total_kg').value = total.toFixed(2);

			updateShippingCalculations();
		}

		function updateShippingCalculations() {
			const totalKg = parseFloat(document.getElementById('total_kg').value.replace(/,/g, '')) || 0;
			const pricePerKg = parseFloat(document.getElementById('shipping_price').value.replace(/,/g, '')) || 0;
			const discount = parseFloat(document.getElementById('discount').value.replace(/,/g, '')) || 0;
			const taxPercent = parseFloat(document.getElementById('taxes').value.replace(/,/g, '')) || 0;

			const priceSum = totalKg * pricePerKg;
			const subtotal = priceSum - discount;
			const taxAmount = (subtotal * taxPercent) / 100;
			const total = subtotal + taxAmount;

			document.getElementById('price_sum').value = priceSum.toFixed(2);
			document.getElementById('total').value = total.toFixed(2);

			updateTotalExchange("total", "total_exchanged", "shipping_from_currency", "shipping_to_currency");
		}

		const shippingPriceInput = document.getElementById('shipping_price');
		const discountInput = document.getElementById('discount');
		const taxesInput = document.getElementById('taxes');

		if (shippingPriceInput) {
			shippingPriceInput.addEventListener('input', updateShippingCalculations);
		}
		if (discountInput) {
			discountInput.addEventListener('input', updateShippingCalculations);
		}
		if (taxesInput) {
			taxesInput.addEventListener('input', updateShippingCalculations);
		}

		const totalInput = document.getElementById("total");
		const currencySelect = document.getElementById("shipping_to_currency");
		const fromCurrencySelect = document.getElementById("shipping_from_currency");

		if (currencySelect && !currencySelect.value) {
			currencySelect.value = "USD";
		}

		if (totalInput) {
			totalInput.addEventListener("input", () => {
				updateTotalExchange("total", "total_exchanged", "shipping_from_currency", "shipping_to_currency");
			});
		}

		if (fromCurrencySelect) {
			fromCurrencySelect.addEventListener("change", () => {
				updateTotalExchange("total", "total_exchanged", "shipping_from_currency", "shipping_to_currency");
			});
		}

		if (currencySelect) {
			currencySelect.addEventListener("change", () => {
				updateTotalExchange("total", "total_exchanged", "shipping_from_currency", "shipping_to_currency");
			});
		}

		handlePopupClose("shipping-options", ".formular-frame", []);
	}

	const formAddLoad = document.querySelector('#formAddLoad');
	if (formAddLoad) {
		formAddLoad.addEventListener('submit', function (e) {
			e.preventDefault();

			(async () => {
				try {
					const formatDecimal = val => parseFloat((val || '').toString().replace(',', '').trim()) || 0;

					const shippingId = formAddLoad.getAttribute('data-shipping-id');
					if (!shippingId) throw new Error("Shipping ID not found.");

					const customerId = document.querySelector('input[name="customer_select"]:checked')?.dataset.id;
					if (!customerId) throw new Error("Select a customer.");

					// Obtener valores del formulario
					const fromCurrency = document.getElementById('shipping_from_currency')?.value || "USD";
					const toCurrency = document.getElementById('shipping_to_currency')?.value || "USD";
					const pricePerKg = formatDecimal(document.getElementById('shipping_price')?.value);
					const totalKg = formatDecimal(document.getElementById('total_kg')?.value);
					const discount = formatDecimal(document.getElementById('discount')?.value);
					const taxes = formatDecimal(document.getElementById('taxes')?.value);
					const totalExchanged = formatDecimal(document.getElementById('total_exchanged')?.value);
					const destination = document.getElementById('destination')?.value.trim() || '';
					const comment = document.getElementById('comment')?.value.trim() || '';

					if (pricePerKg <= 0 || totalKg <= 0) {
						throw new Error("Price/kg and Total Kg must be greater than 0.");
					}

					// Productos seleccionados
					const productCheckboxes = Array.from(document.querySelectorAll('.shipping-product-checkbox:checked'));
					const products = await Promise.all(productCheckboxes.map(async cb => {
						const productId = parseInt(cb.value);
						const weight = parseFloat(cb.dataset.weight) || 0;
						const qtyInput = document.getElementById(`qty-${cb.id}`);
						const quantity = parseInt(qtyInput?.value) || 1;
						const totalKgProduct = weight * quantity;
						const totalKgPrice = totalKgProduct * pricePerKg;

						// 💱 Convertir el precio a la moneda destino
						const totalPriceExchanged = await convertCurrency(totalKgPrice, fromCurrency, toCurrency);

						return {
							product_id: productId,
							quantity: quantity,
							total_kg: Number(totalKgProduct.toFixed(3)),
							total_kg_price: Number(totalKgPrice.toFixed(3)),          // precio original (from_currency)
							total_price_exchanged: Number(totalPriceExchanged.toFixed(3)) // precio convertido (to_currency)
						};
					}));

					if (products.length === 0) throw new Error("Select at least one product.");

					// Construir payload
					const payload = {
						shippings_id: parseInt(shippingId),
						customer_id: parseInt(customerId),
						from_currency: fromCurrency,
						to_currency: toCurrency,
						price_per_kg: pricePerKg,
						total_kg: totalKg,
						discount: discount,
						taxes: taxes,
						price_total_exchanged: totalExchanged,
						destination: destination,
						comment: comment,
						products: products
					};

					// Enviar al backend
					const res = await fetch('api/create_load.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify(payload)
					});

					const data = await res.json();

					// Banner de estado visual
					const banner = document.getElementById('status-message');
					const statusText = document.getElementById('status-text');
					const statusImage = document.getElementById('status-image');

					if (banner && statusText && statusImage) {
						statusText.innerText = data.message || "Unknown response";
						statusImage.src = data.img_gif || "../images/sys-img/success.gif";
						showBanner(banner);
					}

					if (data.success) {
						setTimeout(() => {
							hideBanner(banner, () => {
								formAddLoad.reset();

								const addLoadModal = document.getElementById('add-load-modal');
								if (addLoadModal) fadeOutAndHide(addLoadModal);

								const shippingOptions = document.getElementById('shipping-options');
								if (shippingOptions) fadeOutAndHide(shippingOptions);

								refreshSelectedShipping();
							});
						}, 3000);
					} else {
						alert("❌ Failed: " + (data.message || "Unknown error."));
					}

				} catch (error) {
					alert("⚠️ Error: " + error.message);
				}
			})();
		});
	}

	async function openEditLoadForm(loadId) {
		const formEditLoad = document.getElementById('formEditLoad');
		if (!formEditLoad) return;

		formEditLoad.setAttribute('data-load-id', loadId);

		try {
			const response = await fetch(`api/get_load.php?load_id=${loadId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await response.json();

			if (!data.success || !data.data) {
				console.warn("Load not found or invalid response.");
				return;
			}

			const load = data.data;

			// Inicializar selección de clientes
			const searchEditCustomerInput = document.getElementById('search-edit-shipping-customer');
			const editCustomerListTable = document.getElementById('select-edit-shipping-customers-list');

			if (searchEditCustomerInput && editCustomerListTable) {
				async function fetchAndRenderCustomersForShipping(search = '') {
					try {
						const params = new URLSearchParams();
						if (search.trim() !== '') params.append('search', search.trim());

						const response = await fetch(`api/get_customers.php?${params.toString()}`);
						const customerData = await response.json();
						editCustomerListTable.innerHTML = '';

						if (customerData.success && customerData.data.length > 0) {
							customerData.data.forEach(customer => {
								const uniqueId = `shipping-customer-${customer.customer_id}`;
								const profileImg = customer.image && customer.image.trim() !== '' 
									? `images/customers/${customer.image}` 
									: `images/sys-img/NonProfilePic.png`;

								const row = document.createElement('tr');
								row.className = 'categoryContainer';
								row.innerHTML = `
									<td width='10%' align='center' valign='middle'>
										<div class='customers-profile'>
											<img src='${profileImg}' alt=''>
										</div>
									</td>
									<td width='80%' valign='middle' style='padding-left:10px;'>
										<strong>${customer.full_name}</strong>
										<p class='mini-title' style='color: #000;'>${customer.document_type}: <strong>${customer.document_no}</strong></p>
									</td>
									<td width='10%' align='center' valign='middle'>
										<div class='opcion-radio'>
											<input type='radio' id='${uniqueId}' name='customer_select' class='category-radio' data-id='${customer.customer_id}' />
											<label for='${uniqueId}'></label>
										</div>
									</td>
								`;
								editCustomerListTable.appendChild(row);

								if (String(customer.customer_id) === String(load.customer.customer_id)) {
									const customerRadio = document.getElementById(uniqueId);
									if (customerRadio) customerRadio.checked = true;
								}
							});
						} else {
							editCustomerListTable.innerHTML = `
								<tr><td colspan='3' style='text-align:center; padding: 10px;'>No customers found.</td></tr>
							`;
						}
					} catch (error) {
						console.error('Error loading customers:', error);
						editCustomerListTable.innerHTML = `
							<tr><td colspan='3' style='text-align:center; padding: 10px;'>Error loading customers</td></tr>
						`;
					}
				}

				searchEditCustomerInput.addEventListener('input', () => {
					fetchAndRenderCustomersForShipping(searchEditCustomerInput.value);
				});

				await fetchAndRenderCustomersForShipping();
			}

			// Cargar productos para shipping
			const searchEditProductInput = document.getElementById('search-edit-product-for-shipping');
			const editMarkSelect = document.getElementById('search-edit-product-mark-for-shipping');
			const editShippingProductListTable = document.getElementById('edit-select-product-list-for-shipping');

			if ((searchEditProductInput || editShippingMarkSelect) && editShippingProductListTable) {
				async function fetchAndRenderProductsForShipping(search = "", mark = "") {
					try {
						const params = new URLSearchParams();
						if (search.trim() !== "") params.append('search', search.trim());
						if (mark) params.append('mark', mark);

						const response = await fetch(`api/get_products.php?${params.toString()}`);
						const productData = await response.json();
						editShippingProductListTable.innerHTML = "";

						if (productData.success && productData.data.length > 0) {
							productData.data.forEach(product => {
								const uniqueId = `edit-product-${product.product_id}`;
								const productImg = product.product_image && product.product_image.trim() !== ''
									? `images/products/${product.product_image}`
									: `images/sys-img/wooden-box.png`;

								const row = document.createElement('tr');
								row.className = "productContainer";
								row.innerHTML = `
									<td width="10%" align="center" valign="middle">
										<div class="list-icon">
											<img src="${productImg}" alt="product image" width="32" height="32">
										</div>
									</td>
									<td width="75%" valign="middle" style="padding-left:10px;">
										${product.product_name} <span class="mini-title">(${product.purpose_text})</span><br>
										<small>${product.mark_name || ''} - ${product.model_name || ''} ${product.submodel_name || ''}</small>
									</td>
									<td width="5%" align="left" valign="middle">
										<input type="number" id="qty-${uniqueId}" class="form-mini-input-style" value="1" min="1" disabled />
									</td>
									<td width="10%" align="center" valign="middle">
										<div class="opcion-checkbox">
											<input type="checkbox" id="${uniqueId}" name="product_selection[]" value="${product.product_id}" data-price="${product.price}" data-weight="${product.total_weight}" class="shipping-product-checkbox" />
											<label for="${uniqueId}"></label>
										</div>
									</td>
								`;
								editShippingProductListTable.appendChild(row);

								const checkbox = document.getElementById(uniqueId);
								const quantityInput = document.getElementById(`qty-${uniqueId}`);
								
								// ✅ marcar los productos que ya están en este load
								const selectedProduct = load.products.find(p => p.product_id === product.product_id);
								if (selectedProduct) {
									checkbox.checked = true;
									quantityInput.disabled = false;
									quantityInput.value = selectedProduct.quantity;
								}

								checkbox.addEventListener('change', function () {
									quantityInput.disabled = !this.checked;
									if (!this.checked) quantityInput.value = 1;
									sumByWeight();
								});

								quantityInput.addEventListener('input', function () {
									if (parseInt(this.value) <= 0 || isNaN(parseInt(this.value))) {
										this.value = 1;
									}
									sumByWeight();
								});

								document.getElementById(uniqueId).addEventListener('change', sumByWeight);
							});
						} else {
							editShippingProductListTable.innerHTML = `
								<tr><td colspan="3" style="text-align:center; padding: 10px;">No products found.</td></tr>
							`;
						}
					} catch (error) {
						console.error("Error loading products:", error);
						editShippingProductListTable.innerHTML = `
							<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading products</td></tr>
						`;
					}
				}

				searchEditProductInput.addEventListener('input', () => {
					fetchAndRenderProductsForShipping(searchEditProductInput.value, editMarkSelect.value);
				});
				editMarkSelect.addEventListener('change', () => {
					fetchAndRenderProductsForShipping(searchEditProductInput.value, editMarkSelect.value);
				});

				await loadMarksForSearch(editMarkSelect);
            	await fetchAndRenderProductsForShipping();
			}

			populateCurrencies('edit_shipping_from_currency', load.from_currency);
			populateCurrencies('edit_shipping_to_currency', load.to_currency);

			document.getElementById('edit_shipping_price').value = load.price_per_kg;
			document.getElementById('edit_total_kg').value = load.total_kg;
			document.getElementById('edit_price_sum').value = load.price_sum;
			document.getElementById('edit_discount').value = load.discount;
			document.getElementById('edit_taxes').value = load.taxes;
			document.getElementById('edit_total').value = load.price_total;
			document.getElementById('edit_total_exchanged').value = load.price_total_exchanged;
			document.getElementById('edit_load_destination').value = load.destination || '';
			document.getElementById('edit_comment').value = load.comment || '';

			// Llenar campos del formulario
			function sumByWeight() {
				const checkboxes = document.querySelectorAll('.shipping-product-checkbox:checked');
				let total = 0;
			
				checkboxes.forEach(cb => {
					const weight = parseFloat(cb.getAttribute('data-weight')) || 0;
					const qtyInput = document.getElementById(`qty-${cb.id}`);
					const quantity = parseInt(qtyInput.value) || 1;
					total += weight * quantity;
				});
			
				document.getElementById('edit_total_kg').value = total.toFixed(2);

				updateShippingCalculations();
			}

			function updateShippingCalculations() {
				const totalKg = parseFloat(document.getElementById('edit_total_kg').value.replace(/,/g, '')) || 0;
				const pricePerKg = parseFloat(document.getElementById('edit_shipping_price').value.replace(/,/g, '')) || 0;
				const discount = parseFloat(document.getElementById('edit_discount').value.replace(/,/g, '')) || 0;
				const taxPercent = parseFloat(document.getElementById('edit_taxes').value.replace(/,/g, '')) || 0;

				const priceSum = totalKg * pricePerKg;
				const subtotal = priceSum - discount;
				const taxAmount = (subtotal * taxPercent) / 100;
				const total = subtotal + taxAmount;

				document.getElementById('edit_price_sum').value = priceSum.toFixed(2);
				document.getElementById('edit_total').value = total.toFixed(2);

				updateTotalExchange("edit_total", "edit_total_exchanged", "edit_shipping_from_currency", "edit_shipping_to_currency");
			}

			// Inputs reactivos
			['edit_shipping_price', 'edit_discount', 'edit_taxes', 'edit_total_kg'].forEach(id => {
				const el = document.getElementById(id);
				if (el) el.addEventListener('input', updateShippingCalculations);
			});

			['edit_shipping_from_currency', 'edit_shipping_to_currency'].forEach(id => {
				const el = document.getElementById(id);
				if (el) el.addEventListener('change', () => {
					updateTotalExchange(
						"edit_total",
						"edit_total_exchanged",
						"edit_shipping_from_currency",
						"edit_shipping_to_currency"
					);
				});
			});

			handlePopupClose("shipping-options", ".formular-frame", []);
		} catch (error) {
			console.error("Error loading shipping data:", error);
		}
	}

	const formEditLoad = document.querySelector('#formEditLoad');
	if (formEditLoad) {
		formEditLoad.addEventListener('submit', function (e) {
			e.preventDefault();

			(async () => {
				try {
					const formatDecimal = val => parseFloat((val || '').toString().replace(',', '').trim()) || 0;
					
					const loadId = formEditLoad.getAttribute('data-load-id');
					if (!loadId) throw new Error("Load ID not found.");

					const customerId = document.querySelector('input[name="customer_select"]:checked')?.dataset.id;
					if (!customerId) throw new Error("Please select a customer.");

					// Obtener valores del formulario
					const fromCurrency = document.getElementById('edit_shipping_from_currency')?.value || "USD";
					const toCurrency = document.getElementById('edit_shipping_to_currency')?.value || "USD";
					const pricePerKg = formatDecimal(document.getElementById('edit_shipping_price')?.value);
					const totalKg = formatDecimal(document.getElementById('edit_total_kg')?.value);
					const discount = formatDecimal(document.getElementById('edit_discount')?.value);
					const taxes = formatDecimal(document.getElementById('edit_taxes')?.value);
					const totalExchanged = formatDecimal(document.getElementById('edit_total_exchanged')?.value);
					const destination = document.getElementById('edit_load_destination')?.value.trim() || '';
					const comment = document.getElementById('edit_comment')?.value.trim() || '';

					if (pricePerKg <= 0 || totalKg <= 0) {
						throw new Error("Price/kg and Total Kg must be greater than 0.");
					}

					// Obtener productos seleccionados
					const productCheckboxes = Array.from(document.querySelectorAll('.shipping-product-checkbox:checked'));
					const products = await Promise.all(productCheckboxes.map(async cb => {
						const productId = parseInt(cb.value);
						const weight = parseFloat(cb.dataset.weight) || 0;
						const qtyInput = document.getElementById(`qty-${cb.id}`);
						const quantity = parseInt(qtyInput?.value) || 1;
						const totalKgProduct = weight * quantity;
						const totalKgPrice = totalKgProduct * pricePerKg;

						const totalPriceExchanged = await convertCurrency(totalKgPrice, fromCurrency, toCurrency);

						return {
							product_id: productId,
							quantity: quantity,
							total_kg: Number(totalKgProduct.toFixed(3)),
							total_kg_price: Number(totalKgPrice.toFixed(3)),
							total_price_exchanged: Number(totalPriceExchanged.toFixed(3))
						};
					}));

					if (products.length === 0) throw new Error("Select at least one product.");

					// Construir payload
					const payload = {
						load_id: parseInt(loadId),
						customer_id: parseInt(customerId),
						from_currency: fromCurrency,
						to_currency: toCurrency,
						price_per_kg: pricePerKg,
						total_kg: totalKg,
						discount: discount,
						taxes: taxes,
						price_total_exchanged: totalExchanged,
						destination: destination,
						comment: comment,
						products: products
					};

					// Enviar al backend
					const res = await fetch('api/update_load.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/json' },
						body: JSON.stringify(payload)
					});

					const data = await res.json();
					// console.log("📡 API Update Response:", data);

					const banner = document.getElementById('status-message');
					const statusText = document.getElementById('status-text');
					const statusImage = document.getElementById('status-image');

					if (banner && statusText && statusImage) {
						statusText.innerText = data.message || "Unknown response";
						statusImage.src = data.img_gif || "../images/sys-img/success.gif";
						showBanner(banner);
					}

					if (data.success) {
						setTimeout(() => {
							hideBanner(banner, () => {
								window.location.href = data.redirect_url || window.location.href;
							});
						}, 3000);
					} else {
						alert("❌ Failed: " + (data.message || "Unknown error."));
					}

				} catch (error) {
					alert("⚠️ Error: " + error.message);
					console.error(error);
				}
			})();
		});
	}

	setupBackToMenuButton(
		'.back-to-shipping-menu-btn', 
		['edit-shipping-modal', 'add-load-modal'], 
		'shipping-menu-buttons', 
		'shipping-options'
	);

	setupBackToMenuButton(
		'.back-to-load-menu-btn', 
		['edit-load-modal'], 
		'load-menu-buttons', 
		'load-options'
	);
	//############################################################# END SHIPPING ##################################################################

	//############################################################# SYS-ADMIN ##################################################################
	const sysMenuItems = document.querySelectorAll(".system-menu li");
	const systemContent = document.getElementById("system-content");

	// Recuperar última sección seleccionada
	const savedSection = localStorage.getItem("activeSection");

	sysMenuItems.forEach(i => i.classList.remove("active"));

	sysMenuItems.forEach(item => {
		item.addEventListener("click", async () => {
			const section = item.getAttribute("data-section");
			localStorage.setItem("activeSection", section);

			sysMenuItems.forEach(i => i.classList.remove("active"));
			item.classList.add("active");

			// document.getElementById("system-details")?.innerHTML = "";

			try {
				const response = await fetch(`sys-admin/${section}.php`);
				const html = await response.text();
				systemContent.innerHTML = html;

				if (section === "user-overview") {
					loadAllUsers('searchUserOverviewField', 'userOverviewTable', 'user-overview');
				}

				if (section === "service-rights") {
					loadAllUsers('searchServiceRightsField', 'serviceRightsTable', 'service-rights');
				}

				if (section === "extra-service") {
					loadAllUsers('searchUserField', 'userTable', 'extra-service');
				}
			} catch (err) {
				systemContent.innerHTML = `<p style="color:red;">Error loading section: ${err.message}</p>`;
			}
		});

		// Restaurar activo al cargar
		if (savedSection && item.getAttribute("data-section") === savedSection) {
			item.classList.add("active");
			fetch(`sys-admin/${savedSection}.php`)
				.then(res => res.text())
				.then(html => {
					systemContent.innerHTML = html
				
					if (savedSection === "user-overview") {
						loadAllUsers('searchUserOverviewField', 'userOverviewTable', 'user-overview');
					}

					if (savedSection === "service-rights") {
						loadAllUsers('searchServiceRightsField', 'serviceRightsTable', 'service-rights');
					}

					if (savedSection === "extra-service") {
						loadAllUsers('searchUserField', 'userTable', 'extra-service');
					}
				})
				.catch(err => {
					systemContent.innerHTML = `<p style="color:red;">Error restoring section: ${err.message}</p>`;
				});
		}
	});
	
	function loadAllUsers(searchField, userTable, sectionType = "user-overview") {
		const searchUserField = document.getElementById(searchField);
		const userListTable = document.getElementById(userTable);

		if (searchUserField && userListTable) {
			async function fetchAndRenderUsers(search = "") {
				try {
					const params = new URLSearchParams();
					if (search.trim() !== "") {
						params.append('search', search.trim());
					}

					const response = await fetch(`api/get_all_users_info.php?${params.toString()}`, {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});
					const data = await response.json();
					
					userListTable.innerHTML = "";

					if (data.success && Array.isArray(data.data) && data.data.length > 0) {
						data.data.forEach(user => {
							const uniqueId = `user-${user.user_id}`;
							const row = document.createElement('tr');
							row.className = "users-row";

							const profileImg = user.image && user.image.trim() !== ""
								? `images/profile/${user.image}`
								: `images/sys-img/NonProfilePic.png`;

							let borderColor = getUserBorderColor(user);

							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="customers-profile" style="border: 2px solid ${borderColor};">
										<img src="${profileImg}" alt="">
									</div>
								</td>
								<td width="80%" valign="middle" style="padding-left:10px;">
									<strong>${user.full_name || 'Unknown'}</strong>
									<p class="mini-title">${user.email}</p>
								</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-radio">
										<input type="radio" id="${uniqueId}" name="user_select" class="category-radio" data-id="${user.user_id}" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;

							row.addEventListener("click", () => {
								const radio = row.querySelector('input[type="radio"]');
								if (!radio.disabled) {
									radio.checked = true;

									// Quitar selección visual de las demás filas
									document.querySelectorAll('.users-row').forEach(r => r.classList.remove('selected-user'));

									// Agregar selección visual a esta fila
									row.classList.add('selected-user');

									// Ejecutar el cambio como si se hubiera hecho clic directamente en el radio
									handleUserSelect({ target: radio }, sectionType);
								}
							});

							userListTable.appendChild(row);
						});

						// ✅ Escuchar clics en los radios
						const radios = userListTable.querySelectorAll('input[name="user_select"]');
						radios.forEach(radio => {
							radio.addEventListener("change", (e) => handleUserSelect(e, sectionType));
						});
					} else {
						userListTable.innerHTML = `
							<tr><td colspan="3" style="text-align:center; padding: 10px;">No customers found.</td></tr>
						`;
					}
				} catch (error) {
					console.error("Error loading customers:", error);
					userListTable.innerHTML = `
						<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading customers</td></tr>
					`;
				}
			}

			searchUserField.addEventListener('input', () => {
				fetchAndRenderUsers(searchUserField.value);
			});

			fetchAndRenderUsers();
		}
	}

	async function handleUserSelect(e, sectionType) {
		const selectedUserId = e.target.getAttribute("data-id");

		localStorage.setItem("selectedUserId", selectedUserId);
		localStorage.setItem("selectedSectionType", sectionType);
		
		let detailsContainerId = "";
		switch (sectionType) {
			case "user-overview":
				detailsContainerId = "overview-details";
				break;
			case "extra-service":
				detailsContainerId = "service-details";
				break;
			case "service-rights":
				detailsContainerId = "rights-details";
				break;
			default:
				console.warn("Unknown section type:", sectionType);
				return;
		}

		const detailsContainer = document.getElementById(detailsContainerId);
		if (!detailsContainer) {
			console.warn(`⚠️ Container #${detailsContainerId} not found.`);
			return;
		}

		let endpoint = "";
		let sectionTitle = "";

		// 🔹 Decide qué endpoint usar según la sección actual
		switch (sectionType) {
			case "user-overview":
				endpoint = "api/get_user_overview.php";
				sectionTitle = "User Overview";
				break;
			case "service-rights":
				endpoint = "api/get_user_rights.php";
				sectionTitle = "Service Rights";
				break;
			case "extra-service":
				endpoint = "api/get_extra_services.php";
				sectionTitle = "Extra Services";
				break;
			default:
				console.warn("No endpoint defined for section:", sectionType);
				return;
		}

		try {
			const response = await fetch(`${endpoint}?user_id=${selectedUserId}`);
			const result = await response.json();

			let html = '';

			if (sectionType === "user-overview") {
				const users = Array.isArray(result.data) ? result.data : [];

				const pkg = result.meta?.package ?? null;
				const extraPack = Array.isArray(result.meta?.extra_pack)
					? result.meta.extra_pack
					: (result.meta?.extra_pack ? [result.meta.extra_pack] : []);
				const subs = result.meta?.subscription ?? null;
				const hasPackage = pkg && Object.keys(pkg).length > 0;

				let priceText = 'Free';
				if (pkg && pkg.package_price) {
					priceText = `${pkg.package_price} €`;
				}

				const collaborators = Array.isArray(result.meta.collaborators)
				? result.meta.collaborators
				: [];

				const affiliates = Array.isArray(result.meta?.affiliate)
				? result.meta.affiliate
				: [];

				html += `
					<div class="overview-header">
						${users.map(user => {
							let borderColor = getUserBorderColor(user);

							return `
								<div class="overview-profile-pic" style="border: 2px solid ${borderColor};">
									<img src="${user.image && user.image.trim() !== '' ? `images/profile/${user.image}` : 'images/sys-img/NonProfilePic.png'}" alt="Profile Picture">
								</div>
								<table class="overview-header-table" style="margin-top: 0;" width="80%" cellspacing="0" cellpadding="0">
									<tbody>
											<tr>
												<td>
													<div class="mini-title">ID:</div>
													${user.user_id}
												</td>
												<td>
													<div class="mini-title">Name:</div>
													${user.full_name}
												</td>
												<td>
													<div class="mini-title">Email:</div>
													${user.email}
												</td>
												<td>
													<div class="mini-title">Rank:</div>
													${user.rank_text ?? user.rank}
												</td>
												<td>
													<div class="mini-title">Verified:</div>
													${Number(user.verified) === 1 ? 'Yes' : 'No'}
												</td>
											</tr>
									</tbody>
								</table>
							`;
						}).join('')}
					</div>

					<div class="subsc-section">
						${
							hasPackage
								? `
									<div class="pack-card">
										<div class="overview-pack-img">
											<img src="images/sys-img/${pkg.package_image}" alt="Package Image">
										</div>
										<div class="overview-pack-name"><strong>${pkg.package_name}</strong></div>
										<div class="overview-pack-details">
											<ul>
												<li>Members: ${pkg.members_limit ? pkg.members_limit : 'undefinited'}</li>
												<li>Max admin: ${pkg.admins_limit ? pkg.admins_limit : 'undefinited'}</li>
												<li>Affiliate: ${pkg.branch_affiliate_limit ? pkg.branch_affiliate_limit : 'undefinited'}</li>
											</ul>
										</div>
										<div class="overview-pack-price">
											<strong>${priceText}</strong>
										</div>
									</div>
								`
								: `
									<div class="pack-card no-package">
										<div class="no-package-icon">📦 (Try Pack)</div>
										<div class="no-package-title">
											<strong>No active subscription</strong>
										</div>
										<div class="no-package-text">
											This user does not have an active package.
										</div>
									</div>
								`
						}
						<div class="subsc-info">
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline">
									<td colspan="6" align="center" valign="middle">
										<h3>Subscription Info</h3>
									</td>
								</tr>
								<tr valign="baseline">
									<td width="40%" align="left" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										<strong>Subscription Date</strong>
									</td>
									<td width="40%" align="left" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										<strong>Expiration Date</strong>
									</td>
									<td width="20%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										<strong>Price</strong>
									</td>
								</tr>
								<tr valign="baseline">
									<td width="40%" align="left" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										${formatFullDateTime(subs?.subscription_date) ?? '-'}
									</td>
									<td width="40%" align="left" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										${formatFullDateTime(subs?.expiration_date) ?? '-'}
									</td>
									<td width="20%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										$${subs?.estimated_cost ?? '-'}
									</td>
								</tr>
							</table>
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline">
									<td colspan="6" align="center" valign="middle" style="border-top: 1px solid var(--border-light);">
										<h3>Extra Pack</h3>
									</td>
								</tr>
								<tr valign="baseline">
									<td width="50%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										<strong>Name</strong>
									</td>
									<td width="50%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
										<strong>Access</strong>
									</td>
								</tr>
								${extraPack.length
									? extraPack.map(service => `
										<tr valign="baseline">
											<td width="50%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
												${service.service_name ?? '-'}
											</td>
											<td width="50%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
												${Number(service.can_access) === 1 ? 'Active' : 'Inactive'}
											</td>
										</tr>
									`).join('')
									: `
										<tr valign="baseline">
											<td width="50%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
												-
											</td>
											<td width="50%" align="center" valign="middle" style="height: 20px; border-top: 1px solid var(--border-light);">
												-
											</td>
										</tr>
									`
								}
							</table>
						</div>
					</div>

					<div class="affiliate-section">
						<h3>Affiliates (${affiliates.length})</h3>

						${
							affiliates.length
								? `
									<table class="overview-table" width="100%" cellspacing="0" cellpadding="5">
										<thead>
											<tr>
												<th>ID</th>
												<th>Logo</th>
												<th>Company</th>
												<th>Created</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											${affiliates.map(a => {
												const companyLogo = a.company_logo && a.company_logo.trim() !== ""
													? `images/company-logos/${a.company_logo}`
													: `images/sys-img/NonCompanyPic.png`;

												return `
													<tr>
														<td>${a.company_id}</td>
														<td>
															<div class="affiliate-profile">
																<img src="${companyLogo}" alt="">
															</div>
														</td>
														<td>${a.company_name}</td>
														<td>${formatFullDateTime(a.created_at) ?? '-'}</td>
														<td width="5%">
															<div class="overview-aff-menu" data-id="${a.company_id}">
																<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
															</div>
														</td>
													</tr>
												`;
											}).join('')}
										</tbody>
									</table>
								`
								: `<p class="no-data">No affiliates assigned.</p>`
						}
					</div>

					<div class="collab-section">
						<h3>Collaborators (${collaborators.length})</h3>

						${
							collaborators.length
								? `
									<table class="overview-table" width="100%" cellspacing="0" cellpadding="5">
										<thead>
											<tr>
												<th>ID</th>
												<th></th>
												<th>Name</th>
												<th>Email</th>
												<th>Rank</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											${collaborators.map(c => {
												const collaboratorImg = c.image && c.image.trim() !== ""
													? `images/profile/${c.image}`
													: `images/sys-img/NonProfilePic.png`;

												let borderColor = getUserBorderColor(c);

												return `
													<tr>
														<td>${c.user_id}</td>
														<td>
															<div class="customers-profile" style="border: 2px solid ${borderColor};">
																<img src="${collaboratorImg}" alt="">
															</div>
														</td>
														<td>${c.full_name}</td>
														<td>${c.email}</td>
														<td>${c.rank_text ?? c.rank}</td>
														<td width="5%">
															<div class="overview-collab-menu" data-id="${c.user_id}">
																<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
															</div>
														</td>
													</tr>
												`;
											}).join('')}
										</tbody>
									</table>
								`
								: `<p class="no-data">No collaborators assigned.</p>`
						}
					</div>
				`;
			}
			else if (sectionType === "service-rights") {
				html += `
					<div class="section-header">
						<table style="border-bottom:1px solid var(--clr-border); padding: 5px 0;" width="100%" cellspacing="0">
							<tr>
								<td width="75%" align="center" valign="middle"></td>
								<td width="25%" align="right" valign="middle">
									<button class="button-style-agree" id="add-right-btn">New Right</button>
								</td>
							</tr>
						</table>
					</div>
				`;

				if (result.success && Array.isArray(result.data) && result.data.length > 0) {
					html += `
						${result.data.map(right => {
							const date = new Date(right.created_at);
							const formattedDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;

							return`
								<div class="sys-admin-table">
									<table width="100%" align="center" cellspacing="0">
										<tr data-id="${right.service_id}" valign="baseline" class="form_height">
											<td width="70%" align="left" valign="middle" style="padding-left:15px;">
												${right.service_name}
											</td>
											<td width="10%" align="center" valign="middle">
												${right.can_access == 1 ? "Active" : "Inactive"}
											</td>
											<td width="15%" align="center" valign="middle">
												${formattedDate}
											</td>
											<td width="5%" align="center" valign="middle">
												<div class="rights-menu" data-id="${right.right_id}">
													<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
												</div>
											</td>
										</tr>
									</table>
								</div>
							`;
						}).join('')}
					`;
				} else {
					html += `
						<table width="100%" align="center" cellspacing="0">
							<tr valign="baseline" class="form_height">
								<td width="100%" align="center" valign="middle">
									<p style="color:gray; margin-top:10px;">No rights found for this user.</p>
								</td>
							</tr>
						</table>
					`;
				}
			}
			else if (sectionType === "extra-service") {
				html += `
					<div class="section-header">
						<table style="border-bottom:1px solid var(--clr-border); padding: 5px 0;" width="100%" cellspacing="0">
							<tr>
								<td width="75%" align="center" valign="middle"></td>
								<td width="25%" align="right" valign="middle">
									<button class="button-style-agree" id="add-service-btn">New Service</button>
								</td>
							</tr>
						</table>
					</div>
				`;

				if (result.success && Array.isArray(result.data) && result.data.length > 0) {
					html += `
						${result.data.map(service => {
							const date = new Date(service.created_at);
							const formattedDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;

							return`
								<div class="sys-admin-table">
									<table width="100%" align="center" cellspacing="0">
										<tr data-id="${service.service_id}" valign="baseline" class="form_height">
											<td width="50%" align="left" valign="middle" style="padding-left:15px;">
												${service.service_name}
											</td>
											<td width="20%" align="center" valign="middle">
												$ ${service.service_price}
											</td>
											<td width="10%" align="center" valign="middle">
												${service.status == 1 ? "Active" : "Inactive"}
											</td>
											<td width="15%" align="center" valign="middle">
												${formattedDate}
											</td>
											<td width="5%" align="center" valign="middle">
												<div class="extra-services-menu" data-id="${service.service_id}">
													<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
												</div>
											</td>
										</tr>
									</table>
								</div>
							`;
						}).join('')}
					`;
				} else {
					html += `
						<table width="100%" align="center" cellspacing="0">
							<tr valign="baseline" class="form_height">
								<td width="100%" align="center" valign="middle">
									<p style="color:gray; margin-top:10px;">No extra services found for this user.</p>
								</td>
							</tr>
						</table>
					`;
				}
			}
			else if (result.success) {
				html += `<pre>${JSON.stringify(result.data, null, 2)}</pre>`;
			}
			else {
				// Fallback para depuración o secciones futuras
				html += `<pre>${JSON.stringify(result.data, null, 2)}</pre>`;
			}

			detailsContainer.innerHTML = html;

			if (sectionType === "user-overview") {

			}
			else if (sectionType === "service-rights") {
				const addRightBtn = document.getElementById("add-right-btn");
				if (addRightBtn) {
					addRightBtn.addEventListener("click", async () => {
						scrollToTopIfNeeded();

						const selectedUserId = localStorage.getItem("selectedUserId");
						
						if (!selectedUserId) {
							console.error("⚠️ No user selected. Cannot create right.");
							alert("Please select a user first.");
							return;
						}

						const addRightsForm = document.getElementById('add-rights-form');
						const popupContent = addRightsForm.querySelector('.formular-frame');

						document.getElementById("right_user_id").value = selectedUserId;

						if (addRightsForm && popupContent) {
						    addRightsForm.style.display = 'block';
						    addRightsForm.style.opacity = '0';
						    addRightsForm.style.transition = 'opacity 0.5s ease';
						    setTimeout(() => {
						        addRightsForm.style.opacity = '1';
						    }, 10);

						    popupContent.style.transform = 'scale(0.7)';
						    popupContent.style.opacity = '0';
						    popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
						    setTimeout(() => {
						        popupContent.style.transform = 'scale(1)';
						        popupContent.style.opacity = '1';
						    }, 50);

							populateServicesRight("service_name");

							handlePopupClose("add-rights-form", ".formular-frame", []);
						}
					});
				}

				const rightsMenus = document.querySelectorAll('.rights-menu');
				rightsMenus.forEach(menu => {
					menu.addEventListener('click', (e) => {
						const rightId = e.currentTarget.dataset.id;
						openRightsMenu(rightId);

						handlePopupClose("rights-options", ".formular-frame", []);
					});
				});
			}
			else if (sectionType === "extra-service") {
				// 📌 script para add services popup
				const addServiceBtn = document.getElementById("add-service-btn");
				if (addServiceBtn) {
					addServiceBtn.addEventListener("click", async () => {
						scrollToTopIfNeeded();

						const selectedUserId = localStorage.getItem("selectedUserId");

						if (!selectedUserId) {
							console.error("⚠️ No user selected. Cannot create service.");
							alert("Please select a user first.");
							return;
						}

						const addServicesForm = document.getElementById('add-services-form');
						const popupContent = addServicesForm.querySelector('.formular-frame');

						document.getElementById("service_user_id").value = selectedUserId;

						if (addServicesForm && popupContent) {
						    addServicesForm.style.display = 'block';
						    addServicesForm.style.opacity = '0';
						    addServicesForm.style.transition = 'opacity 0.5s ease';
						    setTimeout(() => {
						        addServicesForm.style.opacity = '1';
						    }, 10);

						    popupContent.style.transform = 'scale(0.7)';
						    popupContent.style.opacity = '0';
						    popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
						    setTimeout(() => {
						        popupContent.style.transform = 'scale(1)';
						        popupContent.style.opacity = '1';
						    }, 50);

							handlePopupClose("add-services-form", ".formular-frame", []);
						}
					});
				}

				const extraServicesMenus = document.querySelectorAll('.extra-services-menu');
				extraServicesMenus.forEach(menu => {
					menu.addEventListener('click', (e) => {
						const serviceId = e.currentTarget.dataset.id;
						openExtraServicesMenu(serviceId);

						handlePopupClose("extra-services-options", ".formular-frame", []);
					});
				});
			}
		} catch (err) {
			console.error(`Error loading ${sectionTitle}:`, err);
			detailsContainer.innerHTML = `<p style="color:red;">Error loading ${sectionTitle}: ${err.message}</p>`;
		}
	}

	let formAddRights = document.getElementById('formAddRights');
	if (formAddRights) {
		formAddRights.addEventListener("submit", async function(e) {
			e.preventDefault();

			let formData = new FormData(this);
			let serviceName = formData.get('service_name').trim();
			let userId = formData.get('user_id');

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			// 🧩 Validaciones básicas
			if (!userId) {
				statusText.innerText = "Error: No user selected.";
				statusImage.src = "../images/sys-img/loading1.gif";
				showBanner(banner);
				return;
			}

			if (!serviceName) {
				statusText.innerText = "Error: Please enter the service name.";
				statusImage.src = "../images/sys-img/loading1.gif";
				showBanner(banner);
				return;
			}

			try {
				let response = await fetch('api/create_right.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message || "Right created successfully!";
					statusImage.src = data.img_gif || "../images/sys-img/loading1.gif";
					showBanner(banner);

					formAddRights.reset();
					document.getElementById('add-rights-form').style.display = 'none';

					setTimeout(() => {
						hideBanner(banner, () => {
							refreshSelectedUserView(); // 🔁 Recargar lista de rights
						});
					}, 2000);
				} else {
					statusText.innerText = "Error: " + (data.message || "Could not create right.");
					statusImage.src = data.img_gif || "../images/sys-img/loading1.gif";
					showBanner(banner);
				}
			} catch (error) {
				console.error("❌ Error in formAddRights:", error);
				statusText.innerText = "Error processing the request.";
				statusImage.src = "../images/sys-img/loading1.gif";
				showBanner(banner);
			}
		});
	}

	let formAddServices = document.getElementById('formAddServices');
	if (formAddServices) {
		formAddServices.addEventListener("submit", async function(e) {
			e.preventDefault();

			let formData = new FormData(this);
			let serviceName = formData.get('service_name').trim();
			let servicePrice = formData.get('service_price').trim();
			let userId = formData.get('user_id');

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			// 🧩 Validaciones básicas
			if (!userId) {
				statusText.innerText = "Error: No user selected.";
				statusImage.src = data.img_gif;
				showBanner(banner);
				return;
			}

			if (!serviceName || !servicePrice) {
				statusText.innerText = "Error: Please fill all fields.";
				statusImage.src = data.img_gif;
				showBanner(banner);
				return;
			}

			try {
				let response = await fetch('api/create_extra_service.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message || "Service created successfully!";
					statusImage.src = data.img_gif;
					showBanner(banner);

					formAddServices.reset();
					document.getElementById('add-services-form').style.display = 'none';

					setTimeout(() => {
						hideBanner(banner, () => {
							refreshSelectedUserView(); // 🔁 Recargar la lista de servicios
						});
					}, 2000);
				} else {
					statusText.innerText = "Error: " + (data.message || "Could not create service.");
					statusImage.src = data.img_gif;
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = data.img_gif;
				showBanner(banner);
			}
		});
	}

	async function openRightsMenu(rightId) {
		scrollToTopIfNeeded();
		
		const rightsOptions = document.getElementById('rights-options');
		const popupContent = rightsOptions.querySelector('.formular-frame');
		
		if (!rightId) return;

		try {
			const res = await fetch(`api/get_user_rights.php?right_id=${rightId}`);
			const data = await res.json();

			if (data.success && data.data.length > 0) {

			}

			if (rightsOptions && popupContent) {
				resetPopupView(['rights-menu-buttons'], [
					'edit-right-modal', 
					// 'assign-sale-section', 
					// 'edit-sales-modal'
				]);

				rightsOptions.style.display = 'block';
				rightsOptions.style.opacity = '0';
				rightsOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					rightsOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				// Botón: Edit Rights
				const editBtn = document.getElementById('editRightBtn');
				if (editBtn) {

					editBtn.setAttribute('data-right-id', rightId);

					editBtn.onclick = () => {
						const menuDiv = document.getElementById('rights-menu-buttons');
						const editDiv = document.getElementById('edit-right-modal');

						const rightId = editBtn.getAttribute('data-right-id');
						if (!rightId) return;

						openEditRightForm(rightId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					}
				}

				// Botón: Delete Rights
				const deleteBtn = document.getElementById('deleteRightBtn');
				if (deleteBtn) {
					deleteBtn.onclick = () => {
						deleteBtn.setAttribute('data-right-id', rightId);
						
						if (!rightId) {
							alert("Right ID not found.");
							return;
						}

						showConfirmModal("Delete Right", "Are you sure you want to delete this Right?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("right_id", rightId);
				
							try {
								const response = await fetch('api/delete_right.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(async () => {
										hideBanner(banner, async () => {
											await refreshSelectedUserView();
										});
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting product:", error);
								alert("Error deleting product. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading product info:", error);
		}
	}

	async function openEditRightForm(rightId) {
		const formEditRight = document.getElementById('formEditRight');
		if (!formEditRight) return;
	
		formEditRight.setAttribute('data-right-id', rightId);
	
		try {
			const response = await fetch(`api/get_user_rights.php?right_id=${rightId}`);
			const data = await response.json();
	
			if (data.success && data.data.length > 0) {
				const right = data.data.find(c => c.right_id == rightId);
				if (!right) return;
	
				// Llenar campos del formulario
				document.getElementById("edit_can_access").checked = right.can_access === "1" || right.can_access === 1;
	
				populateServicesRight("edit_service_name", right.service_name);

				// handlePopupClose("rights-options", ".formular-frame", []);
			}
		} catch (error) {
			console.error("Error loading right data:", error);
		}
	}

	let formEditRight = document.getElementById('formEditRight');
	if (formEditRight) {
		formEditRight.addEventListener("submit", async function(e) {
			e.preventDefault();

			let formData = new FormData(this);
			let rightId = formEditRight.getAttribute('data-right-id');
			formData.append('edit_right_id', rightId);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			if (!rightId) {
				console.error("⚠️ Missing right ID for update.");
				alert("No right selected for update.");
				return;
			}

			try {
				let response = await fetch('api/update_right.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "../images/sys-img/loading.gif";
					showBanner(banner);
				
					formEditRight.reset();
					document.getElementById('edit-right-modal').style.display = 'none';
					document.getElementById('rights-options').style.display = 'none';

					setTimeout(async () => {
						hideBanner(banner, async () => {
							await refreshSelectedUserView();
						});
					}, 2000);
				} else {
					statusText.innerText = "Error: " + (data.message || "Could not update right.");
					statusImage.src = data.img_gif || "../images/sys-img/error.gif";;
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error updating right.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	async function openExtraServicesMenu(serviceId) {
		scrollToTopIfNeeded();

		const extraServicesOptions = document.getElementById('extra-services-options');
		const popupContent = extraServicesOptions.querySelector('.formular-frame');
		
		if (!serviceId) return;

		try {
			const res = await fetch(`api/get_extra_services.php?service_id=${serviceId}`);
			const data = await res.json();

			if (data.success && data.data.length > 0) {

			}

			if (extraServicesOptions && popupContent) {
				resetPopupView(['extra-services-menu-buttons'], [
					'edit-extra-services-modal', 
					// 'assign-sale-section', 
					// 'edit-sales-modal'
				]);

				extraServicesOptions.style.display = 'block';
				extraServicesOptions.style.opacity = '0';
				extraServicesOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					extraServicesOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				// Botón: Edit Extra Services
				const editBtn = document.getElementById('editExtraServiceBtn');
				if (editBtn) {

					editBtn.setAttribute('data-extra-service-id', serviceId);

					editBtn.onclick = () => {
						const menuDiv = document.getElementById('extra-services-menu-buttons');
						const editDiv = document.getElementById('edit-extra-services-modal');

						const serviceId = editBtn.getAttribute('data-extra-service-id');
						if (!serviceId) return;

						openEditServiceForm(serviceId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					}
				}

				// Botón: Delete Rights
				const deleteBtn = document.getElementById('deleteExtraServiceBtn');
				if (deleteBtn) {
					deleteBtn.onclick = () => {
						deleteBtn.setAttribute('data-extra-service-id', serviceId);
						
						if (!serviceId) {
							alert("Service ID not found.");
							return;
						}

						showConfirmModal("Delete Extra Services", "Are you sure you want to delete this Extra Service?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("service_id", serviceId);
				
							try {
								const response = await fetch('api/delete_extra_service.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
				
								const banner = document.getElementById('status-message');
								const statusText = document.getElementById('status-text');
								const statusImage = document.getElementById('status-image');
				
								statusText.innerText = data.message;
								statusImage.src = data.img_gif;
								showBanner(banner);
				
								if (data.success) {
									setTimeout(async () => {
										hideBanner(banner, async () => {
											await refreshSelectedUserView();
										}, 1000);
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting service:", error);
								alert("Error deleting service. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading service info:", error);
		}
	}

	async function openEditServiceForm(serviceId) {
		const formEditExtraServices = document.getElementById('formEditExtraServices');
		if (!formEditExtraServices) return;
	
		formEditExtraServices.setAttribute('data-extra-service-id', serviceId);
	
		try {
			const response = await fetch(`api/get_extra_services.php?service_id=${serviceId}`);
			const data = await response.json();

			if (data.success && data.data.length > 0) {
				const services = data.data.find(c => c.service_id == serviceId);
				if (!serviceId) return;
	
				// Llenar campos del formulario
				document.getElementById("edit_extra_service_name").value = services.service_name || "";
				document.getElementById("edit_extra_service_price").value = services.service_price || "";
				document.getElementById("edit_service_status").checked = services.status === "1" || services.status === 1;
	
				// handlePopupClose("rights-options", ".formular-frame", []);
			}
		} catch (error) {
			console.error("Error loading services data:", error);
		}
	}

	let formEditExtraServices = document.getElementById('formEditExtraServices');
	if (formEditExtraServices) {
		formEditExtraServices.addEventListener("submit", async function(e) {
			e.preventDefault();

			let formData = new FormData(this);
			let serviceId = formEditExtraServices.getAttribute('data-extra-service-id');
			formData.append('edit_service_id', serviceId);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			if (!serviceId) {
				console.error("⚠️ Missing service ID for update.");
				alert("No right selected for update.");
				return;
			}

			try {
				let response = await fetch('api/update_extra_service.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "../images/sys-img/loading.gif";
					showBanner(banner);
				
					formEditExtraServices.reset();
					document.getElementById('edit-extra-services-modal').style.display = 'none';
					document.getElementById('extra-services-options').style.display = 'none';

					setTimeout(async () => {
						hideBanner(banner, async () => {
							await refreshSelectedUserView();
						});
					}, 2000);
				} else {
					statusText.innerText = "Error: " + (data.message || "Could not update service.");
					statusImage.src = data.img_gif || "../images/sys-img/error.gif";;
					showBanner(banner);
				}
			} catch (error) {
				statusText.innerText = "Error updating right.";
				statusImage.src = "../images/sys-img/error.gif";
				showBanner(banner);
			}
		});
	}

	async function refreshSelectedUserView() {
		const userId = localStorage.getItem("selectedUserId");
		const sectionType = localStorage.getItem("selectedSectionType");

		if (!userId || !sectionType) return;

		// Crear un evento ficticio para reusar handleUserSelect()
		const fakeEvent = {
			target: {
				getAttribute: () => userId
			}
		};

		await handleUserSelect(fakeEvent, sectionType);
	}

	setupBackToMenuButton(
		'.edit-back-to-right-menu-btn',
		['edit-right-modal'/*, 'assign-customers-sale-section'*/],
		'rights-menu-buttons',
		'rights-options'
	);
	setupBackToMenuButton(
		'.edit-back-to-services-menu-btn',
		['edit-extra-services-modal'],
		'extra-services-menu-buttons',
		'extra-services-options'
	);
	//############################################################# END SYS-ADMIN ##################################################################

	//############################################################# SETTINGS ##################################################################
	const settingsMenuItems = document.querySelectorAll(".settings-menu li");
	const settingsContent = document.getElementById("settings-content");

	const savedSettingSection = localStorage.getItem("activeSettingSection");

	settingsMenuItems.forEach(i => i.classList.remove("active"));

	settingsMenuItems.forEach(item => {
		item.addEventListener("click", async () => {
			const section = item.getAttribute("data-section");
			localStorage.setItem("activeSettingSection", section);

			settingsMenuItems.forEach(i => i.classList.remove("active"));
			item.classList.add("active");

			try {
				const response = await fetch(`settings/${section}.php`);
				const html = await response.text();
				settingsContent.innerHTML = html;

				if (section === "general") {
					loadCompanies('searchAffiliateField', 'affiliateTable', 'general');
				}

				if (section === "co-workers-rights") {
					loadCoWorkers('searchCoWorkerField', 'coWorkerTable', 'co-workers-rights');
				}
			} catch (err) {
				settingsContent.innerHTML = `<p style="color:red;">Error loading section: ${err.message}</p>`;
			}
		});

		if (savedSettingSection && item.getAttribute("data-section") === savedSettingSection) {
			item.classList.add("active");
			fetch(`settings/${savedSettingSection}.php`)
				.then(response => response.text())
				.then(html => {
					settingsContent.innerHTML = html;

					if (savedSettingSection === "general") {
						loadCompanies('searchAffiliateField', 'affiliateTable', 'general');
					}

					if (savedSettingSection === "co-workers-rights") {
						loadCoWorkers('searchCoWorkerField', 'coWorkerTable', 'co-workers-rights');
					}
				})
				.catch(err => {
					settingsContent.innerHTML = `<p style="color:red;">Error loading section: ${err.message}</p>`;
				});
		}
	});

	function loadCompanies(searchField, companyTable, sectionType = "general") {
		const searchCompanyField = document.getElementById(searchField);
		const companyListTable  = document.getElementById(companyTable);

		if (searchCompanyField && companyListTable) {

			async function fetchAndRenderCompanies(search = "") {
				try {
					const params = new URLSearchParams();

					if (search.trim() !== "") {
						params.append('search', search.trim());
					}

					const response = await fetch(`api/get_company_info.php?${params.toString()}`, {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});

					const data = await response.json();
					companyListTable.innerHTML = "";

					if (data.success && Array.isArray(data.data) && data.data.length > 0) {

						data.data.forEach(company => {
							const uniqueId = `company-${company.company_id}`;
							const row = document.createElement('tr');
							row.className = "company-row";

							const logoImg = company.company_logo && company.company_logo.trim() !== ""
								? `images/company-logos/${company.company_logo}`
								: `images/sys-img/NonCompanyPic.png`;

							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="affiliate-profile">
										<img src="${logoImg}" alt="">
									</div>
								</td>
								<td width="80%" valign="middle" style="padding-left:10px;">
									<strong>${company.company_name || 'Unknown company'}</strong>
									<p class="mini-title">${company.organization_no ?? ''}</p>
								</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-radio">
										<input type="radio" id="${uniqueId}" name="company_select" class="category-radio" data-id="${company.company_id}" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;

							// Click en la fila = seleccionar radio
							row.addEventListener("click", () => {
								const radio = row.querySelector('input[type="radio"]');
								if (!radio.disabled) {
									radio.checked = true;

									// Quitar selección previa
									document.querySelectorAll('.company-row').forEach(r => r.classList.remove('selected-company'));

									// Marcar selección actual
									row.classList.add('selected-company');

									handleSettingsSelect({ target: radio }, sectionType);
								}
							});

							companyListTable.appendChild(row);
						});

						// Listener directo en radios
						const radios = companyListTable.querySelectorAll('input[name="company_select"]');
						radios.forEach(radio => {
							radio.addEventListener("change", (e) => handleSettingsSelect(e, sectionType));
						});

					} else {
						companyListTable.innerHTML = `
							<tr>
								<td colspan="3" style="text-align:center; padding:10px;">
									No companies found.
								</td>
							</tr>
						`;
					}

				} catch (error) {
					console.error("Error loading companies:", error);
					companyListTable.innerHTML = `
						<tr>
							<td colspan="3" style="text-align:center; padding:10px;">
								Error loading companies.
							</td>
						</tr>
					`;
				}
			}

			// Buscar mientras se escribe
			searchCompanyField.addEventListener('input', () => {
				fetchAndRenderCompanies(searchCompanyField.value);
			});

			// Carga inicial
			fetchAndRenderCompanies();
		}
	}

	function loadCoWorkers(searchField, userTable, sectionType = "general") {
		const searchUserField = document.getElementById(searchField);
		const userListTable = document.getElementById(userTable);

		if (searchUserField && userListTable) {
			async function fetchAndRenderUsers(search = "") {
				try {
					const params = new URLSearchParams();
					if (search.trim() !== "") {
						params.append('search', search.trim());
					}

					const response = await fetch(`api/get_users.php?${params.toString()}`, {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});
					const data = await response.json();
					userListTable.innerHTML = "";

					if (data.success && Array.isArray(data.users) && data.users.length > 0) {
						data.users.forEach(user => {
							const uniqueId = `user-${user.user_id}`;
							const row = document.createElement('tr');
							row.className = "co-worker-row";

							let borderColor = Number(user.status) === 1 ? "#8cda8a" : "#fbadad";

							const profileImg = user.image && user.image.trim() !== ""
								? `images/profile/${user.image}`
								: `images/sys-img/NonProfilePic.png`;

							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="customers-profile" style="border: 2px solid ${borderColor};">
										<img src="${profileImg}" alt="">
									</div>
								</td>
								<td width="80%" valign="middle" style="padding-left:10px;">
									<strong>${user.full_name || 'Unknown'}</strong>
									<p class="mini-title">${user.email}</p>
								</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-radio">
										<input type="radio" id="${uniqueId}" name="co_worker_select" class="category-radio" data-id="${user.user_id}" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;

							row.addEventListener("click", () => {
								const radio = row.querySelector('input[type="radio"]');
								if (!radio.disabled) {
									radio.checked = true;

									// Quitar selección visual de las demás filas
									document.querySelectorAll('.co-worker-row').forEach(r => r.classList.remove('selected-co-worker'));

									// Agregar selección visual a esta fila
									row.classList.add('selected-co-worker');

									// Ejecutar el cambio como si se hubiera hecho clic directamente en el radio
									handleSettingsSelect({ target: radio }, sectionType);
								}
							});

							userListTable.appendChild(row);
						});

						// ✅ Escuchar clics en los radios
						const radios = userListTable.querySelectorAll('input[name="co_worker_select"]');
						radios.forEach(radio => {
							radio.addEventListener("change", (e) => handleSettingsSelect(e, sectionType));
						});
					} else {
						userListTable.innerHTML = `
							<tr>
								<td colspan="3" style="text-align:center; padding: 10px;">
									No customers found.
								</td>
							</tr>
						`;
					}
				} catch (error) {
					console.error("Error loading customers:", error);
					userListTable.innerHTML = `
						<tr>
							<td colspan="3" style="text-align:center; padding: 10px;">
								Error loading customers
							</td>
						</tr>
					`;
				}
			}

			searchUserField.addEventListener('input', () => {
				fetchAndRenderUsers(searchUserField.value);
			});

			fetchAndRenderUsers();
		}
	}

	async function handleSettingsSelect(e, sectionType) {
		const selectedId = e.target.getAttribute("data-id");

		localStorage.setItem("selectedUserId", selectedId);
		localStorage.setItem("selectedSectionType", sectionType);

		let detailsContainerId = "";
		switch (sectionType) {
			case "general":
				detailsContainerId = "general-details";
				break;
			case "co-workers-rights":
				detailsContainerId = "co-worker-details";
				break;
			default:
				console.warn("Unknown section type:", sectionType);
				return;
		}

		const detailsContainer = document.getElementById(detailsContainerId);
		if (!detailsContainer) {
			console.warn(`⚠️ Container #${detailsContainerId} not found.`);
			return;
		}

		let endpoint = "";
		let sectionTitle = "";

		// 🔹 Decide qué endpoint usar según la sección actual
		switch (sectionType) {
			case "general":
				endpoint = "api/get_general_config.php";
				sectionTitle = "General Overview";
				break;
			case "co-workers-rights":
				endpoint = "api/get_co_workers_rights.php";
				sectionTitle = "Co-Worker Rights";
				break;
			default:
				console.warn("No endpoint defined for section:", sectionType);
				return;
		}

		try {
			let result = null;

			if (sectionType === "general") {
				const res = await fetch(`${endpoint}?company_id=${selectedId}`);
				result = await res.json();
			}
			else if (sectionType === "co-workers-rights") {
				const res = await fetch(`${endpoint}?user_id=${selectedId}`);
				result = await res.json();
			}

			let html = '';
			let companyCurrency = "";

			if (sectionType === "general") {
				const data = result.data || {};

				companyCurrency = data.company_currency || "";
				const shippingKgPrice = data.shipping_kg_price || "";

				html += `
					<div class="general-form">
						<form method="post" name="formGeneralOverview" id="formGeneralOverview">
							<input type="hidden" name="company_id" id="company_id" value="${selectedId}">
						
							<table style="margin: 0px auto 50px" width="95%" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td colspan="6" style="border-bottom: 1px solid var(--clr-border);" align="center" valign="middle">
										<h2 style="margin: 20px 0 10px;">${sectionTitle}</h2>
									</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td colspan="6" style="border-bottom: 1px solid var(--clr-border);" align="center" valign="middle">
										<h4 style="margin-bottom: 10px;">Regional</h4>
									</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="left" valign="middle">
										<span style="display: block;">Company Currency</span>
									</td>
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="right" valign="middle">
										<select class="form-input-style" id="company_currency" name="company_currency"></select>
									</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="left" valign="middle">
										<span style="display: block;">Shipping Price/kg</span>
									</td>
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="right" valign="middle">
										<input class="form-input-style" type="text" name="shipping_kg_price" value="${shippingKgPrice}" placeholder="e.g., 5.00">
									</td>
								</tr>
							</table>
							<div class="access-rights-buttons" style="margin-top: 15px; text-align: center;">
								<button type="submit" class="button-style-agree" style="width: 150px;">Update Settings</button>
							</div>
						</form>
					</div>
				`;
			}
			else if (sectionType === "co-workers-rights") {
				const data = result.data || {};

				const hasShippingAccess = data?.shipping_access === true ? "checked" : "";
				const hasSaleAccess = data?.sale_access === true ? "checked" : "";
				const hasShippingStatusNotice = data?.shipping_status_notice === true ? "checked" : "";

				html += `
					<div class="access-rights-form">
						<form method="post" name="formAddAccessRights" id="formAddAccessRights">
							<input type="hidden" name="user_id" id="user_id" value="${selectedId}">

							<table style="margin: 0px auto 50px" width="95%" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td colspan="6" style="border-bottom: 1px solid var(--clr-border);" align="center" valign="middle">
										<h2 style="margin: 20px 0 10px;">${sectionTitle}</h2>
									</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="left" valign="middle">
										<span style="display: block;">Shipping Access</span>
									</td>
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="right" valign="middle">
										<label class="switch">
											<input type="checkbox" name="shipping_access" value="1" ${hasShippingAccess}>
											<span class="slider round"></span>
										</label>
									</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="left" valign="middle">
										<span style="display: block;">Sale Access</span>
									</td>
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="right" valign="middle">
										<label class="switch">
											<input type="checkbox" name="sale_access" value="1" ${hasSaleAccess}>
											<span class="slider round"></span>
										</label>
									</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td colspan="6" style="border-bottom: 1px solid var(--clr-border);" align="center" valign="middle">
										<h4 style="margin-bottom: 10px;">Mobile</h4>
									</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="left" valign="middle">
										<span style="display: block;">Shipping Status Notice</span>
									</td>
									<td width="50%" style="border-bottom: 1px solid var(--clr-light-border); padding: 5px 10px;" align="right" valign="middle">
										<label class="switch">
											<input type="checkbox" name="shipping_status_notice" value="1" ${hasShippingStatusNotice}>
											<span class="slider round"></span>
										</label>
									</td>
								</tr>
							</table>
							<div class="access-rights-buttons" style="margin-top: 15px; text-align: center;">
								<button type="submit" class="button-style-agree" style="width: 150px;">Update Rights</button>
							</div>
						</form>
					</div>
				`;
			}
			else {
				// Fallback para depuración o secciones futuras
				html += `<pre>${JSON.stringify(result.data, null, 2)}</pre>`;
			}

			detailsContainer.innerHTML = html;

			if (sectionType === "general") {
				await populateCurrencies('company_currency', companyCurrency);
			}

			switch (sectionType) {
				case "general":
					attachGeneralSettingsFormHandler();
					break;

				case "co-workers-rights":
					attachAccessRightsFormHandler();
					break;
			}
		} catch (err) {
			console.error(`Error loading ${sectionTitle}:`, err);
			detailsContainer.innerHTML = `<p style="color:red;">Error loading ${sectionTitle}: ${err.message}</p>`;
		}
	}

	function attachGeneralSettingsFormHandler() {
		const form = document.getElementById('formGeneralOverview');
		if (!form) return;

		form.addEventListener("submit", async function (e) {
			e.preventDefault();

			const formData = new FormData(form);
			const companyId = formData.get('company_id');

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			if (!companyId) {
				statusText.innerText = "Error: No company selected.";
				statusImage.src = "../images/sys-img/loading1.gif";
				showBanner(banner);
				return;
			}

			try {
				const response = await fetch('api/update_general_config.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				const data = await response.json();

				if (data.success) {
					statusText.innerText = data.message || "Settings updated successfully.";
					statusImage.src = data.img_gif || "../images/sys-img/loading1.gif";
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, async () => {
							await refreshSelectedGeneralView();
						});
					}, 2000);
				} else {
					statusText.innerText = data.message || "Error updating settings.";
					statusImage.src = "../images/sys-img/loading1.gif";
					showBanner(banner);
				}
			} catch (err) {
				statusText.innerText = "Error processing the request.";
				statusImage.src = "../images/sys-img/loading1.gif";
				showBanner(banner);
			}
		});
	}

	async function refreshSelectedGeneralView() {
		const companyId = localStorage.getItem("selectedUserId");
		const sectionType = "general";

		if (!companyId) return;

		const fakeEvent = {
			target: {
				getAttribute: () => companyId
			}
		};

		await handleSettingsSelect(fakeEvent, sectionType);
	}


	function attachAccessRightsFormHandler() {
		let formAddAccessRights = document.getElementById('formAddAccessRights');
		if (!formAddAccessRights) return;

		formAddAccessRights.addEventListener("submit", async function(e) {
			e.preventDefault();

			let formData = new FormData(this);
			
			let userId = formData.get('user_id');

			let permissionCheckboxes = formAddAccessRights.querySelectorAll('input[type="checkbox"]');

			permissionCheckboxes.forEach(checkbox => {
				let name = checkbox.name;
				let value = checkbox.checked ? "1" : "0";
				formData.set(name, value);
			});

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			if (!userId) {
				statusText.innerText = "Error: No user selected.";
				statusImage.src = "../images/sys-img/loading1.gif";
				showBanner(banner);
				return;
			}

			try {
				let response = await fetch('api/update_co_workers_rights.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();

				if (data.success) {
					statusText.innerText = data.message || "Right created successfully!";
					statusImage.src = data.img_gif || "../images/sys-img/loading1.gif";
					showBanner(banner);

					setTimeout(() => {
						hideBanner(banner, async () => {
							await refreshSelectedCoWorkerView();
						});
					}, 2000);
				} else {
					statusText.innerText = "Error: " + (data.message || "Could not create right.");
					statusImage.src = data.img_gif || "../images/sys-img/loading1.gif";
					showBanner(banner);
				}
			} catch (error) {
				console.error("❌ Error in formAddRights:", error);
				statusText.innerText = "Error processing the request.";
				statusImage.src = "../images/sys-img/loading1.gif";
				showBanner(banner);
			}
		});
	}

	async function refreshSelectedCoWorkerView() {
		const userId = localStorage.getItem("selectedUserId");
		const sectionType = localStorage.getItem("selectedSectionType");

		if (!userId || !sectionType) return;

		// Crear un evento ficticio para reusar handleSettingsSelect()
		const fakeEvent = {
			target: {
				getAttribute: () => userId
			}
		};

		await handleSettingsSelect(fakeEvent, sectionType);
	}
	//############################################################# END SETTINGS ##################################################################

	//############################################################# DIRECT MESSAGE ##################################################################
	// 📌 script para direct message popup
	let startDirectMessageBtn = document.getElementById('startDirectMessage');
	if (startDirectMessageBtn) {
		startDirectMessageBtn.addEventListener('click', async function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const editCompanyForm = document.getElementById('add-direct-message-form');
			const popupContent = editCompanyForm.querySelector('.formular-frame');

			if (editCompanyForm && popupContent) {
				editCompanyForm.style.display = 'block';
				editCompanyForm.style.opacity = '0';
				editCompanyForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					editCompanyForm.style.opacity = '1';
				}, 10);

				popupContent.style.transform = 'scale(0.7)';
				popupContent.style.opacity = '0';
				popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
			
				initDMUsersList();

				handlePopupClose("add-direct-message-form", ".formular-frame", []);
			}
		});
	}

	window.selectedDMUserId = null;
	function initDMUsersList() {
		const searchUserInput = document.getElementById('search-dm-users');
		const userListTable = document.getElementById('select-dm-users-list');
		const CURRENT_USER_ID = window.currentUserId || null;

		if (!searchUserInput || !userListTable) return;

		async function fetchAndRenderUsers(search = "") {
			try {
				const params = new URLSearchParams();
				params.append('include_parent', '1');

				if (search.trim() !== "") {
					params.append('search', search.trim());
				}

				const response = await fetch(`api/get_users.php?${params.toString()}`, {
					headers: { 'Accept': 'application/json' }
				});

				const data = await response.json();
				userListTable.innerHTML = "";

				if (data.success && Array.isArray(data.users) && data.users.length > 0) {
					data.users.forEach(user => {

						if (CURRENT_USER_ID && Number(user.user_id) === Number(CURRENT_USER_ID)) return;

						const row = document.createElement('tr');
						row.className = "dm-user-row";

						const profileImg = user.image
							? `images/profile/${user.image}`
							: `images/sys-img/NonProfilePic.png`;

						row.innerHTML = `
							<td width="10%" align="center">
								<div class="message-user-profile-pic">
									<img src="${profileImg}">
								</div>
							</td>
							<td width="80%" valign="middle" style="padding-left:10px;">
								<P><strong>${user.full_name || user.username}</strong></P>
								<p>${user.email || ''}</p>
							</td>
							<td width="10%" align="center">
								<input type="radio" name="user_select" data-id="${user.user_id}">
							</td>
						`;

						row.addEventListener('click', () => {
							document.querySelectorAll('.dm-user-row')
								.forEach(r => r.classList.remove('selected-dm-user'));
							row.classList.add('selected-dm-user');

							window.selectedDMUserId = Number(user.user_id);
							// console.log("Selected DM User ID:", window.selectedDMUserId);
						});

						userListTable.appendChild(row);
					});
				} else {
					userListTable.innerHTML = `
						<tr><td colspan="3" align="center">No users found</td></tr>
					`;
				}
			} catch (e) {
				console.error("Error loading DM users:", e);
			}
		}

		searchUserInput.addEventListener('input', () => {
			fetchAndRenderUsers(searchUserInput.value);
		});

		fetchAndRenderUsers();
	}

	const dmForm = document.getElementById('formDirectMessage');
	if (dmForm) {
		dmForm.addEventListener('submit', function (e) {
			e.preventDefault();

			if (!window.selectedDMUserId) {
				alert("Please select a user first.");
				return;
			}

			const targetUserId = window.selectedDMUserId;

			// Cerrar popup
			const popup = document.getElementById('add-direct-message-form');
			if (popup) popup.style.display = 'none';

			// 👉 Inicializar vista de chat
			(async () => {
				try {
					const res = await fetch('api/get_notifications.php', {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});

					const data = await res.json();
					if (!data.success) throw new Error('Cannot load notifications');

					// 🔎 Buscar chat existente
					const existingChat = data.data.find(n =>
						n.notification_type === 'Direct Message' &&
						(
							Number(n.from_user_id) === targetUserId ||
							Number(n.to_user_id) === targetUserId
						)
					);

					if (existingChat) {
						// ✅ Chat ya existe → reutilizar
						localStorage.setItem('activeDMUserId', targetUserId);
						localStorage.setItem('activeDMNotificationId', existingChat.notification_id);
						localStorage.setItem('dmAutoSelect', '1');
					} else {
						// 🆕 Chat nuevo → sin notification_id aún
						localStorage.setItem('activeDMUserId', targetUserId);
						localStorage.removeItem('activeDMNotificationId');
						localStorage.setItem('dmAutoSelect', '1');
					}

					if (typeof window.fetchAndRenderNotifications === 'function') {
						await window.fetchAndRenderNotifications();
					}

					openDirectMessageChat();

				} catch (e) {
					console.error('Error resolving DM chat:', e);
				} finally {
					window.selectedDMUserId = null;
				}
			})();
		});
	}

	async function openDirectMessageChat() {
		const toUserId = Number(localStorage.getItem('activeDMUserId'));
		const notifId  = Number(localStorage.getItem('activeDMNotificationId')) || null;

		if (!toUserId) return;

		const prevChatUser = Number(localStorage.getItem('activeDMChatUserId'));

		if (prevChatUser !== toUserId) {
			localStorage.removeItem('activeChatId');
			localStorage.setItem('activeDMChatUserId', toUserId);
		}

		const detailsDiv = document.getElementById('notifications-details');
		if (!detailsDiv) return;

		// Vista base (sin nombre aún)
		detailsDiv.innerHTML = `
		<div>
			<table class="message-details" width="90%" align="center" cellspacing="0" style="margin-top:15px;">
				<tr valign="baseline" id="dm-header">
					<td colspan="3" style="border-bottom: 1px solid #ccc; padding:10px;">
						<strong>Loading...</strong>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="3" align="center" valign="middle">
						<div class="message-content-box" id="dm-messages-box">
							<p style="opacity:.6; text-align:center;">Start the conversation</p>
						</div>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="3" align="center" valign="middle">
						<div class="message-reply-box">
							<input 
								type="text"
								id="replyMessageField"
								class="form-input-style"
								placeholder="Message..."
							/>
							<button id="sendMessageBtn" class="button-style-agree" style="margin-left:10px; width: 80px;">Send</button>
						</div>
					</td>
				</tr>
			</table>
		</div>
		`;

		attachDMInputHandler();

		try {
			const res = await fetch(`api/get_notifications.php`, {
				headers: { 'Accept': 'application/json' }
			});

			const data = await res.json();
			if (!data.success) return;

			let resolvedNotifId = notifId;
	
			const header = document.getElementById('dm-header');
			if (!header) return;

			// 🆕 CHAT NUEVO (sin notificación aún)
			if (!resolvedNotifId) {
				const userRes = await fetch(`api/get_users.php?id=${toUserId}`, {
					headers: { 'Accept': 'application/json' }
				});
				const userData = await userRes.json();

				if (userData.success && userData.user) {
					const u = userData.user;

					const img = u.image
						? `images/profile/${u.image}`
						: `images/sys-img/NonProfilePic.png`;

					header.innerHTML = `
						<td width="5%" align="center" valign="middle" style="border-bottom: 1px solid #ccc;">
							<div class="message-user-profile-pic">
								<img src="${img}" alt="Profile Pic">
							</div>
						</td>
						<td width="45%" align="left" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0; padding-left: 10px;">
							<strong>${u.full_name || u.username}</strong>
						</td>
						<td width="50%" align="right" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0;"></td>
					`;
				} else {
					// fallback mínimo si no hay preview
					header.innerHTML = `
						<td colspan="3" style="border-bottom:1px solid #ccc; padding:10px;">
							<strong>Direct Message</strong>
						</td>
					`;
				}
			}

			const notif = Array.isArray(data.data)
				? data.data.find(n => Number(n.notification_id) === Number(resolvedNotifId))
				: null;

			if (!notif) {
				console.warn(
					// '[DM] Notification not found, fallback to chat_id from storage',
					{ notifId, activeChatId: localStorage.getItem('activeChatId') }
				);
			}

			if (notif) {
				header.innerHTML = `
					<td width="5%" align="center" valign="middle" style="border-bottom: 1px solid #ccc;">
						<div class="message-user-profile-pic">
							<img src="${notif.from_user_image}" alt="Profile Pic">
						</div>
					</td>
					<td width="45%" align="left" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0; padding-left: 10px;">
						<strong>${notif.from_user_name}</strong>
					</td>
					<td width="50%" align="right" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0;"></td>
				`;
			}

			// 🔹 Cargar historial del chat (si existe chat_id)
			let chatId = null;

			// 1️⃣ Prioridad: notification_link
			if (notif && notif.notification_link) {
				chatId = Number(notif.notification_link);
				localStorage.setItem('activeChatId', chatId);
			}

			// 2️⃣ Fallback: localStorage
			if (!chatId) {
				chatId = Number(localStorage.getItem('activeChatId')) || null;
			}

			try {
				const historyRes = await fetch(
					`api/get_chat_messages.php?chat_id=${chatId}`,
					{ headers: { 'Accept': 'application/json' } }
				);

				const history = await historyRes.json();
				// console.log('DM History:', history);
				if (history.success && Array.isArray(history.messages)) {
					renderDMHistory(history.messages);

					updateReadStatus(chatId);

					if (!window.dmReadInterval) {
						window.dmReadInterval = setInterval(() => {
							const activeChatId = Number(localStorage.getItem('activeChatId'));
							if (activeChatId) {
								updateReadStatus(activeChatId);
							}
						}, 5000);
					}
				}
			} catch (e) {
				console.error('Error loading chat history:', e);
			}

		} catch (e) {
			console.error('Error loading DM chat:', e);
		}
	}
	window.openDirectMessageChat = openDirectMessageChat;

	async function sendDMMessage() {
		const input = document.getElementById('replyMessageField');
		const messagesBox = document.getElementById('dm-messages-box');

		if (!input || !messagesBox) return;

		const message = input.value.trim();
		if (!message) return;

		const chatId   = Number(localStorage.getItem('activeChatId')) || null;
		const toUserId = Number(localStorage.getItem('activeDMUserId'));
		if (!toUserId) return;

		// UI optimista
		const tempMsg = document.createElement('div');
		tempMsg.className = 'dm-message dm-me pending';
		tempMsg.dataset.createdAt = new Date().toISOString();
		tempMsg.innerHTML = `
			<div class="dm-text">${message}</div>
			<div class="dm-meta">
				<span class="dm-time">${formatTime(new Date())}</span>
				<span class="dm-status">✔</span>
			</div>
		`;
		messagesBox.appendChild(tempMsg);
		messagesBox.scrollTop = messagesBox.scrollHeight;

		input.value = '';

		try {
			const formData = new FormData();
			if (chatId > 0) formData.append('chat_id', chatId);
			formData.append('to_user_id', toUserId);
			formData.append('message', message);

			const res = await fetch('api/create_chat_message.php', {
				method: 'POST',
				headers: { 'Accept': 'application/json' },
				body: formData
			});

			const data = await res.json();

			if (!data.success) {
				tempMsg.classList.remove('pending');
				tempMsg.classList.add('error');
				tempMsg.querySelector('.dm-status').innerText = '❌';
				return;
			}

			tempMsg.classList.remove('pending');
			tempMsg.classList.add('confirmed');
			tempMsg.dataset.messageId = data.message_id;

			const status = tempMsg.querySelector('.dm-status');
			if (status) status.innerText = '✔✔';

			if (!chatId && data.chat_id) {
				localStorage.setItem('activeChatId', data.chat_id);
			}

			if (typeof window.fetchAndRenderNotifications === 'function') {
				await window.fetchAndRenderNotifications();
			}
		} catch (err) {
			tempMsg.classList.remove('pending');
			tempMsg.classList.add('error');
			tempMsg.innerText = '❌ Error sending message';
			console.error(err);
		}
	}

	function attachDMInputHandler() {
		const input = document.getElementById('replyMessageField');
		const sendBtn = document.getElementById('sendMessageBtn');

		if (!input) return;

		if (input.dataset.bound === '1') return;
		input.dataset.bound = '1';

		// ⌨️ Enter
		input.addEventListener('keydown', (e) => {
			if (e.key === 'Enter') {
				e.preventDefault();
				sendDMMessage();
			}
		});

		// 🖱️ Botón Send
		if (sendBtn) {
			sendBtn.addEventListener('click', (e) => {
				e.preventDefault();
				sendDMMessage();
			});
		}
	}

	function renderDMHistory(messages) {
		const box = document.getElementById('dm-messages-box');
		if (!box) return;

		box.innerHTML = "";

		const me = Number(window.currentUserId);
		let lastDay = null;

		messages.forEach(m => {
			const msgDate = new Date(m.created_at);
			const dayKey = msgDate.toDateString();

			// 🗓️ Separador de fecha
			if (dayKey !== lastDay) {
				const dayDivider = document.createElement('div');
				dayDivider.className = 'dm-day-divider';
				dayDivider.innerText = formatDayLabel(m.created_at);
				box.appendChild(dayDivider);
				lastDay = dayKey;
			}

			// 💬 Mensaje
			const msg = document.createElement('div');
			const isMine = Number(m.from_user_id) === me;

			msg.className = `dm-message ${isMine ? 'dm-me' : 'dm-other'}`;

			msg.dataset.createdAt = m.created_at;
			msg.innerHTML = `
				<div class="dm-text">${m.message}</div>
				<div class="dm-meta">
					<span class="dm-time">${formatTime(m.created_at)}</span>
					${isMine ? `
						<span class="dm-status ${m.is_read ? 'read' : ''}">
							${m.is_read ? '✔✔' : '✔'}
						</span>` : ``}
				</div>
			`;

			box.appendChild(msg);
		});

		box.scrollTop = box.scrollHeight;
	}


	window.DM = window.DM || {};
	
	window.DM.refreshChat = async function (chatId) {
		if (!chatId) return;

		try {
			const res = await fetch(`api/get_chat_messages.php?chat_id=${chatId}`, {
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();

			if (!data.success || !Array.isArray(data.messages)) return;

			renderDMHistory(data.messages);
			updateReadStatus(chatId);

		} catch (e) {
			console.error('[DM] refreshChat error:', e);
		}
	};

	function formatTime(dateStr) {
		const d = new Date(dateStr);
		return d.toLocaleTimeString([], {
			hour: '2-digit',
			minute: '2-digit'
		});
	}

	function formatDayLabel(dateStr) {
		const d = new Date(dateStr);
		const today = new Date();
		const yesterday = new Date();
		yesterday.setDate(today.getDate() - 1);

		const sameDay = (a, b) =>
			a.getFullYear() === b.getFullYear() &&
			a.getMonth() === b.getMonth() &&
			a.getDate() === b.getDate();

		if (sameDay(d, today)) return 'Today';
		if (sameDay(d, yesterday)) return 'Yesterday';

		return d.toLocaleDateString();
	}

	function updateReadStatus(chatId) {
		fetch(`api/get_chat_read_status.php?chat_id=${chatId}`)
			.then(res => res.json())
			.then(data => {
				if (!data.success || !data.last_read_at) return;

				const readAt = new Date(data.last_read_at);

				document.querySelectorAll('.dm-message.dm-me').forEach(msg => {
					const msgTime = new Date(msg.dataset.createdAt);
					const status = msg.querySelector('.dm-status');

					if (!status) return;

					if (msgTime <= readAt) {
						status.innerText = '✔✔';
						status.classList.add('read'); // azul
					}
				});
			});
	}
	//############################################################# END DIRECT MESSAGE ##################################################################

	//############################################################# SEND EMAIL ##################################################################
	const contactForm = document.getElementById('contactForm');
	if (contactForm) {
		contactForm.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);

			const banner = document.getElementById('status-message');
			const statusText = document.getElementById('status-text');
			const statusImage = document.getElementById('status-image');

			try {
				const response = await fetch('api/send_email.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				if (response.status === 204 || response.status === 429) {
					statusText.innerText = "Please try again later.";
					statusImage.src = '../images/sys-img/error.gif';
					showBanner(banner);
					return;
				}

				const data = await response.json();

				statusText.innerText = data.message;
				statusImage.src = data.img_gif || '';
				showBanner(banner);

				if (data.success) {
					setTimeout(() => {
						hideBanner(banner, () => {
							contactForm.reset();
							banner.style.display = 'none';

							const contactBox = document.getElementById('contactBox');
							const form = contactBox.querySelector('form');

							// Remover clase expandido
							contactBox.classList.remove('expanded');

							// Ocultar formulario
							if (form) {
								form.style.display = 'none';
								form.style.opacity = 0;
							}

							// Restaurar imagen
							if (!contactBox.querySelector('img')) {
								const img = document.createElement('img');
								img.src = '../images/sys-img/email.gif';
								img.alt = 'e-mail';
								img.style.width = '140%';
								img.style.height = '140%';
								img.style.borderRadius = '50%';
								img.style.objectFit = 'cover';
								img.style.marginBottom = '5px';
								contactBox.prepend(img);
							}
						});
					}, 1000);
				}
			} catch (error) {
				console.error("Error sending email:", error);
			}
		});
	}
	//############################################################# END SEND EMAIL ##################################################################

	//############################################################# FUNCTIONES ##################################################################
	async function bindSubmitToCheckboxes({
		checkboxIds = [],
		submitId,
		ghostClass = "button-ghost",
	}) {
		const submitBtn = document.getElementById(submitId);
		const checkboxes = checkboxIds
			.map((id) => document.getElementById(id))
			.filter(Boolean);

		if (!submitBtn || checkboxes.length !== checkboxIds.length) return;

		function update() {
			const enabled = checkboxes.every((cb) => cb.checked);
			submitBtn.disabled = !enabled;

			if (enabled) submitBtn.classList.remove(ghostClass);
			else submitBtn.classList.add(ghostClass);
		}

		checkboxes.forEach((cb) => cb.addEventListener("change", update));
		update();
	};
	
	// 📌 scroll to top 
	function scrollToTopIfNeeded() {
		if (window.scrollY > 0) {
			window.scrollTo({
				top: 0,
				behavior: 'smooth'
			});
		}
	}

	function clearFields(ids) {
		ids.forEach(id => {
			const el = document.getElementById(id);
			if (el) el.value = '';
		});
	}

	function animateHeightChange(container, sectionToShow, callback) {
		const startHeight = container.offsetHeight + 'px';
		container.style.height = startHeight;
	
		// Ocultar sección destino antes de mostrarla
		sectionToShow.style.display = 'block';
		sectionToShow.style.opacity = '0';
		sectionToShow.style.visibility = 'hidden';
	
		// Realizar cambios (esconder lo anterior, mostrar lo nuevo)
		if (callback) callback();
	
		requestAnimationFrame(() => {
			const desiredHeight = container.scrollHeight;
			const maxHeight = window.innerHeight * 0.9;
			const endHeight = Math.min(desiredHeight, maxHeight) + 'px';
			container.style.height = endHeight;
	
			container.addEventListener('transitionend', function handler() {
				container.style.height = 'auto';
				// Mostrar suavemente la sección nueva después del estiramiento
				sectionToShow.style.visibility = 'visible';
				sectionToShow.style.transition = 'opacity 0.2s ease';
				sectionToShow.style.opacity = '1';
	
				container.removeEventListener('transitionend', handler);
			});
		});
	}

	// 📌 cerrar al hacer clic fuera del formulario
	function handlePopupClose(popupId, contentSelector, otherPopups = []) {
		const popup = document.getElementById(popupId);
		if (!popup) return;

		const content = popup.querySelector(contentSelector);
		if (!content) return;

		const isVisible = (el) => !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));

		// Evita listeners duplicados si re-llamas esta función
		if (popup._outsideHandler) {
			document.removeEventListener('click', popup._outsideHandler, true);
			popup._outsideHandler = null;
		}

		const handler = (e) => {
			if (!isVisible(popup)) return;

			const mini = document.getElementById('create-type-form');
			if (mini && isVisible(mini) && mini.contains(e.target)) {
				return;
			}

			const clickDentroContenido = content.contains(e.target);
			if (clickDentroContenido) return; // si clickeas dentro, mantenemos el listener

			// Cerrar
			popup.style.display = 'none';
			otherPopups.forEach(id => {
				const other = document.getElementById(id);
				if (other) other.style.display = 'none';
			});

			// Limpieza: ya no necesitamos seguir escuchando
			document.removeEventListener('click', handler, true);
			popup._outsideHandler = null;
		};

		popup._outsideHandler = handler;

		// Deja que termine de abrir/transicionar antes de enganchar el listener
		setTimeout(() => {
			document.addEventListener('click', handler, { capture: true }); // ← sin once
		}, 0);
	}

	function parseDbTimestamp(s) {
		// intenta ISO-like
		const d = new Date(s.replace(' ', 'T')); // "2025-07-18T13:05:11.000"
		if (!isNaN(d)) return d;

		// fallback manual: "YYYY-MM-DD HH:mm:ss.fff"
		const [datePart, timePart='00:00:00'] = s.split(' ');
		const [y, m, d2] = datePart.split('-').map(Number);
		const [h, min, secMs] = timePart.split(':');
		const [s2, ms='0'] = (secMs || '0').split('.');
		return new Date(y, (m-1), d2, Number(h||0), Number(min||0), Number(s2||0), Number(ms||0));
	}

	// 📌 script para recojer los datos de la compania
	async function loadCompanyFormOrData(selectedCompanyId = undefined) {
		if (!isNaN(selectedCompanyId)) {
			try {
				let response = await fetch(`api/get_company_info.php?select_company=${selectedCompanyId}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				let data = await response.json();
				
				if (data.success && data.data && data.data.length > 0) {
					let company = data.data[0];

					originalCompanyData = {
						company_name: company.company_name || '',
						organization_no: company.organization_no || '',
						company_address: company.company_address || '',
						company_phone: company.company_phone || '',
						country_code: company.country_code || ''
					};

					document.getElementById('company_id').value = company.company_id;
					document.getElementById('company_name').value = originalCompanyData.company_name || '';
					document.getElementById('organization_no').value = originalCompanyData.organization_no || '';
					document.getElementById('company_address').value = originalCompanyData.company_address || '';
					document.getElementById('company_phone').value = originalCompanyData.company_phone || '';
					document.getElementById('company_country_code').value = originalCompanyData.country_code || '';

					const selectedKeyFromDB = originalCompanyData.country_code || '';
					await populateCountryPhoneCodes('company_country_code', 'company_phone', selectedKeyFromDB);

					const logoPreview = document.getElementById('logo-preview');
					if (logoPreview) {
						if (company.company_logo && company.company_logo.trim() !== "") {
							logoPreview.src = `images/company-logos/${company.company_logo}`;
							logoPreview.style.display = 'block';
							logoPreview.style.visibility = 'visible';
							logoPreview.style.opacity = '1';
						}
					}
				}
			} catch (error) {
				console.error("Error loading company data:", error);
			}
		} else {
			// 🧹 Si no se pasa ID válido, limpiamos los campos
			originalCompanyData = {
				company_name: '',
				organization_no: '',
				company_address: '',
				company_phone: ''
			};

			document.getElementById('company_id').value = '';
			document.getElementById('company_name').value = '';
			document.getElementById('organization_no').value = '';
			document.getElementById('company_address').value = '';
			document.getElementById('company_phone').value = '';

			await populateCountryPhoneCodes('company_country_code', 'company_phone');

			initImagePreview('company_logo', 'logo-preview').then((isImage) => {
				if (!isImage) {
					const logoPreview = document.getElementById('logo-preview');
					if (logoPreview) {
						logoPreview.src = '';
						logoPreview.style.display = 'none';
						logoPreview.style.visibility = 'hidden';
						logoPreview.style.opacity = '0';
					}
				}
			});
		}
	}

	// 📌 script para recojer los datos de los slot
	async function loadSlotFormOrData(selectedSlotId = undefined) {
		if (!isNaN(selectedSlotId)) {
			try {
				let response = await fetch(`api/get_slot_info.php?select_slot=${selectedSlotId}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				let data = await response.json();
				
				if (data.success && data.data && data.data.length > 0) {
					let slot = data.data[0];

					originalSlotData = {
						slot_name: slot.slot_name || '',
						max_capacity: slot.max_capacity || '',
						current_capacity: slot.current_capacity || '',
						slot_description: slot.slot_description || '',
						status: slot.status || 0
					};

					document.getElementById('slot_id').value = slot.slot_id;
					document.getElementById('slot_name').value = originalSlotData.slot_name || '';
					document.getElementById('max_capacity').value = originalSlotData.max_capacity || '';
					document.getElementById('current_capacity').value = originalSlotData.current_capacity || '';
					document.getElementById('slot_description').value = originalSlotData.slot_description || '';
					document.getElementById("slot_status").checked = String(originalSlotData.status) === "1";
				}
			} catch (error) {
				console.error("Error loading company data:", error);
			}
		} else {
			// 🧹 Si no se pasa ID válido, limpiamos los campos
			originalSlotData = {
				slot_name: '',
				max_capacity: '',
				current_capacity: '',
				slot_description: '',
				status: 0
			};

			document.getElementById('slot_id').value = '';
			document.getElementById('slot_name').value = '';
			document.getElementById('max_capacity').value = '';
			document.getElementById('current_capacity').value = '';
			document.getElementById('slot_description').value = '';
			document.getElementById("slot_status").checked = true;
		}
	}

	// 📌 script para recojer los productos seleccionados
	async function syncProductsFromSelectedSlot(slotId) {
		if (!slotId) {
			await initProductList({
				listId: 'products-list',
				searchId: 'input-search-product',
				checkName: 'products_info[]'
			});
			return;
		}

		try {
			const params = new URLSearchParams();
			params.append('slot_id', slotId);

			const response = await fetch(`api/get_storages.php?${params.toString()}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});

			const data = await response.json();

			if (!data.success || !data.data) {
				await initProductList({
					listId: 'products-list',
					searchId: 'input-search-product',
					checkName: 'products_info[]'
				});
				return;
			}

			const payload = Array.isArray(data.data) ? data.data[0] : data.data;
			const storages = payload?.storages || [];

			const selectedProductIds = [...new Set(
				storages
					.map(storage => storage.product_id)
					.filter(v => v !== null && v !== undefined && v !== '')
					.map(String)
			)];

			await initProductList({
				listId: 'products-list',
				searchId: 'input-search-product',
				checkName: 'products_info[]',
				selectedProductIds
			});

			updateStorageActionButtonState();
		} catch (error) {
			console.error('Error syncing products from slot:', error);
		}
	}

	function initImagePreview(inputFileId, previewImgId, onChangeCallback = null) {
		return new Promise((resolve) => {
			const fileInput = document.getElementById(inputFileId);
			const previewImage = document.getElementById(previewImgId);

			if (!fileInput || !previewImage) {
				resolve(false);
				return;
			}

			// Almacena el último archivo cargado para comparar cambios
			let lastFileName = fileInput.files?.[0]?.name || '';

			const processFile = (file) => {
				if (file && file.type.startsWith('image/')) {
					const reader = new FileReader();
					reader.onload = function (e) {
						previewImage.src = e.target.result;
						previewImage.style.display = 'block';
						previewImage.style.opacity = '1';

						if (file.name !== lastFileName && typeof onChangeCallback === 'function') {
							onChangeCallback(); // 🔁 Notifica el cambio de imagen
						}

						lastFileName = file.name;
						resolve(true);
					};
					reader.readAsDataURL(file);
				} else {
					resolve(false);
				}
			};

			// Detecta si ya había una imagen cargada
			if (fileInput.files && fileInput.files[0]) {
				processFile(fileInput.files[0]);
			}

			// Detecta cambios futuros del input
			fileInput.addEventListener('change', () => {
				if (fileInput.files && fileInput.files[0]) {
					processFile(fileInput.files[0]);
				} else {
					resolve(false);
				}
			}, { once: true });
		});
	}

	async function populateCustomerTypes(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select Customer Type';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch('api/get_global_array.php?key=customerTypes');
			const data = await res.json();
	
			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No customer types found</option>`;
			}
		} catch (error) {
			console.error("Error loading customer types:", error);
			select.innerHTML += `<option value="">Error loading customer types</option>`;
		}
	}

	async function populateProductPurpose(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select Product Purposes';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch('api/get_global_array.php?key=productPurpose');
			const data = await res.json();
	
			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No product purposes found</option>`;
			}
		} catch (error) {
			console.error("Error loading product purposes:", error);
			select.innerHTML += `<option value="">Error loading product purposes</option>`;
		}
	}

	async function populateServicesRight(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;

		select.innerHTML = '';

		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select Service Right';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_global_array.php?key=serviceRights');
			const data = await res.json();

			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;

					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}

					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No service rights found</option>`;
			}
		} catch (error) {
			console.error("Error loading service rights:", error);
			select.innerHTML += `<option value="">Error loading service rights</option>`;
		}
	}

	async function populateProductTypes(selectId, selectedValue = '', companyId = '', withCreate = true) {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		// 🔹 Limpiar contenido actual del <select>
		select.innerHTML = '';
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Type';
		select.appendChild(defaultOption);
	
		try {
			const params = new URLSearchParams();
    		if (companyId) params.append('select_company', companyId);

			const res = await fetch(`api/get_product_type.php${params.toString() ? `?${params}` : ''}`, {
				headers: { 'Accept': 'application/json' },
				credentials: 'same-origin'
			});
	
			const ct   = res.headers.get('content-type') || '';
			const raw  = await res.text();

			// si no viene JSON, log y error claro
			if (!ct.includes('application/json')) {
				console.error('[get_product_type] Content-Type:', ct);
				console.error('[get_product_type] Raw (primeros 400 chars):', raw.slice(0, 400));
				throw new Error('Respuesta no JSON del servidor');
			}

			const data = JSON.parse(raw);
			if (!res.ok) {
				throw new Error(data?.message || `HTTP ${res.status}`);
			}

			// Tu API: { success, count, data:[ {product_type_id, product_type_name, ...}, ... ] }
			if (data.success && Array.isArray(data.data) && data.data.length) {
				for (const row of data.data) {
					const value = String(row.product_type_id);
					const label = row.product_type_name || `Type ${value}`;
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) option.selected = true;
					select.appendChild(option);
				}
			} else {
				const emptyOpt = document.createElement('option');
				emptyOpt.value = '';
				emptyOpt.textContent = 'No types found';
				select.appendChild(emptyOpt);
			}
		} catch (error) {
			console.error('Error loading product types:', error);
			const errOpt = document.createElement('option');
			errOpt.value = '';
			errOpt.textContent = 'Error loading types';
			select.appendChild(errOpt);
		}

		// (Opcional) Opción "create type" al final, si la usas SIEMPRE (y sin duplicar)
  		if (withCreate && !Array.from(select.options).some(o => o.value === '+')) {
			const plusOpt = document.createElement('option');
			plusOpt.value = '+';
			plusOpt.textContent = '+ create new type...';
			select.appendChild(plusOpt);
		}

		if (!select.dataset.createBound) {
			bindCreateTypeMiniForm({
				select,
				companyId,
				apiUrl: 'api/create_product_type.php',   // <-- ajusta si usas otra ruta
				formId: 'create-type-form',
				inputId: 'new-type-name',
				saveId: 'save-type-btn',
				cancelId: 'cancel-type-btn',
				msgId: 'create-type-msg'
			});
			select.dataset.createBound = '1';
		}
	}

	function bindCreateTypeMiniForm({ 
		select, companyId = '', apiUrl, formId, inputId, saveId, cancelId, msgId 
	}) {
		const formBox	= document.getElementById(formId);          // overlay
		const modalBox	= formBox?.querySelector('.delete-modal-box'); // caja interna
		const input		= document.getElementById(inputId);
		const saveBtn	= document.getElementById(saveId);
		const cancelBtn	= document.getElementById(cancelId);
		const msgEl		= document.getElementById(msgId);

		if (!formBox || !modalBox || !input || !saveBtn || !cancelBtn || !msgEl) {
			console.warn('[create-type] Falta algún elemento del mini-form.');
			return;
		}

		// Evita doble binding si llamas varias veces a bindCreateTypeMiniForm
		if (formBox.dataset.bound === '1') return;
		formBox.dataset.bound = '1';

		const setLoading = (b) => {
			saveBtn.disabled = b;
			cancelBtn.disabled = b;
			input.disabled = b;
		};

		// --- Bloquear propagación dentro del modal ---
		// Clics dentro de la caja NO burbujean hacia el overlay/documento
		formBox.addEventListener('click', (e) => {
			e.stopPropagation();
		});

		modalBox.addEventListener('click', (e) => {
			e.stopPropagation();
		});

		const showForm = () => {
			msgEl.textContent = '';
			msgEl.style.color = '#c00';
			input.value = '';
			formBox.style.display = 'flex';
			setTimeout(() => input.focus(), 0);
			select.disabled = true;
		};

		const hideForm = () => {
			formBox.style.display = 'none';
			msgEl.textContent = '';
			select.disabled = false;
		};

		let prevValue = select.value || '';

		// Clic en el overlay (fuera de la caja) -> cerrar SOLO este overlay
		formBox.addEventListener('click', (e) => {
			// Si haces click fuera de modalBox, cierra solo este
			if (e.target === formBox) {
				e.preventDefault();
				hideForm();
				select.value = prevValue;
			}
		});

		formBox.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				e.preventDefault();
				hideForm();
				select.value = prevValue;
			}
		});

		// Abrir cuando elijan "+"
		select.addEventListener('change', (e) => {
			const v = e.target.value;
			if (v === '+') {
				select.value = prevValue;
				setTimeout(showForm, 0);
			} else {
				prevValue = v;
				hideForm();
			}
		});

		// Guardar
		const doSave = async () => {
			const name = (input.value || '').trim();
			if (!name) {
				msgEl.textContent = 'Please enter a name.';
				input.focus();
				return;
			}
			setLoading(true);
			msgEl.style.color = '#666';
			msgEl.textContent = 'Saving…';

			try {
				const payload = { name };
				if (companyId) payload.company_id = companyId;

				const res = await fetch(apiUrl, {
					method: 'POST',
					headers: { 'Accept':'application/json','Content-Type':'application/json' },
					credentials: 'same-origin',
					body: JSON.stringify(payload)
				});

				const ct  = res.headers.get('content-type') || '';
				const raw = await res.text();
				if (!ct.includes('application/json')) {
					console.error('[create_product_type] Raw:', raw.slice(0, 400));
					throw new Error('Respuesta no JSON del servidor');
				}

				const data = JSON.parse(raw);
				if (!res.ok || !data.success || !data.id) {
					throw new Error(data?.message || 'Request failed');
				}

				// Insertar/seleccionar el nuevo option (antes del "+")
				const newVal   = String(data.id);
				const newLabel = data.name || name;
				let opt = Array.from(select.options).find(o => o.value === newVal);
				if (!opt) {
					opt = document.createElement('option');
					opt.value = newVal;
					opt.textContent = newLabel;
					const plusOpt = Array.from(select.options).find(o => o.value === '+');
					select.insertBefore(opt, plusOpt || null);
				} else {
					opt.textContent = newLabel;
				}
				select.value = newVal;
				prevValue = newVal;

				select.dispatchEvent(new Event('change', { bubbles: true }));

				hideForm(); // <-- solo cierra este overlay
			} catch (err) {
				console.error(err);
				msgEl.style.color = '#c00';
				msgEl.textContent = err.message || 'Could not save. Try again.';
				select.value = prevValue;
			} finally {
				setLoading(false);
			}
		};

		// Botón Save: impedir que un handler global cierre todo
		saveBtn.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			doSave();
		});

		// Enter/Escape en el input
		input.addEventListener('keydown', (e) => {
			if (e.key === 'Enter') {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				doSave();
			} else if (e.key === 'Escape') {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				hideForm();
				select.value = prevValue;
			}
		});

		// Botón Cancel: cerrar solo este
		cancelBtn.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			hideForm();
			select.value = prevValue;
		});
	}

	async function populateCountryPhoneCodes(selectId, phoneInputId, selectedValue = '') {
		const select = document.getElementById(selectId);
		const phoneInput = document.getElementById(phoneInputId);
		if (!select || !phoneInput) return;

		// Limpia el select
		select.innerHTML = '';

		// Opción por defecto
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Country Code';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_global_array.php?key=countryPhoneCodes');
			const data = await res.json();

			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = `${label} (${value.split('|')[1]})`;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
						setPhonePrefix(phoneInput, value.split('|')[1], true);
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No country codes found</option>`;
			}
		} catch (error) {
			console.error("Error loading country codes:", error);
			select.innerHTML += `<option value="">Error loading country codes</option>`;
		}

		// Evento para actualizar prefijo al cambiar el país
		select.addEventListener('change', () => {
			const selected = select.value;
			const prefix = selected ? selected.split('|')[1] : '';
			setPhonePrefix(phoneInput, prefix, true);
		});
	}

	function setPhonePrefix(input, prefix, preserveExisting = true) {
		if (!prefix) {
			if (input._prefixHandler) {
			input.removeEventListener('input', input._prefixHandler);
			input._prefixHandler = null;
			}
			input.value = '';
			input.readOnly = false;
			return;
		}

		const base = prefix + ' ';

		if (input._prefixHandler) {
			input.removeEventListener('input', input._prefixHandler);
			input._prefixHandler = null;
		}

		let numberPart = '';
		if (preserveExisting && input.value) {
			const normalized = String(input.value).replace(/[^\d+]/g, '');
			if (normalized.startsWith(prefix)) {
				numberPart = normalized.slice(prefix.length).replace(/[^0-9]/g, '');
			} else if (normalized.startsWith('+')) {
				numberPart = normalized.replace(/^\+\d+/, '').replace(/[^0-9]/g, '');
			} else {
				numberPart = normalized.replace(/[^0-9]/g, '');
			}
		}

		numberPart = numberPart.replace(/^0+/, '');

		input.value = base + numberPart;
		input.readOnly = false;

		const handler = () => {
			if (!input.value.startsWith(base)) {
				input.value = base + '';
				input.setSelectionRange(input.value.length, input.value.length);
				return;
			}
			
			let np = input.value.slice(base.length).replace(/[^0-9]/g, '');
			np = np.replace(/^0+/, '');
			input.value = base + np;
			input.setSelectionRange(input.value.length, input.value.length);
		};

		input._prefixHandler = handler;
		input.addEventListener('input', handler);

		input.setSelectionRange(input.value.length, input.value.length);
	}


	// Función para llenar el <select> con los roles de usuario
	async function populateRankSelect(selectId, selectedValue = '', minRoleId = 1) {
		const select = document.getElementById(selectId);
		if (!select) return;

		select.innerHTML = '';

		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select user role';
		select.appendChild(defaultOption);

		try {
			const response = await fetch('api/get_roles.php');
			const data = await response.json();

			if (!data.success) {
				console.error("Failed to fetch roles:", data.message);
				return;
			}

			const roles = data.data;
			for (const [value, label] of Object.entries(roles)) {
				if (parseInt(value) >= parseInt(minRoleId)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			}
		} catch (error) {
			console.error("Error fetching roles:", error);
		}
	}

	async function populateCurrencies(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;

		select.innerHTML = '';

		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select Currency';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_global_array.php?key=currencies');
			const data = await res.json();

			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No currencies found</option>`;
			}
		} catch (error) {
			console.error("Error loading currencies:", error);
			select.innerHTML += `<option value="">Error loading currencies</option>`;
		}
	}

	async function populatePaymentMethods(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;

		select.innerHTML = '';

		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select Payment Method';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_global_array.php?key=paymentMethods');
			const data = await res.json();

			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No payment methods found</option>`;
			}
		} catch (error) {
			console.error("Error loading payment methods:", error);
			select.innerHTML += `<option value="">Error loading payment methods</option>`;
		}
	}


	const DISABLED_PACKAGES = [7];

	async function populatePackages(containerId, selectedValue = '') {
		const packageList = document.getElementById(containerId);
		if (!packageList) return;

		packageList.innerHTML = ''; // limpia el contenedor

		let currentPackId = 0;

		// 1. Obtener el paquete actual del usuario
		try {
			const currentRes = await fetch('api/get_current_package.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const currentData = await currentRes.json();
			if (currentData.success && currentData.current_pack) {
				currentPackId = parseInt(currentData.current_pack);
			}
		} catch (err) {
			console.warn("The current package could not be loaded:", err);
		}

		// 2. Cargar los paquetes disponibles
		try {
			const response = await fetch('api/get_packages.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});

			const data = await response.json();

			if (data.success && data.packages.length > 0) {
				data.packages.forEach(pkg => {
					const uniqueId = `package-${pkg.package_id}`;
					const pkgId = parseInt(pkg.package_id);
					const isDisabledByConfig = DISABLED_PACKAGES.includes(pkgId);
					const isAvailable = pkgId > currentPackId && !isDisabledByConfig;

					const container = document.createElement('div');
					container.className = 'packages';
					container.dataset.radioId = uniqueId;

					if (!isAvailable) {
						container.style.pointerEvents = 'none';

						if (isDisabledByConfig) {
							container.title = 'This package is temporarily unavailable';
							container.style.opacity = '0.7';
							container.style.backgroundColor = '#5cb2cfff';
						} else {
							container.style.opacity = '0.3';
							container.style.backgroundColor = '#f2f2f2'; // estilo visual para desactivado
						}
					}

					const priceText = isDisabledByConfig
						? 'Custom plan' 
						: (pkg.package_price != null ? `$ ${pkg.package_price}` : 'free');

					container.innerHTML = `
						<div class="pack-img">
							<img src="images/sys-img/${pkg.package_image}" alt="Package Image">
						</div>
						<div class="pack-name"><strong>${pkg.package_name}</strong></div>
						<div class="pack-details">
							<ul>
								<li>${window.i18n?.memmbers || "Members"}: ${pkg.members_limit ? pkg.members_limit : 'undefinited'}</li>
								<li>Max admin: ${pkg.admins_limit ? pkg.admins_limit : 'undefinited'}</li>
								<li>${window.i18n?.branches || "Branches"}: ${pkg.branch_affiliate_limit ? pkg.branch_affiliate_limit : 'undefinited'}</li>
							</ul>
						</div>
						<div class="pack-price">
							<strong>${priceText}</strong>
						</div>
						<div class="opcion-radio">
							<input type="radio" id="${uniqueId}" name="packs" class="category-radio"
								value="${pkg.package_id}"
								${String(pkg.package_id) === String(selectedValue) ? 'checked' : ''}
								${!isAvailable ? 'disabled' : ''}
							/>
							<label for="${uniqueId}"></label>
						</div>
					`;

					// Si este es el seleccionado, aplicamos estilo visual
					if (String(pkg.package_id) === String(selectedValue)) {
						container.classList.add('selected-package');
					}

					// Asignar evento al div para seleccionar el radio
					container.addEventListener('click', () => {
						const radio = container.querySelector('input[type="radio"]');
						if (!radio.disabled) {
							radio.checked = true;

							// Desmarcar todos los paquetes
							document.querySelectorAll('.packages').forEach(p => p.classList.remove('selected-package'));

							// Marcar este paquete
							container.classList.add('selected-package');

							// Actualizar costo estimado
							updateEstimatedCost();
						}
					});

					packageList.appendChild(container);
				});

				assignPackageListeners();
			} else {
				packageList.innerHTML = '<p>No packages found.</p>';
			}
		} catch (error) {
			console.error("Error loading packages:", error);
			packageList.innerHTML = '<p>Error loading packages.</p>';
		}
	}

	async function populateExtraServices(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;

		select.style.display = "block";
		select.innerHTML = '';

		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = window.i18n?.select_extra_service || 'Select Extra Service';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_extra_services.php?status=1');
			const data = await res.json();

			if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
				select.style.display = "none";
				return;
			}

			data.data.forEach(service => {
				const opt = document.createElement('option');

				// ✅ CAMBIO #1: value = service_id (no el precio)
				opt.value = String(service.service_id);

				// ✅ CAMBIO #2: texto visible con nombre + precio
				const price = service.service_price != null ? parseFloat(service.service_price) : 0;
				opt.dataset.price = String(price);

				opt.textContent = `${service.service_name} (+$${price.toFixed(2)})`;

				if (String(opt.value) === String(selectedValue)) {
					opt.selected = true;
				}

				select.appendChild(opt);
			});

		} catch (error) {
			console.error("Error loading extra services:", error);
			select.style.display = "none";
		}
	}

	// 📌 recoje el valor del select del formulario subscripcion
	let estimated = document.getElementById('estimated');
	let estimatedInput = document.getElementById('estimated_cost');
	let extraPackSelect = document.getElementById('extra_pack');
	let packUpdateBtn = document.getElementById('packUpgradeBtn');
	
	async function updateEstimatedCost() {
		const selectedRadio = document.querySelector('input[name="packs"]:checked');
		const currentPackageInput = document.getElementById('current_package_id');
		const currentPackageId = currentPackageInput
			? parseInt(currentPackageInput.value, 10)
			: 0;

		if (!estimated || !estimatedInput) return;

		const selectedOpt = extraPackSelect?.selectedOptions?.[0] || null;
		const extraValue = selectedOpt
			? (Number.parseFloat(selectedOpt.dataset.price || 0) || 0)
			: 0;

		const selectedValue = selectedRadio
			? parseInt(selectedRadio.value, 10)
			: currentPackageId;

		// Valor visual: solo lo que el usuario cambió
		const visualHasNewPack = !!selectedRadio;
		const visualBaseValue = visualHasNewPack
			? parseInt(selectedRadio.value, 10)
			: (extraValue > 0 ? currentPackageId : 0);

		if (!selectedValue) {
			const visualCost = extraValue.toFixed(2);

			estimated.innerHTML = `${window.i18n?.estimated_cost}: <strong>$ ${visualCost}</strong>`;
			estimatedInput.value = extraValue.toFixed(2);

			if (packUpdateBtn) {
				if (extraValue > 0) {
					packUpdateBtn.classList.remove('disabled');
				} else {
					packUpdateBtn.classList.add('disabled');
				}
			}
			return;
		}

		try {
			const res = await fetch('api/get_packages.php');
			const data = await res.json();

			if (data.success && Array.isArray(data.packages)) {
				const realPkg = data.packages.find(
					p => parseInt(p.package_id, 10) === selectedValue
				);

				if (realPkg) {
					const realBaseCost = Number.parseFloat(realPkg.package_price ?? 0) || 0;
					const realTotalCost = (realBaseCost + extraValue).toFixed(2);

					// estimatedInput conserva el valor real
					estimatedInput.value = realTotalCost;

					let visualCost = extraValue;

					if (visualBaseValue > 0) {
						const visualPkg = data.packages.find(
							p => parseInt(p.package_id, 10) === visualBaseValue
						);

						if (visualPkg) {
							const visualBaseCost = Number.parseFloat(visualPkg.package_price ?? 0) || 0;
							visualCost = visualBaseCost + extraValue;
						}
					}

					// Solo visual
					estimated.innerHTML = `${window.i18n?.estimated_cost}: <strong>$ ${visualCost.toFixed(2)}</strong>`;

					if (packUpdateBtn) {
						if (visualHasNewPack || extraValue > 0) {
							packUpdateBtn.classList.remove('disabled');
						} else {
							packUpdateBtn.classList.add('disabled');
						}
					}
				} else {
					estimated.innerHTML = `${window.i18n?.estimated_cost}: <strong>$ 0.00</strong>`;
					estimatedInput.value = 0;
					if (packUpdateBtn) {
						packUpdateBtn.classList.add('disabled');
					}
				}
			} else {
				estimated.innerHTML = `${window.i18n?.estimated_cost}: <strong>$ 0.00</strong>`;
				estimatedInput.value = 0;
				if (packUpdateBtn) {
					packUpdateBtn.classList.add('disabled');
				}
			}
		} catch (error) {
			console.error("Error loading package data:", error);
			estimated.innerHTML = `${window.i18n?.estimated_cost}: <strong>$ 0.00</strong>`;
			estimatedInput.value = 0;
			if (packUpdateBtn) {
				packUpdateBtn.classList.add('disabled');
			}
		}
	}

	// 📌 Asigna el evento a todos los radios de paquetes
	function assignPackageListeners() {
		document.querySelectorAll('input[name="packs"]').forEach(radio => {
			radio.addEventListener('change', updateEstimatedCost);
		});
	}

	if (extraPackSelect) {
		extraPackSelect.addEventListener('change', updateEstimatedCost);
	}

	// Drag & Drop + click
	function initDragAndDrop(dropAreaId, inputFileId, previewImgId = null) {
		const dropArea = document.getElementById(dropAreaId);
		const fileInput = document.getElementById(inputFileId);
		const previewImage = previewImgId ? document.getElementById(previewImgId) : null;
	
		if (!dropArea || !fileInput) return;
	
		// Al hacer clic en el área se dispara el input
		dropArea.addEventListener('click', () => fileInput.click());
	
		// Drag events
		dropArea.addEventListener('dragenter', (e) => {
			e.preventDefault();
			dropArea.classList.add('active');
		});
		dropArea.addEventListener('dragleave', () => dropArea.classList.remove('active'));
		dropArea.addEventListener('dragover', (e) => e.preventDefault());
	
		// Drop file
		dropArea.addEventListener('drop', (e) => {
			e.preventDefault();
			dropArea.classList.remove('active');
			const files = e.dataTransfer.files;
			fileInput.files = files;
	
			if (previewImage && files && files[0]) {
				const reader = new FileReader();
				reader.onload = function (e) {
					previewImage.src = e.target.result;
					previewImage.style.display = 'block';
					previewImage.style.opacity = 1;
				};
				reader.readAsDataURL(files[0]);
			}
		});
	
		// Input change
		fileInput.addEventListener('change', () => {
			if (previewImage && fileInput.files && fileInput.files[0]) {
				const reader = new FileReader();
				reader.onload = function (e) {
					previewImage.src = e.target.result;
					previewImage.style.display = 'block';
					previewImage.style.opacity = 1;
				};
				reader.readAsDataURL(fileInput.files[0]);
			}
		});
	}

	function showConfirmModal(title, message, onConfirm) {
		const modal = document.getElementById('globalConfirmModal');
		const modalTitle = document.getElementById('confirm-modal-title');
		const modalMessage = document.getElementById('confirm-modal-message');
		const cancelBtn = document.getElementById('modalCancelBtn');
		const confirmBtn = document.getElementById('modalConfirmBtn');

		if (!modal || !modalTitle || !modalMessage || !cancelBtn || !confirmBtn) return;

		modalTitle.textContent = title || "Confirm Action";
		modalMessage.textContent = message || "Are you sure you want to proceed?";
		modal.style.display = 'flex';

		// Reset listeners
		const newConfirmBtn = confirmBtn.cloneNode(true);
		confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

		// Confirmación
		newConfirmBtn.addEventListener('click', () => {
			modal.style.display = 'none';
			if (typeof onConfirm === 'function') onConfirm();
		});

		// Cancelar
		cancelBtn.onclick = () => {
			modal.style.display = 'none';
		};
	}

	function showAlertModal(title, message) {
		const modal = document.getElementById('globalOkModal');
		const modalTitle = document.getElementById('alert-modal-title');
		const modalMessage = document.getElementById('alert-modal-message');
		const confirmBtn = document.getElementById('modalOkBtn');
	
		if (!modal || !modalTitle || !modalMessage || !confirmBtn) return;
	
		modalTitle.textContent = title || "Notice";
		modalMessage.textContent = message || "";
	
		modal.style.display = 'flex';
	
		// Clonar y reemplazar para quitar listeners previos
		const newConfirmBtn = confirmBtn.cloneNode(true);
		confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
	
		newConfirmBtn.textContent = 'OK';
		newConfirmBtn.onclick = () => {
			modal.style.display = 'none';
		};
	}

	function setupBackToMenuButton(buttonSelector, divsToHide = [], menuDivId = '', optionsDivId = '') {
		const buttons = document.querySelectorAll(buttonSelector);
		if (!buttons.length) return;
	
		buttons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
	
				const menuDiv = document.getElementById(menuDivId);
				const optionsDiv = optionsDivId ? document.getElementById(optionsDivId) : null;
	
				let anyDivShown = false;
	
				divsToHide.forEach(divId => {
					const div = document.getElementById(divId);
					if (div && div.style.display === 'block') {
						anyDivShown = true;
						fadeOutAndHide(div, () => {
							if (menuDiv) showWithFadeIn(menuDiv);
						});
					}
				});
	
				if (!anyDivShown && menuDiv) {
					showWithFadeIn(menuDiv);
				}
	
				if (optionsDiv) {
					optionsDiv.style.display = 'block';
				}

				const formFrame = document.getElementById('formular-frame');
				if (formFrame) {
					formFrame.classList.remove('expanded');
				}

				const formFrame2 = document.getElementById('formular-frame-2');
				if (formFrame2) {
					formFrame2.classList.remove('expanded');
				}

				const mediumFormFrame = document.getElementById('formular-medium-frame');
				if (mediumFormFrame) {
					mediumFormFrame.classList.remove('expanded-medium');
				}

				const mediumFormFrame2 = document.getElementById('formular-medium-frame-2');
				if (mediumFormFrame2) {
					mediumFormFrame2.classList.remove('expanded-medium');
				}
			});
		});
	}

	function fadeOutAndHide(element, callback) {
		element.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
		element.style.opacity = '1';
		element.style.transform = 'scale(1)';
	
		setTimeout(() => {
			element.style.opacity = '0';
			element.style.transform = 'scale(0.8)';

			setTimeout(() => {
				element.style.display = 'none';
				element.style.removeProperty('opacity');
				element.style.removeProperty('transform');
				element.style.removeProperty('transition');
				element.style.removeProperty('height');
				if (callback) callback();
			}, 400);
		}, 10);
	}
	
	function showWithFadeIn(element) {
		if (!element) return;

		element.style.removeProperty('opacity');
		element.style.removeProperty('transform');
		element.style.removeProperty('transition');

		element.style.display = 'block';
		element.style.opacity = '0';
		element.style.transform = 'scale(0.8)';

		void element.offsetWidth;
		element.getBoundingClientRect();

		requestAnimationFrame(() => {
			element.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
			element.style.opacity = '1';
			element.style.transform = 'scale(1)';
		});
	}

	function activateTab(activeTab, inactiveTab, showSection, hideSection) {
		activeTab.classList.add('tab-active');
		inactiveTab.classList.remove('tab-active');

		showSection.style.display = 'block';
		hideSection.style.display = 'none';
	}

	document.querySelectorAll('.input-year-only').forEach(input => {
		input.addEventListener('input', () => {
			let year = input.value.replace(/\D/g, ''); // eliminar todo lo que no sea número
			if (year.length > 4) year = year.slice(0, 4); // limitar a 4 dígitos
			input.value = year;
		});
	});

	function resetPopupView(menuIds = [], sectionIdsToHide = []) {
		const allFrames = document.querySelectorAll('.formular-frame, .formular-big-frame, .formular-medium-frame');
		
		allFrames.forEach(frame => {
			frame.classList.remove('expanded', 'expanded-medium');
		});

		menuIds.forEach(menuId => {
			const menuDiv = document.getElementById(menuId);
			if (menuDiv) {
				menuDiv.style.display = 'block';
				menuDiv.style.opacity = '1';
				menuDiv.style.transform = 'scale(1)';
			}
		});

		sectionIdsToHide.forEach(sectionId => {
			const section = document.getElementById(sectionId);
			if (section) {
				section.style.display = 'none';
				section.style.opacity = '0';
				section.style.transform = 'scale(0.8)';
			}
		});
	}

	async function loadMarksForSearch(saleMarkSelectId) {
		try {
			const response = await fetch("api/get_categories.php", {
				method: "GET",
				headers: { "Accept": "application/json" }
			});
			const data = await response.json();

			saleMarkSelectId.innerHTML = `<option value="">All Marks</option>`;

			if (data.success && data.data.length > 0) {
				data.data.forEach(category => {
					const option = document.createElement("option");
					option.value = category.category_id;
					option.textContent = category.category_name;
					saleMarkSelectId.appendChild(option);
				});
			} else {
				saleMarkSelectId.innerHTML += `<option value="">${window.i18n?.no_marks_found || 'No marks found'}</option>`;
			}
		} catch (error) {
			console.error("Error loading marks:", error);
			saleMarkSelectId.innerHTML = `<option value="">Error loading marks</option>`;
		}
	}


	// LOGICA DE CAMBIO DE MONEDAS CON CACHÉ EN LOCALSTORAGE
	let exchangeRatesCache = {};

	// 🔐 Cargar el caché desde localStorage (si existe)
	function loadExchangeCache() {
		try {
			const data = localStorage.getItem("exchangeRatesCache");
			if (data) exchangeRatesCache = JSON.parse(data);
		} catch (err) {
			console.warn("⚠️ No se pudo cargar cache de tasas:", err);
		}
	}

	// 💾 Guardar caché en localStorage
	function saveExchangeCache() {
		try {
			localStorage.setItem("exchangeRatesCache", JSON.stringify(exchangeRatesCache));
		} catch (err) {
			console.warn("⚠️ No se pudo guardar cache de tasas:", err);
		}
	}

	// Cargar cache al inicio
	loadExchangeCache();

	async function updateTotalExchange(sourceTotalId, targetUsdId, fromCurrencyId, currencyId = null) {
		const total = parseFloat(document.getElementById(sourceTotalId)?.value) || 0;
		const targetField = document.getElementById(targetUsdId);
		const fromCurrency = document.getElementById(fromCurrencyId)?.value || "USD";
		const toCurrency = currencyId ? (document.getElementById(currencyId)?.value || "USD") : "USD";

		if (!targetField) return total;

		// Si las monedas son iguales
		if (fromCurrency === toCurrency) {
			targetField.value = total.toFixed(2);
			return total;
		}

		try {
			const converted = await convertCurrency(total, fromCurrency, toCurrency);
			targetField.value = converted.toFixed(2);
			return converted; // ⬅️ devuelve también el valor convertido si lo necesitas
		} catch (error) {
			console.error("Error updating exchange:", error);
			targetField.value = "Error";
			return total;
		}
	}

	async function convertCurrency(amount, fromCurrency, toCurrency) {
		if (fromCurrency === toCurrency) return amount;

		const rateKey = `${fromCurrency}_${toCurrency}`;
		const now = Date.now();
		let rateInfo = exchangeRatesCache[rateKey];

		// Verificar caché (12 horas)
		if (!rateInfo || now - rateInfo.timestamp > 12 * 60 * 60 * 1000) {
			try {
				const res = await fetch(`https://api.frankfurter.app/latest?amount=1&from=${fromCurrency}&to=${toCurrency}`);
				const data = await res.json();

				if (data && data.rates && data.rates[toCurrency]) {
					const rate = data.rates[toCurrency];
					rateInfo = { rate, timestamp: now };
					exchangeRatesCache[rateKey] = rateInfo;
					saveExchangeCache(); // Guardar en localStorage
				} else {
					console.warn("⚠️ Conversion API response:", data);
					return amount;
				}
			} catch (err) {
				console.error("💥 Error fetching conversion rate:", err);
				return amount;
			}
		}

		return amount * rateInfo.rate;
	}


//############################################################# END FUNCTIONES ##################################################################
});