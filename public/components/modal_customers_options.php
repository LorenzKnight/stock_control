<div class="bg-popup" id="customers-options">
	<div class="formular-frame">
		<div id="customers-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= tr('customer_options') ?></h2>
						<p id="customers-name"></p>
					</td>      
				</tr>
				<!-- <tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="assignCustomerSaleBtn">Assign to a sale</button>
					</td>
				</tr> -->
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editCustomerBtn"><?= tr('edit_customer') ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteCustomerBtn"><?= tr('delete_customer') ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= tr('close') ?></button>
					</td>
				</tr>
			</table>
		</div>
		<div id="edit-customers-modal" style="display: none;">
			<div class="edit-back-to-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditCustomer" id="formEditCustomer">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2><?= tr('edit_customer_title') ?></h2>
						</td>      
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<div class="drop-area" id="edit-customer-drop-area">
								<img class="image-preview" id="edit-customer-pic-preview" src="" alt="customer pic preview">
								<p><?= tr('form_drop_image') ?></p>
								<input type="file" name="edit_customer_image" id="edit_customer_image" accept="image/*" style="display: none;">
							</div>
						</td>
					</tr>
				</table>
				<div class="customer-form-section">
					<ul>
						<li id="tab-edit-customer-data"><?= tr('customer_data') ?></li>
						<li id="tab-edit-customer-reference"><?= tr('customer_reference') ?></li>
					</ul>
				</div>
				<div id="edit-customer-data">
					<table width="80%" align="center" cellspacing="0">
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_name"><?= tr('form_name') ?>:</label>
								<input class="form-medium-input-style" type="text" name="edit_customer_name" id="edit_customer_name" placeholder="<?= tr('form_name') ?>..." title="<?= tr('form_name') ?>"/>
							</td>
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_surname"><?= tr('form_surname') ?>:</label>
								<input class="form-medium-input-style" type="text" name="edit_customer_surname" id="edit_customer_surname" placeholder="<?= tr('form_surname') ?>..." title="<?= tr('form_surname') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<label for="edit_customer_email">E-Mail:</label>
								<input class="form-input-style" type="text" name="edit_customer_email" id="edit_customer_email" placeholder="Enter a email..." title="Enter a valid name"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<label for="edit_customer_address"><?= tr('address') ?>:</label>
								<input class="form-input-style" type="text" name="edit_customer_address" id="edit_customer_address" placeholder="<?= tr('address') ?>..." title="<?= tr('address') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_country_code"><?= tr('form_country_code') ?>:</label>
								<select class="form-medium-input-style" name="edit_customer_country_code" id="edit_customer_country_code" required></select>
							</td>
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_phone"><?= tr('phone') ?>:</label>
								<input class="form-medium-input-style" type="text" name="edit_customer_phone" id="edit_customer_phone" placeholder="<?= tr('phone') ?>..." title="<?= tr('phone') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_type"><?= tr('customer_type') ?>:</label>
								<select class="form-input-style" name="edit_customer_type" id="edit_customer_type"></select>
							</td>
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_birthday"><?= tr('form_birthday') ?>:</label>
								<input class="form-medium-input-style" type="date" name="edit_customer_birthday" id="edit_customer_birthday" placeholder="<?= tr('form_birthday') ?>..." title="<?= tr('form_birthday') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_document_type"><?= tr('document_type') ?>:</label>
								<select class="form-input-style" name="edit_customer_document_type" id="edit_customer_document_type"></select>
							</td>
							<td width="50%" align="center" valign="middle">
								<label for="edit_customer_document_no"><?= tr('document_no') ?>:</label>
								<input class="form-medium-input-style" type="text" name="edit_customer_document_no" id="edit_customer_document_no" placeholder="<?= tr('document_no') ?>..." title="<?= tr('document_no') ?>"/>
							</td>
						</tr>
					</table>
				</div>
				<div id="edit-customer-reference">
					<table width="80%" align="center" cellspacing="0">
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<label for="edit_references_1"><?= tr('references_1') ?>:</label>
								<input class="form-input-style" type="text" name="edit_references_1" id="edit_references_1" placeholder="<?= tr('references_1') ?>..." title="<?= tr('references_1') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<label for="edit_references_1_country_code"><?= tr('form_country_code') ?>:</label>
								<select class="form-medium-input-style" name="edit_references_1_country_code" id="edit_references_1_country_code" required></select>
							</td>
							<td width="50%" align="center" valign="middle">
								<label for="edit_references_1_phone"><?= tr('references_1_phone') ?>:</label>
								<input class="form-medium-input-style" type="text" name="edit_references_1_phone" id="edit_references_1_phone" placeholder="<?= tr('references_1_phone') ?>..." title="<?= tr('references_1_phone') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<label for="edit_references_2"><?= tr('references_2') ?>:</label>
								<input class="form-input-style" type="text" name="edit_references_2" id="edit_references_2" placeholder="<?= tr('references_2') ?>..." title="<?= tr('references_2') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<label for="edit_references_2_country_code"><?= tr('form_country_code') ?>:</label>
								<select class="form-medium-input-style" name="edit_references_2_country_code" id="edit_references_2_country_code" required></select>
							</td>
							<td width="50%" align="center" valign="middle">
								<label for="edit_references_2_phone"><?= tr('references_2_phone') ?>:</label>
								<input class="form-medium-input-style" type="text" name="edit_references_2_phone" id="edit_references_2_phone" placeholder="<?= tr('references_2_phone') ?>..." title="<?= tr('references_2_phone') ?>"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
								<span style="display: block;"><?= tr('status') ?></span>
							</td>
							<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
								<label class="switch">
									<input type="checkbox" name="edit_customer_status" id="edit_customer_status" value="1">
									<span class="slider round"></span>
								</label>
							</td>
						</tr>
					</table>
				</div>
				<table width="80%" style="margin-top: 10px;" align="center" cellspacing="0">
					<tr valign="baseline" class="form_height">
						<td colspan="1" align="center" valign="middle">
							<button type="button" class="neutral-btn"><?= tr('cancel') ?></button>
						</td>
						<td colspan="1" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="<?= tr('update') ?>" />
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div id="assign-customers-sale-section" style="display: none;">
			<div class="edit-back-to-menu-btn">
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