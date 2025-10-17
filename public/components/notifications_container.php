<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<div class="data-container" style="height: 780px;">
		<h2 style="margin-left: 10px;">Notifications</h2>
		<div class="product-table flex">
			<div class="notifications-sidebar">
				<table width="90%" align="center" cellspacing="0" style="margin-top: 15px;">
					<tr valign="baseline" class="form_height">
						<td colspan="4" style="border-bottom: 1px solid var(--clr-border); padding-bottom: 5px;" align="center" valign="middle">
							<input type="text" name="messageSearchField" id="messageSearchField" class="search-field" placeholder="Search Messages...">
						</td>
					</tr>
				</table>
				<div class="notifications-list" id="notificationsList">
					<table class="message-list" id="messageList" width="90%" align="center" cellspacing="0" style="margin-top: 15px;"></table>
				</div>
			</div>
			<div class="notifications-details" id="notifications-details">
				<!-- <div>
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
								<div class="request-position">	
									<div class="product-card">
										<div class="product-pic">
											<img src="${productImage}" alt="${product.product_name}" class="${imageClass}" />
										</div>
										<div class="product-desc">
											<table width="90%" align="center" cellspacing="0">
												<tr valign="baseline">
													<td style="width: 50%; height: 20px;">
														<p style="margin: 10px 0 0;">${product.product_name}</p>
													</td>
													<td style="width: 50%; height: 20px;" align="right">
														<p style="margin: 10px 0 0;">Qty: <strong class="${minQty}">${product.quantity || ''}</strong></p>
													</td>
												</tr>
												<tr valign="baseline">
													<td colspan="2" style="height: 20px;">
														<h3><strong>${product.mark_name + ' - ' + product.model_name}</strong></h3>
													</td>
												</tr>
												<tr valign="baseline">
													<td colspan="2" style="height: 20px;">
														${product.submodel_name || ''}
													</td>
												</tr>
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
								</div>

								<div class="answer-pro-requested">
									<p><strong>Contenido:</strong> ${notif.notification_content}</p>
									<table width="100%" align="center" cellspacing="0" style="margin-top: 15px;">
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
								</div>
							</td>
							<td width="50%" align="left" valign="middle"><span id="notif-content"></span></td>
						</tr>
					</table>
				</div> -->
			</div>
		</div>
	</div>
</div>