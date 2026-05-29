<div class="bg-popup" id="add-sale-form">
	<div class="formular-big-frame">
		<form method="post" name="formAddSale" id="formAddSale">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2><?= tr('create_sale_title') ?></h2>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="2" align="center" valign="middle">
						<div class="formular-customers-list">
							<div class="create-list-holder">
								<input class="form-input-style" type="text" name="search-customer" id="search-customer" placeholder="<?= tr('enter_name_or_document') ?>..." title="<?= tr('enter_name_or_document') ?>"/>
							</div>
							<div class="cat-all-list">
								<table id="select-customers-list" cellspacing="0"></table>
							</div>
						</div>
					</td>
					<td colspan="2" align="center" valign="middle">
						<div class="formular-customers-list">
							<div class="create-list-holder">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline">
										<td width="60%" align="center" valign="middle">
											<input type="text" class="form-medium-input-style" name="search-product-purchase" id="search-product-purchase" placeholder="<?= tr('enter_product_name') ?>..." title="<?= tr('enter_product_name') ?>"/>
										</td>
										<td width="40%" align="center" valign="middle">
											<select class="form-input-style" name="search-product-mark" id="search-product-mark"></select>
										</td>
									</tr>
								</table>
							</div>
							<div class="cat-all-list">
								<table id="select-product-list" cellspacing="0"></table>
							</div>
						</div>
					</td>
					<td colspan="2" align="center" valign="middle">
						<div class="formular-customers-list">
							<div class="create-list-holder" style="padding: 5px 0 15px;">
								<button type="button" class="button-style-agree disabled" style="pointer-events: none" disabled><?= tr('method_of_payment') ?></button>
							</div>
							<div class="cat-all-list">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline" class="form_height">
										<td width="50%" align="center" valign="middle">
											<label for="currency"><?= tr('currency') ?>:</label>
											<select class="form-input-style" name="currency" id="currency" required></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="price_sum"><?= tr('price_sum') ?>:</label>
											<input class="form-medium-input-style" type="text" name="price_sum" id="price_sum" placeholder="<?= tr('price_sum') ?>..." disabled/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<label for="initial"><?= tr('initial') ?>:</label>
											<input class="form-input-style" type="text" name="initial" id="initial" placeholder="<?= tr('initial') ?>..."/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<label for="payment_date"><?= tr('payment_date') ?>:</label>
											<input class="form-input-style" type="date" name="payment_date" id="payment_date" placeholder="<?= tr('payment_date') ?>..."/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<label for="remaining"><?= tr('remaining') ?>:</label>
											<input class="form-input-style" type="text" name="remaining" id="remaining" placeholder="<?= tr('remaining') ?>..." disabled/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td width="50%" align="center" valign="middle">
											<label for="interest">% <?= tr('interest') ?>:</label>
											<input class="form-medium-input-style" type="text" name="interest" id="interest" value="10" placeholder="<?= tr('percent') ?>..."/>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="total_interest"><?= tr('total_interest') ?>:</label>
											<input class="form-medium-input-style" type="text" name="total_interest" id="total_interest" placeholder="<?= tr('total_interest') ?>..." disabled/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td width="50%" align="center" valign="middle">
											<label for="installments_month"><?= tr('installments_month') ?>:</label>
											<select class="form-input-style" name="installments_month" id="installments_month"></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="delivery_date"><?= tr('delivery_date') ?>:</label>
											<input class="form-medium-input-style" type="date" name="delivery_date" id="delivery_date" placeholder="<?= tr('delivery_date') ?>..."/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<label for="due"><?= tr('due') ?>:</label>
											<input class="form-input-style" type="text" name="due" id="due" placeholder="<?= tr('due') ?>..." disabled/>
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
						<button type="button" class="neutral-btn"><?= tr('cancel') ?></button>
					</td>
					<td colspan="1" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= tr('create') ?>" />
					</td>
					<td colspan="2" align="center" valign="middle">
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>