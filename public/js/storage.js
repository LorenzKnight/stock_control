document.addEventListener("DOMContentLoaded", async function () {
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
					storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">${window.i18n?.no_results_yet || "No results yet"}</p></td></tr>`;
					storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">${window.i18n?.no_results_yet || "No results yet"}</p>`;
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
					storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">${window.i18n?.no_results_yet || "No results yet"}</p></td></tr>`;
					storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">${window.i18n?.no_results_yet || "No results yet"}</p>`;
				}
			} catch (err) {
				console.error("Error loading storages:", err);
				storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">${window.i18n?.error_loading_storages || "Error loading storages"}</p></td></tr>`;
				storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">${window.i18n?.error_loading_storages || "Error loading storages"}</p>`;
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
			storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">${window.i18n?.no_results_yet || "No results yet"}</p></td></tr>`;
			storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">${window.i18n?.no_results_yet || "No results yet"}</p>`;
			return;
		}

		if (!Array.isArray(products) || products.length === 0) {
			storageDetails.innerHTML = `<p style="text-align:center; padding:15px;">${window.i18n?.no_product_found || "No product found"}</p>`;
		} else {
			renderStorageDetails({
				type: 'product-search',
				products: products,
				storages: storages
			}, null);
		}

		if (!Array.isArray(slots) || slots.length === 0) {
			storageSidebarTable.innerHTML = `<tr><td><p style="text-align:center;">${window.i18n?.no_slots_found || "No slots found"}</p></td></tr>`;
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
						${window.i18n?.slot_name || "Slot Name"}: <strong>${slot.slot_name || '—'}</strong><br>
						<p>${window.i18n?.status || "Status"}: <strong style="color:${statusColor};">${statusText}</strong></p>
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

		function buildProductCard(product) { //AQUI
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
				}
			} else {
				unitImg = "images/sys-img/wooden-box.png";
				prodDetail = `
					<tr valign="baseline">
						<td colspan="6" style="height: 10px;">
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline">
									<td style="width: 30%; height: 10px; border-top: 1px solid var(--border-light);">
										<p>${window.i18n?.units}<br><strong>${product.units_per_pack || ''}</strong></p>
									</td>
									<td style="width: 40%; height: 10px; border-top: 1px solid var(--border-light);">
										<p>${window.i18n?.weight_unit}<br><strong>${product.weight_per_unit ? product.weight_per_unit + ' kg' : ''}</strong></p>
									</td>
									<td style="width: 30%; height: 10px; border-top: 1px solid var(--border-light);">
										<p>${window.i18n?.total_weight}<br><strong>${product.total_weight ? product.total_weight + ' kg' : ''}</strong></p>
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
									<p style="margin: 10px 0 0;">${window.i18n?.qty}: <strong class="${minQty}">${product.quantity ?? ''}</strong></p>
								</td>
							</tr>
							<tr valign="baseline">
								<td colspan="2" style="height: 20px;">
                                    <p>${markText} - ${modelText}</p>
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
									<p>${window.i18n?.year}<br><strong>${product.product_year || ''}</strong></p>
								</td>
								<td style="width: 50%; border-top: 1px solid var(--border-light);">
									<p>${window.i18n?.price}<br><strong>${product.price ? '$' + product.price + ' ' + product.currency : ''}</strong></p>
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
								<p class="mini-title">${window.i18n?.slot_name || "Slot Name"}:</p>
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
								<p class="mini-title">${window.i18n?.product_found || "Products found"}:</p>
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
								<p class="mini-title">${window.i18n?.slot_name || "Slot Name"}:</p>
								<strong>${slot.slot_name || '—'}</strong>
							</td>
							<td width="50%" align="center" valign="middle"></td>
						</tr>
						<tr valign="baseline">
							<td width="100%" align="left" valign="middle"></td>
						</tr>
						<tr valign="baseline">
							<td width="100%" align="left" valign="middle">
								<p class="mini-title">${window.i18n?.description || "Description"}:</p>
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
				slotActionBtn.value = window.i18n?.ok || "Ok";
                // slotActionBtn.value = window.i18n?.select_slot || "Select Slot";
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
				slotActionBtn.value = window.i18n?.save_changes || "Save Changes";
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
					slotActionBtn.value = window.i18n?.create_slot || "Create Slot";
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
});