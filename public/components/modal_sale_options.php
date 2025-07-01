<div class="bg-popup" id="sale-options">
	<div id="formular-frame" class="formular-frame">
		<div id="sale-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Sale Options</h2>
						<p id="ord-no"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editSaleBtn">Edit Sale</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="assignCustomerSaleBtn">More Information</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteSaleBtn">Delete Sale</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
					</td>
				</tr>
			</table>
		</div>
		<div id="edit-sales-modal" style="display: none;">
			<div class="back-to-sale-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditSale" id="formEditSale">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td colspan="6" align="center" valign="middle">
							<h2>Edit a Sale</h2>
						</td>      
					</tr>
					<tr valign="baseline">
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<input class="form-input-style" type="text" name="search-customer-for-edit" id="search-customer-for-edit" placeholder="Enter a name or Doc No..." title="Enter a valid name"/>
								</div>
								<div class="cat-all-list">
									<table id="select-customers-list-for-edit" cellspacing="0"></table>
								</div>
							</div>
						</td>
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td width="60%" align="center" valign="middle">
												<input type="text" class="form-medium-input-style" name="search-product-purchase-for-edit" id="search-product-purchase-for-edit" placeholder="Enter Product No..." title="Enter a valid Product No."/>
											</td>
											<td width="40%" align="center" valign="middle">
												<select class="form-input-style" name="search-product-mark-for-edit" id="search-product-mark-for-edit"></select>
											</td>
										</tr>
									</table>
								</div>
								<div class="cat-all-list">
									<table id="select-product-list-for-edit" cellspacing="0"></table>
								</div>
							</div>
						</td>
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<button type="button" class="button-style-agree disabled" style="pointer-events: none" disabled>Method of Payment</button>
								</div>
								<div class="cat-all-list">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<input class="form-input-style" type="text" name="edit_price_sum" id="edit_price_sum" placeholder="Price sum..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<input class="form-input-style" type="text" name="edit_initial" id="edit_initial" placeholder="Initial..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<input class="form-input-style" type="date" name="edit_delivery_date" id="edit_delivery_date" placeholder="Enter a delivery date..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<input class="form-input-style" type="text" name="edit_remaining" id="edit_remaining" placeholder="Remaining..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<input class="form-medium-input-style" type="text" name="edit_interest" id="edit_interest" value="10" placeholder="Enter a percent..."/>
											</td>
											<td width="50%" align="center" valign="middle">
												<input class="form-medium-input-style" type="text" name="edit_total_interest" id="edit_total_interest" placeholder="Total Interest..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<select class="form-input-style" name="edit_installments_month" id="edit_installments_month"></select>
											</td>
											<td width="50%" align="center" valign="middle">
												<input class="form-medium-input-style" type="date" name="edit_payment_date" id="edit_payment_date" placeholder="Enter a Payment date..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<input class="form-input-style" type="text" name="edit_due" id="edit_due" placeholder="Due..." disabled/>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
						</td>
						<td colspan="1" align="center" valign="middle">
							<button type="button" class="neutral-btn">Cancel</button>
						</td>
						<td colspan="1" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="Save" />
						</td>
						<td colspan="2" align="center" valign="middle">
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div id="sale-2" style="display: none;">
			<div class="back-to-sale-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="" id="">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Assign Product to Sale</h2>
						</td>      
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>