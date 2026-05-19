document.addEventListener("DOMContentLoaded", async function () {
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
											<p class="mini-title">${window.i18n.qty}</p>
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
											<p class="mini-title">${window.i18n.year}</p>
											<strong>${product.year}</strong>
										</td>
										<td width="12%" align="left" valign="middle">
											<p class="mini-title">${window.i18n.price}</p>
											<strong>${product.price}</strong>
										</td>
										<td width="20%" align="left" valign="middle">
											<p class="mini-title">${window.i18n.form_name}:</p>
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
											<p class="mini-title">${window.i18n.phone}:</p>
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
										<td width="35%" align="right">${window.i18n.price} :</td><td width="65%" style="padding-left: 5px;">${sale.price_sum}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">${window.i18n.initial} :</td><td style="padding-left: 5px;">${sale.initial}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">${window.i18n.delivery_date} :</td><td style="padding-left: 5px;">${sale.delivery_date}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">${window.i18n.remaining} :</td><td style="padding-left: 5px;">${sale.remaining}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">${window.i18n.interest} :</td><td style="padding-left: 5px;">${sale.total_interest}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">${window.i18n.installments_month} :</td><td style="padding-left: 5px;">${sale.no_installments} / ${sale.payments}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">${window.i18n.payment_date} :</td><td style="padding-left: 5px;">${paymentDateFormatted}</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td align="right">${window.i18n.due} :</td><td style="padding-left: 5px;">${sale.due}</td>
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

						showConfirmModal(window.i18n?.delete_sale_title || "Delete Sale", window.i18n?.confirm_delete_sale || "Are you sure you want to delete this sale and all associated data?", async () => {
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
});