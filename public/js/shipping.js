document.addEventListener("DOMContentLoaded", async function () {
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
					<tr valign="baseline" >
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
					<tr valign="baseline" >
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
					<tr valign="baseline" >
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
						<th style="border-bottom: 1px solid var(--gray-200); padding-bottom: 5px;" align="left">Product</th>
						<th style="border-bottom: 1px solid var(--gray-200); padding-bottom: 5px;" align="center">Qty</th>
						<th style="border-bottom: 1px solid var(--gray-200); padding-bottom: 5px;" align="center">Weight/Unit</th>
						<th style="border-bottom: 1px solid var(--gray-200); padding-bottom: 5px;" align="center">Weight</th>
						<th style="border-bottom: 1px solid var(--gray-200); padding-bottom: 5px;" align="center">Price/Kg</th>
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
						<th style="border-bottom: 1px solid var(--gray-200);" align="left">Product</th>
						<th style="border-bottom: 1px solid var(--gray-200);" align="center">Qty</th>
						<th style="border-bottom: 1px solid var(--gray-200);" align="center">Weight</th>
						<th style="border-bottom: 1px solid var(--gray-200);" align="center">Total $</th>
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
						<td style="border-top: 1px solid var(--gray-200);">Total</td>
						<td style="border-top: 1px solid var(--gray-200);" align="center">${totalQty}</td>
						<td style="border-top: 1px solid var(--gray-200);" align="center">${totalWeight.toFixed(2)} kg</td>
						<td style="border-top: 1px solid var(--gray-200);" align="center">$${totalConverted.toFixed(2)}</td>
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
			    addShippingForm.style.display = 'flex';
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

				shippingOptions.style.display = 'flex';
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
				
				loadOptions.style.display = 'flex';
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
							alert("Load ID not found.");
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

							makeRadioRowSelectable(row, {
								rowSelector: `#${customerListTable.id} .categoryContainer`,
								selectedClass: 'selected-category'
							});

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
					const destination = document.getElementById('load_destination')?.value.trim() || '';
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

								// 🟢 Hacer seleccionable toda la fila
								makeRadioRowSelectable(row, {
									rowSelector:
										`#${editCustomerListTable.id} .categoryContainer`,
									selectedClass: 'selected-customer'
								});

								editCustomerListTable.appendChild(row);

								if (String(customer.customer_id) === String(load.customer.customer_id)) {
									const customerRadio = row.querySelector('input[type="radio"]');
									if (customerRadio) {
										customerRadio.checked = true;
										row.classList.add('selected-customer');
									}
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

			if ((searchEditProductInput || editMarkSelect) && editShippingProductListTable) {
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
								const uniqueId = `edit-load-product-${product.product_id}`;
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

								const checkbox = row.querySelector('.shipping-product-checkbox');
								const quantityInput = row.querySelector('input[type="number"]');
								
								// ✅ marcar los productos que ya están en este load
								const selectedProduct = load.products.find(p => Number(p.product_id) === Number(product.product_id));
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

								checkbox.addEventListener('change', sumByWeight);
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
				const checkboxes = formEditLoad.querySelectorAll('.shipping-product-checkbox:checked');
				let total = 0;
			
				checkboxes.forEach(cb => {
					const weight = parseFloat(cb.getAttribute('data-weight')) || 0;
					const qtyInput = formEditLoad.querySelector(`#qty-${cb.id}`);
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

					const customerId = formEditLoad.querySelector('input[name="customer_select"]:checked')?.dataset.id;
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
					const productCheckboxes = Array.from(formEditLoad.querySelectorAll('.shipping-product-checkbox:checked'));
					const products = await Promise.all(productCheckboxes.map(async cb => {
						const productId = parseInt(cb.value);
						const weight = parseFloat(cb.dataset.weight) || 0;
						const qtyInput = formEditLoad.querySelector(`#qty-${cb.id}`);
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
});