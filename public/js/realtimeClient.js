document.addEventListener("DOMContentLoaded", async function () {
	//############################################################# NOTIFICATIONS ##################################################################
	(async () => {
		const headerMenu = document.getElementById("header-menu");
		if (headerMenu) {
			let currentUserId = 0;

			let response = await fetch('api/get_my_info.php', {
				method: 'GET',
				headers: { Accept: "application/json" }
			});

			let data = await response.json();
			if (data.success && data.data) {
				let user = data.data;
				currentUserId = parseInt(user.user_id, 10) || 0;
			}

			// PRODUCCIÓN (Nginx proxya a ws://
			// const wsProtocol = location.protocol === 'https:' ? 'wss' : 'ws';
			// const wsUrl = `${wsProtocol}://${location.host}/ws`; // sin puertos; Nginx proxya a 3001
			// const socket = new WebSocket(wsUrl);

			// LOCAL (para desarrollo, sin Nginx)
			const socket = new WebSocket(`ws://${location.hostname}:3001`);

			socket.addEventListener('open', () => {
				console.log('📡 WS connected ✅');
			});

			socket.addEventListener('close', () => {
				console.warn('⚠️ WS disconnected');
			});

			socket.addEventListener('error', error => {
				console.error('❌ Error in WebSocket:', error);
			});

			const refetchMessages = (() => {
				let t;
				return () => {
					clearTimeout(t);
					t = setTimeout(() => window.fetchAndRenderNotifications?.(), 250);
				};
			})();

			socket.addEventListener('message', async event => {
				let data;
				try {
					data = JSON.parse(event.data);
				} catch (e) {
					console.error('[WS] JSON inválido:', event.data);
					return;
				}

				if (data.type !== 'notification') return;

				const me = Number(currentUserId);

				let matchesMe = Number.isFinite(Number(data.to_user_id)) && Number(data.to_user_id) === me;

				if (!matchesMe && Array.isArray(data.to_user_id)) {
					matchesMe = data.to_user_id.map(Number).includes(me);
				}
				if (!matchesMe && (data.to_user_id === null || data.to_user_id === 'all')) {
					matchesMe = true;
				}

				// console.log('[WS] incoming notification', data, { me, matchesMe });
				if (!matchesMe) return;

				const message = data.message;
				const notifType = data.notification_type || 'General';
				const link = data.link;

				const container = document.getElementById('notification-container');
				if (!container) {
					console.warn('[WS] #notification-container no encontrado');
					return;
				}

				const box = document.createElement('div');
				box.classList.add('notification-box');

				// box.innerHTML = `
				// 	<div class="notification-title">${notifType}</div>
				// 	<div class="notification-message">${message}</div>
				// `;

				const titleEl = document.createElement('div');
				titleEl.className = 'notification-title';
				titleEl.textContent = notifType;

				const msgEl = document.createElement('div');
				msgEl.className = 'notification-message';
				msgEl.textContent = message;

				box.append(titleEl, msgEl);

				if (link) {
					box.style.cursor = 'pointer';
					box.addEventListener('click', () => {
						window.location.href = link;
					});
				}

				container.appendChild(box);

				// Forzar reflow para que la transición funcione
				void box.offsetWidth;
				box.classList.add('show');

				await checkNotifications();
				refetchMessages(); 

				// Eliminar después de 5 segundos
				setTimeout(() => {
					box.classList.remove('show');
					setTimeout(() => container.removeChild(box), 300); // coincide con el transition
				}, 10000);
			});
		}

		async function checkNotifications() {
			const notifCount = document.getElementById('notif-count');
			if (!notifCount) return;

			try {
				const response = await fetch('api/get_notifications.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
				});

				const data = await response.json();
				const count = (data.success && typeof data.count !== 'undefined') ? parseInt(data.count, 10) : 0;

				notifCount.textContent = count > 0 ? count : '';
				notifCount.style.display = count > 0 ? 'inline-block' : 'none';

			} catch (error) {
				console.error("❌ Error getting notifications:", error);
				notifCount.textContent = '';
				notifCount.style.display = 'none';
			}
		} checkNotifications();

		const messageListContainer = document.getElementById('messageList');
		const searchMessageField = document.getElementById('messageSearchField');
		if (messageListContainer && searchMessageField) {
			async function fetchAndRenderNotifications() {
				try {
					const searchTerm = searchMessageField.value.trim().toLowerCase();

					const params = new URLSearchParams();
					if (searchTerm) params.append('search', searchTerm);

					const res = await fetch(`api/get_notifications.php?${params.toString()}`, {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});
					const data = await res.json();

					messageListContainer.innerHTML = "";

					if (data.success && Array.isArray(data.data) && data.data.length > 0) {
						data.data.forEach(notif => {
							const row = document.createElement('tr');
							row.className = 'form_height';
							row.setAttribute('data-id', notif.notification_id);

							row.innerHTML = `
								<td width="20%" align="center" valign="middle">
									<div class="customers-profile">
										<img src="${notif.from_user_image}" alt="Profile Pic">
									</div>
								</td>
								<td width="65%" align="left" valign="middle">
									<p>${notif.is_read == 0 ? `<strong>${notif.from_user_name || 'Notification'}</strong>` : `${notif.from_user_name || 'Notification'}`}</p>
									<p>${notif.notification_type || ''}</p>
								</td>
								<td width="15%" align="center" valign="top">
									<p>${notif.is_read == 0 ? `<strong>${formatNotificationDate(notif.created_at)}</strong>` : `${formatNotificationDate(notif.created_at)}`}</p>
								</td>
							`;

							// Agregar evento al tr
							row.addEventListener('click', async () => {
								try {
									const formData = new URLSearchParams();
									formData.append('notification_id', notif.notification_id);

									await fetch('api/mark_notification_read.php', {
										method: 'POST',
										headers: {
											'Accept': 'application/json'
										},
										body: formData
									});
								
									const nameCell = row.querySelector('td:nth-child(2) p:first-child');
									if (nameCell && nameCell.innerHTML.includes('<strong>')) {
										nameCell.innerHTML = notif.from_user_name || 'Notification';
									}

									const dateCell = row.querySelector('td:nth-child(3) p');
									if (dateCell && dateCell.innerHTML.includes('<strong>')) {
										dateCell.innerHTML = formatNotificationDate(notif.created_at);
									}

									await checkNotifications();

									// ---- Cargar el producto (si notification_link trae un ID numérico) ----
									let productHtml = '';
									let answerProductHtml = '';
									
									// const prodId = Number(notif.notification_link);

									const rawLink = notif.notification_link;
									const prodId = rawLink !== null && rawLink !== undefined && rawLink !== ''
										? Number(rawLink)
										: NaN;

									if (Number.isInteger(prodId) && prodId > 0) {
										try {
											const prodRes = await fetch(`api/get_products.php?product_id=${prodId}`, {
												method: 'GET',
												headers: { 'Accept': 'application/json' }
											});
											const prodJson = await prodRes.json();

											if (prodJson.success && Array.isArray(prodJson.data) && prodJson.data.length) {
												const product = prodJson.data.find(p => String(p.product_id) === String(prodId)) || prodJson.data[0];

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
																			<td colspan="6" align="center" style="height: 10px; border-top: 1px solid #CCC;">
																				<p>Total Weight<br><strong>${product.total_weight ? product.total_weight + ' kg' : ''}</strong></p>
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
																		<td style="width: 25%; height: 10px; border-top: 1px solid #CCC;">
																			<p>Units<br><strong>${product.units_per_pack || ''}</strong></p>
																		</td>
																		<td style="width: 40%; height: 10px; border-top: 1px solid #CCC;">
																			<p>Weight/unit<br><strong>${product.weight_per_unit ? product.weight_per_unit + ' kg' : ''}</strong></p>
																		</td>
																		<td style="width: 35%; height: 10px; border-top: 1px solid #CCC;">
																			<p>Total Weight<br><strong>${product.total_weight ? product.total_weight + ' kg' : ''}</strong></p>
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

												const minQty = (
													product.quantity !== null &&
													product.min_quantity !== null &&
													!isNaN(product.quantity) &&
													!isNaN(product.min_quantity) &&
													Number(product.quantity) <= Number(product.min_quantity)
												) ? "min-qty" : "";

												productHtml = `
												<div class="request-position">	
													<div class="notification-detail-card">
														<div class="notification-product-pic">
															<img src="${productImage}" alt="${product.product_name}" class="${imageClass}" />
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
																	<td style="width: 50%; border-top: 1px solid #CCC;">
																		<p>Year<br><strong>${product.product_year || ''}</strong></p>
																	</td>
																	<td style="width: 50%; border-top: 1px solid #CCC;">
																		<p>Price<br><strong>${product.price ? '$' + product.price + ' ' + product.currency : ''}</strong></p>
																	</td>
																</tr>
															</table>
														</div>
													</div>
												</div>`;

												answerProductHtml = `
												<div class="answer-pro-requested">
													<form method="post" name="formAnswerProdReq" id="formAnswerProdReq">
														<table width="100%" align="center" cellspacing="0" style="margin: 10px 0 15px;">
															<tr valign="baseline">
																<td colspan="2" align="center">
																	<input class="form-input-style" type="number" name="" id="">
																</td>
															</tr>
															<tr valign="baseline" class="form_height" >
																<td width="50%" align="left" valign="middle">
																	<button type="button" class="neutral-btn">Cancel</button>
																</td>
																<td width="50%" align="right" valign="middle">
																	<input type="submit" class="button-style-agree" value="Create" />
																</td>
															</tr>
														</table>
													</form>
												</div>`;

												updateStockHtml = `
												`;
											}
										} catch (e) {
											console.error('Error fetching product by ID:', e);
										}
									}

									let notificationContent = '';

									if (notif.notification_type === 'Product Request') {
										notificationContent = `${productHtml || ''}${answerProductHtml || ''}`;
									}
									else if (notif.notification_type === 'Product Info') {
										notificationContent = `${productHtml || ''}`;
									}

									const detailsDiv = document.getElementById('notifications-details');
									if (detailsDiv) {
										detailsDiv.innerHTML = `
										<div>
											<table class="message-details" id="messageDetails" width="90%" align="center" cellspacing="0" style="margin-top: 15px;">
												<tr valign="baseline" class="form_height">
													<td colspan="2" style="border-bottom: 1px solid #ccc; padding-bottom: 5px;" align="center" valign="middle">
														<h3>${notif.notification_type}</h3>
													</td>
												</tr>
												<tr class="form_height" valign="baseline">
													<td width="50%" align="left" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0;"><strong>From:</strong> ${notif.from_user_name}</td>
													<td width="50%" align="right" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0;"><strong>Date: </strong><span id="notif-from-user">${formatFullDateTime(notif.created_at)}</span></td>
												</tr>
												<tr valign="baseline">
													<td colspan="2" align="center" valign="middle">
														${notificationContent}
													</td>
													<td width="50%" align="left" valign="middle"><span id="notif-content"></span></td>
												</tr>
											</table>
										</div>
										`;
									}
								} catch (err) {
									console.error("Error marking notification as read:", err);
								}
							});

							messageListContainer.appendChild(row);
						});

						const detailsDiv = document.getElementById('notifications-details');
						if (detailsDiv && detailsDiv.innerHTML.trim() === "") {
							detailsDiv.innerHTML = `<p style="text-align:center; opacity: 0.7;">Select a notification</p>`;
						}
					} else {
						messageListContainer.innerHTML = `<p style="text-align:center;">No notifications found.</p>`;

						const detailsDiv = document.getElementById('notifications-details');
						if (detailsDiv) {
							detailsDiv.innerHTML = `<p style="text-align:center; opacity: 0.7;">Select a notification</p>`;
						}
					}
				} catch (err) {
					console.error("Error loading notifications:", err);
					messageListContainer.innerHTML = `<p style="text-align:center;">Error loading notifications</p>`;

					const detailsDiv = document.getElementById('notifications-details');
					if (detailsDiv) {
						detailsDiv.innerHTML = `<p style="text-align:center; opacity: 0.7;">Select a notification</p>`;
					}
				}
			}
			window.fetchAndRenderNotifications = fetchAndRenderNotifications;

			searchMessageField.addEventListener('keyup', fetchAndRenderNotifications);
			fetchAndRenderNotifications();
		}
	})();
	//############################################################# END NOTIFICATIONS ##################################################################
});