document.addEventListener("DOMContentLoaded", async function () {
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
										<p class="mini-title">${window.i18n?.payment_no}:</p>
										${payment.payment_no || ''}
									</td>
									<td width="8%" align="center" valign="middle">
										<p class="mini-title">Ord no:</p>
										${payment.ord_no || ''}
									</td>
									<td width="13%" align="left" valign="middle" style="padding-left:2%;">
										<p class="mini-title">${window.i18n?.form_name}:</p>
										${payment.full_name || ''}
									</td>
									<td width="10%" align="center" valign="middle">
										<p class="mini-title">${payment.document_type}:</p>
										${payment.document_no || ''}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">${window.i18n?.method_of_payment}:</p>
										${payment.payment_method || ''}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">${window.i18n?.amount}:</p>
										${parseFloat(payment.amount).toFixed(2)}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">${window.i18n?.interest}:</p>
										- ${parseFloat(payment.interest).toFixed(2)}
									</td>
									<td width="11%" align="center" valign="middle">
										<p class="mini-title">${window.i18n?.due}:</p>
										${parseFloat(payment.due).toFixed(2)}
									</td>
									<td width="10%" align="center" valign="middle">
										<p class="mini-title">${window.i18n?.payment_date}:</p>
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

						showConfirmModal(window.i18n?.delete_payment || "Delete Payment", window.i18n?.confirm_delete_payment || "Are you sure you want to delete this Payment?", async () => {
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
});