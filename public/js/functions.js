document.addEventListener("DOMContentLoaded", async function () {
    async function showBanner(bannerEl) {
		if (!bannerEl) return;
		bannerEl.classList.remove('hide');
		bannerEl.style.display = 'block';
		void bannerEl.offsetWidth;
		requestAnimationFrame(() => bannerEl.classList.add('show'));
	}
    window.showBanner = showBanner;

	async function hideBanner(bannerEl, onDone) {
		if (!bannerEl) return;

		bannerEl.classList.remove('show');
		void bannerEl.offsetWidth;
		bannerEl.classList.add('hide');

		const onEnd = (e) => {
			if (e.animationName !== 'slideUpOver') return;
			bannerEl.style.display = 'none';
			bannerEl.classList.remove('hide');
			bannerEl.removeEventListener('animationend', onEnd);
			if (typeof onDone === 'function') onDone();
		};

		bannerEl.addEventListener('animationend', onEnd);
	}
    window.hideBanner = hideBanner;

	function getUserBorderColor(user) {
		if (Number(user.status_by_admin) !== 1) {
			return "#9a9999"; // Gris: bloqueado por admin
		}

		if (Number(user.status) !== 1) {
			return "#fe7070"; // Rojo: inactivo
		}

		if (Number(user.verified) !== 1) {
			return "#fad186"; // Amarillo: no verificado
		}

		if (Number(user.package_id) === 1 && user.signup_date) {
			const signupDate = new Date(user.signup_date);
			const now = new Date();
			const diffMs = now - signupDate;
			const diffDays = diffMs / (1000 * 60 * 60 * 24);

			if (diffDays >= 15) {
				return "#4c0bbd"; // Morado: 15+ días con package_id 1
			}
		}

		return "#8cda8a"; // Verde: activo y verificado
	}
	window.getUserBorderColor = getUserBorderColor;

	// 📌 script para cargar marcas, modelos y submodelos
	async function initCategorySelectors(markId, modelId, submodelId, companyId) {
		let markSelect = document.getElementById(markId);
		let modelSelect = document.getElementById(modelId);
		let submodelSelect = document.getElementById(submodelId);
		let companySelect = document.getElementById(companyId);
	
		if (!markSelect || !modelSelect || !submodelSelect || !companySelect) return;
		// 🔹 Función para cargar marcas
		async function loadMarksByCompany(companyIdValue) {
			let url = 'api/get_categories.php';
			if (companyIdValue && !isNaN(companyIdValue)) {
				url += `?company=${companyIdValue}`;
			}

			try {
				const response = await fetch(url, {
					method: "GET",
					headers: { "Accept": "application/json" }
				});
				const data = await response.json();

				markSelect.innerHTML = `<option value="">All Marks</option>`;
				modelSelect.innerHTML = `<option value="">Select Model</option>`;
				submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;

				if (data.success && data.data.length > 0) {
					data.data.forEach(category => {
						const option = document.createElement("option");
						option.value = category.category_id;
						option.textContent = category.category_name;
						markSelect.appendChild(option);
					});
				} else {
					markSelect.innerHTML += `<option value="">No marks found</option>`;
				}
			} catch (error) {
				console.error("Error loading marks:", error);
				markSelect.innerHTML = `<option value="">Error loading marks</option>`;
			}
		}

		// 🔹 Cargar marcas iniciales (si hay una empresa ya seleccionada)
		await loadMarksByCompany(companySelect.value);

		// 🔹 Cuando cambia la empresa → recargar marcas
		companySelect.addEventListener('change', async () => {
			const newCompanyId = companySelect.value;
			await loadMarksByCompany(newCompanyId);
		});

		// 🔹 Reemplazar modelo (por si venía como input) y configurar
		modelSelect.disabled = true;
		modelSelect.innerHTML = `<option value="">Select Model</option>`;

		// 🔹 Reemplazar submodelo y configurar
		submodelSelect.disabled = true;
		submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;
	
		// 🔹 Evento: Al cambiar Marca → cargar Modelos
		markSelect.addEventListener('change', () => {
			const markId = markSelect.value;
			modelSelect.innerHTML = `<option value="">Select Model</option>`;
			modelSelect.disabled = !markId;
	
			// Reset submodel también
			submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;
			submodelSelect.disabled = true;
	
			if (!markId) return;
	
			fetch(`api/get_sub_categories.php?mark_id=${markId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			})
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data.length > 0) {
					data.data.forEach(category => {
						const option = document.createElement("option");
						option.value = category.category_id;
						option.textContent = category.category_name;
						modelSelect.appendChild(option);
					});
				} else {
					modelSelect.innerHTML += `<option value="">No models found</option>`;
				}
			})
			.catch(error => {
				console.error("Error loading models:", error);
			});
		});
	
		// 🔹 Evento: Al cambiar Modelo → cargar Submodelos
		modelSelect.addEventListener('change', () => {
			const modelId = modelSelect.value;
			submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;
			submodelSelect.disabled = !modelId;
	
			if (!modelId) return;
	
			fetch(`api/get_sub_models.php?model_id=${modelId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			})
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data.length > 0) {
					data.data.forEach(submodel => {
						const option = document.createElement('option');
						option.value = submodel.category_id;
						option.textContent = submodel.category_name;
						submodelSelect.appendChild(option);
					});
				} else {
					submodelSelect.innerHTML += `<option value="">No submodels found</option>`;
				}
			})
			.catch(error => {
				console.error("Error loading submodels:", error);
			});
		});
	}
	window.initCategorySelectors = initCategorySelectors;

	// 📌 formatear fecha y hora completa
	function formatFullDateTime(dateString) {
		const monthsAbbr = [
			"Jan", "Feb", "Mar", "Apr", "May", "Jun",
			"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
		];

		if (!dateString || String(dateString).trim() === "") {
			return "No date available";
		}

		const date = new Date(dateString);

		if (isNaN(date.getTime())) {
			return "Invalid date";
		}

		const year = date.getFullYear();
		const month = monthsAbbr[date.getMonth()];
		const day = String(date.getDate()).padStart(2, '0');
		const hours = String(date.getHours()).padStart(2, '0');
		const minutes = String(date.getMinutes()).padStart(2, '0');

		return `${year} ${month} ${day} ${hours}:${minutes}`;
	}
  	window.formatFullDateTime = formatFullDateTime;

	// 📌 formatear fecha de notificación
	function formatNotificationDate(dateString) {
		const monthsAbbr = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		const dateObj = new Date(dateString);
		const now = new Date();

		const isToday = dateObj.toDateString() === now.toDateString();
		const isSameYear = dateObj.getFullYear() === now.getFullYear();

		if (isToday) {
			// Mostrar solo la hora: HH:MM
			return dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
		} else if (isSameYear) {
			// Mostrar día y mes abreviado: ej. 21 Jul
			return `${dateObj.getDate()} ${monthsAbbr[dateObj.getMonth()]}`;
		} else {
			// Mostrar día/mes/año corto: ej. 24/11/23
			const day = String(dateObj.getDate()).padStart(2, '0');
			const month = String(dateObj.getMonth() + 1).padStart(2, '0');
			const year = String(dateObj.getFullYear()).slice(-2);
			return `${year}/${month}/${day}`;
		}
	}
	window.formatNotificationDate = formatNotificationDate;

	async function populateCompanies(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;

		// 🔹 Limpiar el contenido actual del <select>
		select.innerHTML = '';

		// 🔹 Agregar opción por defecto
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Company';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_company_info.php');
			const data = await res.json();

			if (data.success && Array.isArray(data.data) && data.data.length > 0) {
				data.data.forEach((company, index) => {
					const option = document.createElement('option');
					option.value = company.company_id;
					option.textContent = company.company_name;

					if (selectedValue) {
						if (String(company.company_id) === String(selectedValue)) {
							option.selected = true;
						}
					} else if (index === 0) {
						option.selected = true;
					}

					select.appendChild(option);
				});

				select.dispatchEvent(new Event('change'));
			} else {
				select.innerHTML += `<option value="">No companies found</option>`;
			}
		} catch (error) {
			console.error("Error loading companies:", error);
			select.innerHTML += `<option value="">Error loading companies</option>`;
		}
	}
	window.populateCompanies = populateCompanies;

	



	// 📌 Función para actualizar el progreso del onboarding
	function updateOnboardingProgress(status) {
		const onboardingPercent = document.getElementById('onboarding-percent');
		const onboardingBarFill = document.getElementById('onboarding-bar-fill');
		const onboardingProgress = document.getElementById('onboarding-progress');

		if (!onboardingPercent || !onboardingBarFill || !onboardingProgress) {
			return;
		}

		const steps = ['company', 'product', 'client', 'sale'];
		const completed = steps.filter(step => status[step]).length;
		const percent = Math.round((completed / steps.length) * 100);

		onboardingPercent.textContent = `${percent}%`;
		onboardingBarFill.style.width = `${percent}%`;

		steps.forEach(step => {
			const element = document.getElementById(`step-${step}`);
			if (element) {
				element.classList.toggle('completed', status[step]);
			}
		});

		if (percent !== 100) {
			onboardingProgress.classList.remove('hidden');
		}
	}
	window.updateOnboardingProgress = updateOnboardingProgress;

	function waitForElements(selectors, callback, timeout = 5000) {
		const start = Date.now();

		const interval = setInterval(() => {
			const elements = selectors.map(selector => document.querySelector(selector));
			const allFound = elements.every(element => element !== null);

			if (allFound) {
				clearInterval(interval);
				callback(elements);
				return;
			}

			if (Date.now() - start > timeout) {
				clearInterval(interval);

				const missingSelectors = selectors.filter(selector => !document.querySelector(selector));
				console.warn('Elementos no encontrados para onboarding:', missingSelectors);
			}
		}, 300);
	}

	function startOnboardingGuide() {
		const driverFactory = window.driver?.js?.driver || window.driver?.driver;

		if (!driverFactory) {
			console.warn('Driver.js no está cargado.');
			console.log('window.driver:', window.driver);
			return;
		}

		const requiredSelectors = [
			'#btn-create-product',
			'#onboarding-progress',
			'#btn-create-client',
			'#btn-create-sale'
		];

		waitForElements(requiredSelectors, () => {
			const guide = driverFactory({
				showProgress: true,
				allowClose: true,
				nextBtnText: 'Next',
				prevBtnText: 'Back',
				doneBtnText: 'Done',
				steps: [
					{
						element: '#onboarding-progress',
						popover: {
							title: 'Track your progress',
							description: 'Here you can see the steps needed to set up your inventory.',
							side: 'bottom',
							align: 'start'
						}
					},
					{
						element: '#btn-create-product',
						popover: {
							title: 'Create your first product',
							description: 'Start by adding a product to your inventory. It takes less than a minute.',
							side: 'bottom',
							align: 'start'
						}
					},
					{
						element: '#btn-create-client',
						popover: {
							title: 'Add your customers',
							description: 'You can create customer profiles to manage and track your sales more easily.',
							side: 'bottom',
							align: 'start'
						}
					},
					{
						element: '#btn-create-sale',
						popover: {
							title: 'Record a sale',
							description: 'When you record a sale, the system will automatically update your stock.',
							side: 'bottom',
							align: 'start'
						}
					}
				]
			});

			guide.drive();
		});
	}
	window.startOnboardingGuide = startOnboardingGuide;
});