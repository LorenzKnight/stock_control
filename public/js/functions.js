document.addEventListener("DOMContentLoaded", async function () {
	const lang = typeof getCurrentLang === 'function'
		? getCurrentLang()
		: (window.APP_LANG || 'en');


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

	function getUserBorderColorAndText(user) {
		if (Number(user.status_by_admin) !== 1) {
			return {
				color: "#9a9999",
				text: "Blocked by admin"
			};
		}

		if (Number(user.status) !== 1) {
			return {
				color: "#fe7070",
				text: "Inactive"
			};
		}

		if (Number(user.verified) !== 1) {
			return {
				color: "#fad186",
				text: "Not Verified"
			};
		}

		if (Number(user.package_id) === 1 && user.signup_date) {
			const signupDate = new Date(user.signup_date);
			const now = new Date();
			const diffMs = now - signupDate;
			const diffDays = diffMs / (1000 * 60 * 60 * 24);

			if (diffDays >= 30) {
				return {
					color: "#4c0bbd",
					text: "Trial expired"
				};
			}
		}

		return {
			color: "#8cda8a",
			text: "Active"
		};
	}
	window.getUserBorderColorAndText = getUserBorderColorAndText;

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

		return `${year} ${month} ${day}, ${hours}:${minutes}`;
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

		const t = window.i18n || {};

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
				nextBtnText: t.next || 'Next',
				prevBtnText: t.back || 'Back',
				doneBtnText: t.done || 'Done',
				steps: [
					{
						element: '#onboarding-progress',
						popover: {
							title: t.track_your_progress || 'Track your progress',
							description: t.track_desc || 'Here you can see the steps needed to set up your inventory.',
							side: 'bottom',
							align: 'start'
						}
					},
					{
						element: '#btn-create-product',
						popover: {
							title: t.create_first_product || 'Create your first product',
							description: t.first_product_desc || 'Start by adding a product to your inventory. It takes less than a minute.',
							side: 'bottom',
							align: 'start'
						}
					},
					{
						element: '#btn-create-client',
						popover: {
							title: t.add_first_customer || 'Add your customers',
							description: t.first_customer_desc || 'You can create customer profiles to manage and track your sales more easily.',
							side: 'bottom',
							align: 'start'
						}
					},
					{
						element: '#btn-create-sale',
						popover: {
							title: t.record_your_first_sale || 'Record your first sale',
							description: t.first_sale_desc || 'When you record a sale, the system will automatically update your stock.',
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
	window.setupBackToMenuButton = setupBackToMenuButton;

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
	window.animateHeightChange = animateHeightChange;

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
	window.fadeOutAndHide = fadeOutAndHide;
	
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
	window.showWithFadeIn = showWithFadeIn;

	// 📌 scroll to top 
	function scrollToTopIfNeeded() {
		if (window.scrollY > 0) {
			window.scrollTo({
				top: 0,
				behavior: 'smooth'
			});
		}
	}
	window.scrollToTopIfNeeded = scrollToTopIfNeeded;

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
	window.resetPopupView = resetPopupView;

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
	window.handlePopupClose = handlePopupClose;

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
	window.loadSlotFormOrData = loadSlotFormOrData;

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
	window.syncProductsFromSelectedSlot = syncProductsFromSelectedSlot;

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
	window.initProductList = initProductList;

	function updateStorageActionButtonState() {
		const storageActionBtn = document.getElementById('storage-action-btn');
		if (!storageActionBtn) return;

		const hasSlotSelected = !!document.querySelector('input[name="storages_info"]:checked');
		const hasProductSelected = !!document.querySelector('input[name="products_info[]"]:checked');

		if (hasSlotSelected || hasProductSelected) {
			storageActionBtn.value = window.i18n?.save_changes || "Save Changes";
		} else {
			storageActionBtn.value = window.i18n?.add_storage || "Add Storage";
		}
	}
	window.updateStorageActionButtonState = updateStorageActionButtonState;

	function showChangeAlert() {
		const banner = document.getElementById('status-message');
		const statusText = document.getElementById('status-text');
		const statusImage = document.getElementById('status-image');

		statusText.innerText = "You have unsaved changes.";
		statusImage.src = "images/sys-img/error.gif";
		showBanner(banner);
	}
	window.showChangeAlert = showChangeAlert;

	function activateTab(activeTab, inactiveTab, showSection, hideSection) {
		activeTab.classList.add('tab-active');
		inactiveTab.classList.remove('tab-active');

		showSection.style.display = 'block';
		hideSection.style.display = 'none';
	}
	window.activateTab = activateTab;

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
	window.initDragAndDrop = initDragAndDrop;

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
	window.populateCustomerTypes = populateCustomerTypes;

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
	window.populateCountryPhoneCodes = populateCountryPhoneCodes;

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

	async function populateDocumentTypes(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = window.i18n?.select_document_type || 'Select a Document Type';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch(`/api/get_global_array.php?key=documentTypes&lang=${encodeURIComponent(lang)}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			
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
				const option = document.createElement('option');
				option.value = '';
				option.textContent = window.i18n?.no_document_types_found || 'No document types found';
				select.appendChild(option);
			}
		} catch (error) {
			console.error("Error loading document types:", error);

			const option = document.createElement('option');
			option.value = '';
			option.textContent = window.i18n?.error_loading_document_types || 'Error loading document types';
			select.appendChild(option);
		}
	}
	window.populateDocumentTypes = populateDocumentTypes;

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
	window.showConfirmModal = showConfirmModal;

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
	window.showAlertModal = showAlertModal;

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
	window.loadMarksForSearch = loadMarksForSearch;

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
	window.populateCurrencies = populateCurrencies;
});