document.addEventListener("DOMContentLoaded", async function () {
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
										<p class="mini-title">${window.i18n?.address}:</p>
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

			window.populateCustomerTypes('customer_type', 1);

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

						showConfirmModal(window.i18n?.delete_customer_title || "Delete Customer", window.i18n?.confirm_delete_customer || "Are you sure you want to delete this customer?", async () => {
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
});