document.addEventListener("DOMContentLoaded", async function () {
//############################################################# REPORTS ##################################################################
    const reportsSearchField = document.getElementById('reportsSearchField');
    const reportsFromDate = document.getElementById('reports_from_date');
    const reportsToDate = document.getElementById('reports_to_date');
	const reportsSelectCompany = document.getElementById('reports_select_company');
	const reportsProductMark = document.getElementById('reports_product_mark');
	const reportsProductModel = document.getElementById('reports_product_model');
	const reportsProductSubModel = document.getElementById('reports_product_sub_model');

    const reportsContainer = document.getElementById('reports-list');
    const reportSidebar = document.getElementById('report-sidebar');

	if (reportsSearchField || reportsFromDate || reportsToDate || reportsContainer || reportSidebar) {
		async function fetchAndRenderReports() {
			try {
				const searchTerm = reportsSearchField?.value.trim() || "";
                const fromDate = reportsFromDate?.value || "";
			    const toDate = reportsToDate?.value || "";
				const company = reportsSelectCompany?.value || "";
				const productMark = reportsProductMark?.value || "";
				const productModel = reportsProductModel?.value || "";
				const productSubModel = reportsProductSubModel?.value || "";
				

				const params = new URLSearchParams();
				if (searchTerm) params.append('search', searchTerm);
                if (fromDate) params.append('reports_from_date', fromDate);
                if (toDate) params.append('reports_to_date', toDate);
				if (company) params.append('reports_select_company', company);
				if (productMark) params.append('reports_product_mark', productMark);
				if (productModel) params.append('reports_product_model', productModel);
				if (productSubModel) params.append('reports_product_sub_model', productSubModel);

				const res = await fetch(`api/get_reports.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				const data = await res.json();
				
				if (reportsContainer) {
					reportsContainer.innerHTML = "";
				}

                if (reportSidebar) {
					reportSidebar.innerHTML = "";
				}

				if (reportSidebar) {
					const summary = data?.summary || {};

					const totalSoldAmount = !isNaN(parseFloat(summary.total_sold_amount))
						? parseFloat(summary.total_sold_amount).toFixed(2)
						: "0.00";

					const totalQuantitySold = !isNaN(parseInt(summary.total_quantity_sold, 10))
						? parseInt(summary.total_quantity_sold, 10)
						: 0;

					const productsFound = !isNaN(parseInt(summary.products_found, 10))
						? parseInt(summary.products_found, 10)
						: 0;

					const averageSoldAmount = !isNaN(parseFloat(summary.average_sold_amount_per_product))
						? parseFloat(summary.average_sold_amount_per_product).toFixed(2)
						: "0.00";

					const summaryFromDate = summary.from_date || "-";
					const summaryToDate = summary.to_date || "-";

					reportSidebar.innerHTML = `
						<div class="report-sidebar-box">
							<table width="90%" align="center" cellspacing="0" cellpadding="6">
								<tr>
									<td colspan="2" align="center" valign="middle">
										<h3 style="margin:0;">Report Summary</h3>
									</td>
								</tr>
								<tr>
									<td width="60%" align="left" valign="middle">
										<strong>From</strong>
									</td>
									<td width="40%" align="right" valign="middle">
										${summaryFromDate}
									</td>
								</tr>
								<tr>
									<td width="60%" align="left" valign="middle">
										<strong>To</strong>
									</td>
									<td width="40%" align="right" valign="middle">
										${summaryToDate}
									</td>
								</tr>
								<tr>
									<td width="60%" align="left" valign="middle">
										<strong>Products found</strong>
									</td>
									<td width="40%" align="right" valign="middle">
										${productsFound}
									</td>
								</tr>
								<tr>
									<td width="60%" align="left" valign="middle">
										<strong>Total quantity sold</strong>
									</td>
									<td width="40%" align="right" valign="middle">
										${totalQuantitySold}
									</td>
								</tr>
								<tr>
									<td width="60%" align="left" valign="middle">
										<strong>Total sold amount</strong>
									</td>
									<td width="40%" align="right" valign="middle">
										$${totalSoldAmount}
									</td>
								</tr>
								<tr>
									<td width="60%" align="left" valign="middle">
										<strong>Avg. per product</strong>
									</td>
									<td width="40%" align="right" valign="middle">
										$${averageSoldAmount}
									</td>
								</tr>
							</table>
						</div>
					`;
				}

				if (data.success && Array.isArray(data.data) && data.data.length > 0) {
					data.data.forEach(report => {
						const row = document.createElement('div');
						row.className = 'report-row';

						const price = !isNaN(parseFloat(report.price))
							? parseFloat(report.price).toFixed(2)
							: "0.00";

						const soldTotal = !isNaN(parseFloat(report.sold_total))
							? parseFloat(report.sold_total).toFixed(2)
							: "0.00";

						row.innerHTML = `
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td width="12%" align="center" valign="middle">
										<p class="mini-title">HS Code:</p>
										${report.hs_code || '-'}
									</td>
									<td width="21%" align="left" valign="middle" style="padding-left:2%;">
										${report.product_name || ''}
										<p class="mini-title">${report.mark_name + ' - ' + report.model_name + ' - ' + report.sub_model_name}</p>
									</td>
                                    <td width="11%" align="center" valign="middle">
										<p class="mini-title">Quantity:</p>
										${report.quantity || '-'}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">Price:</p>
										$${price}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">Sold:</p>
										${report.sold || '-'}
									</td>
                                    <td width="11%" align="center" valign="middle">
										<p class="mini-title">Sold Total:</p>
										$${soldTotal}
									</td>
									<td width="16%" align="center" valign="middle">
										<p class="mini-title">Create Date:</p>
										${formatFullDateTime(report.created_at) || '-'}
									</td>
									<td width="5%" align="center" valign="middle">
										<div class="reports-menu">
											<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
										</div>
									</td>
								</tr>
							</table>
						`;

						reportsContainer.appendChild(row);

						// const reportsMenuBtn = row.querySelector('.reports-menu');
						// reportsMenuBtn.addEventListener('click', () => {
						// 	// openReportsForm(payment.payment_id);

						// 	handlePopupClose("payments-options", ".formular-frame", []);
						// });
					});
				} else {
					if (reportsContainer) {
						reportsContainer.innerHTML = `
							<p style="text-align:center;">No reports found.</p>
						`;
					}
				}
			} catch (err) {
				console.error("Error loading reports:", err);
				
				if (reportsContainer) {
					reportsContainer.innerHTML = `<p style="text-align:center;">Error loading reports</p>`;
				}

				if (reportSidebar) {
					reportSidebar.innerHTML = `
						<div class="report-sidebar-box">
							<p style="text-align:center;">Error loading summary</p>
						</div>
					`;
				}
			}
		}

		await populateCompanies('reports_select_company');

		initCategorySelectors('reports_product_mark', 'reports_product_model', 'reports_product_sub_model', 'reports_select_company');
		
		reportsSearchField?.addEventListener('keyup', fetchAndRenderReports);
        reportsFromDate?.addEventListener('change', fetchAndRenderReports);
	    reportsToDate?.addEventListener('change', fetchAndRenderReports);
		
		document.addEventListener('change', (e) => {
			const id = e.target?.id;
			if (["reports_product_mark", "reports_product_model", "reports_product_sub_model", "reports_select_company"].includes(id)) {
				fetchAndRenderReports();
			}
		});
        
		fetchAndRenderReports();
	}

	async function openReportsForm(reportId) {
		scrollToTopIfNeeded();
		
		const paymentsOptions = document.getElementById('payments-options');
		const popupContent = paymentsOptions.querySelector('.formular-frame');
		const ordNoName = document.getElementById('ord-no-name');
	
		if (!reportId) return;

		try {
			const res = await fetch(`api/get_payments.php?payment_id=${reportId}`);
			const data = await res.json();

			if (data.success && data.data.length > 0) {
				const payment = data.data.find(p => p.payment_id == reportId);
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
	//############################################################# END REPORTS ##################################################################
});