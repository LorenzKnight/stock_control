<div class="bg-popup" id="sale-options">
	<div id="formular-frame" class="formular-frame">
		<div id="sale-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= $t['sale_options'] ?></h2>
						<p id="ord-no"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editSaleBtn"><?= $t['edit_sale'] ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="assignCustomerSaleBtn"><?= $t['more_information'] ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteSaleBtn"><?= $t['delete_sale'] ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= $t['close'] ?></button>
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
							<h2><?= $t['edit_sale_title'] ?></h2>
						</td>      
					</tr>
					<tr valign="baseline">
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<input class="form-input-style" type="text" name="search-customer-for-edit" id="search-customer-for-edit" placeholder="<?= $t['enter_name_or_document'] ?>..." title="<?= $t['enter_name_or_document'] ?>"/>
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
												<input type="text" class="form-medium-input-style" name="search-product-purchase-for-edit" id="search-product-purchase-for-edit" placeholder="<?= $t['enter_product_name'] ?>..." title="<?= $t['enter_product_name'] ?>"/>
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
									<button type="button" class="button-style-agree disabled" style="pointer-events: none" disabled><?= $t['method_of_payment'] ?></button>
								</div>
								<div class="cat-all-list">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="edit_price_sum"><?= $t['price_sum'] ?>:</label>
												<input class="form-input-style" type="text" name="edit_price_sum" id="edit_price_sum" placeholder="<?= $t['price_sum'] ?>..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="edit_initial"><?= $t['initial'] ?>:</label>
												<input class="form-input-style" type="text" name="edit_initial" id="edit_initial" placeholder="<?= $t['initial'] ?>..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="edit_payment_date"><?= $t['payment_date'] ?>:</label>
												<input class="form-input-style" type="date" name="edit_payment_date" id="edit_payment_date" placeholder="<?= $t['payment_date'] ?>..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="edit_remaining"><?= $t['remaining'] ?>:</label>
												<input class="form-input-style" type="text" name="edit_remaining" id="edit_remaining" placeholder="<?= $t['remaining'] ?>..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="edit_interest">% <?= $t['interest'] ?>:</label>
												<input class="form-medium-input-style" type="text" name="edit_interest" id="edit_interest" value="10" placeholder="<?= $t['percent'] ?>..."/>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="edit_total_interest"><?= $t['total_interest'] ?>:</label>
												<input class="form-medium-input-style" type="text" name="edit_total_interest" id="edit_total_interest" placeholder="<?= $t['total_interest'] ?>..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="edit_installments_month"><?= $t['installments_month'] ?>:</label>
												<select class="form-input-style" name="edit_installments_month" id="edit_installments_month"></select>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="edit_delivery_date"><?= $t['delivery_date'] ?>:</label>
												<input class="form-medium-input-style" type="date" name="edit_delivery_date" id="edit_delivery_date" placeholder="<?= $t['delivery_date'] ?>..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="edit_due"><?= $t['due'] ?>:</label>
												<input class="form-input-style" type="text" name="edit_due" id="edit_due" placeholder="<?= $t['due'] ?>..." disabled/>
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
							<button type="button" class="neutral-btn"><?= $t['cancel'] ?></button>
						</td>
						<td colspan="1" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="<?= $t['update'] ?>" />
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