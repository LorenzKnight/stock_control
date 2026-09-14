<div class="bg-popup" id="add-customers-form">
	<div class="formular-medium-frame">
		<form method="post" name="formCustomers" id="formCustomers" enctype="multipart/form-data">
			<table width="95%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= tr('add_customer_title') ?></h2>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<div class="lists-container">
							<div class="formular-list">
								<table width="95%" align="center" cellspacing="0">
									<tr valign="baseline">
										<td colspan="2" align="center" valign="middle">
											<div class="drop-area" id="customer-drop-area">
												<img class="image-preview" id="customer-pic-preview" src="" alt="customer pic preview">
												<p><?= tr('form_drop_image') ?></p>
												<input type="file" name="customer_image" id="customer_image" accept="image/*" style="display: none;">
											</div>
										</td>
									</tr>
									<tr valign="baseline">
										<td width="50%" align="center" valign="middle">
											<label for="customer_name"><?= tr('form_name') ?>:</label>
											<input class="form-medium-input-style" type="text" name="customer_name" id="customer_name" placeholder="<?= tr('form_name') ?>..." title="<?= tr('form_name') ?>"/>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="customer_surname"><?= tr('form_surname') ?>:</label>
											<input class="form-medium-input-style" type="text" name="customer_surname" id="customer_surname" placeholder="<?= tr('form_surname') ?>..." title="<?= tr('form_surname') ?>"/>
										</td>
									</tr>
									<tr valign="baseline">
										<td colspan="2" align="center" valign="middle">
											<label for="customer_email">E-Mail:</label>
											<input class="form-input-style" type="text" name="customer_email" id="customer_email" placeholder="Email..." title="Email"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td colspan="2" align="center" valign="middle">
											<label for="customer_address"><?= tr('address') ?>:</label>
											<input class="form-input-style" type="text" name="customer_address" id="customer_address" placeholder="<?= tr('address') ?>..." title="<?= tr('address') ?>"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td width="50%" align="center" valign="middle">
											<label for="customer_country_code"><?= tr('form_country_code') ?>:</label>
											<select class="form-medium-input-style" name="customer_country_code" id="customer_country_code" required></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="customer_phone"><?= tr('phone') ?>:</label>
											<input class="form-medium-input-style" type="text" name="customer_phone" id="customer_phone" placeholder="<?= tr('phone') ?>..." title="<?= tr('phone') ?>"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td width="50%" align="center" valign="middle">
											<label for="customer_type"><?= tr('customer_type') ?>:</label>
											<select class="form-input-style" name="customer_type" id="customer_type"></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="customer_birthday"><?= tr('birthdate') ?>:</label>
											<input class="form-medium-input-style" type="date" name="customer_birthday" id="customer_birthday" placeholder="<?= tr('birthdate') ?>..." title="<?= tr('birthdate') ?>"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td width="50%" align="center" valign="middle">
											<label for="customer_document_type"><?= tr('document_type') ?>:</label>
											<select class="form-input-style" name="customer_document_type" id="customer_document_type"></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="customer_document_no"><?= tr('document_no') ?>:</label>
											<input class="form-medium-input-style" type="text" name="customer_document_no" id="customer_document_no" placeholder="<?= tr('document_no') ?>..." title="<?= tr('document_no') ?>"/>
										</td>
									</tr>
								</table>
							</div>
							<div class="formular-list">
								<table width="95%" align="center" cellspacing="0">
									<tr valign="baseline" >
										<td colspan="2" align="center" valign="middle">
											<label for="references_1"><?= tr('references_1') ?>:</label>
											<input class="form-input-style" type="text" name="references_1" id="references_1" placeholder="<?= tr('references_1') ?>..." title="<?= tr('references_1') ?>"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td width="50%" align="center" valign="middle">
											<label for="references_1_country_code"><?= tr('form_country_code') ?>:</label>
											<select class="form-medium-input-style" name="references_1_country_code" id="references_1_country_code" required></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="references_1_phone"><?= tr('references_1_phone') ?>:</label>
											<input class="form-medium-input-style" type="text" name="references_1_phone" id="references_1_phone" placeholder="<?= tr('references_1_phone') ?>..." title="<?= tr('references_1_phone') ?>"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td colspan="2" align="center" valign="middle">
											<label for="references_2"><?= tr('references_2') ?>:</label>
											<input class="form-input-style" type="text" name="references_2" id="references_2" placeholder="<?= tr('references_2') ?>..." title="<?= tr('references_2') ?>"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td width="50%" align="center" valign="middle">
											<label for="references_2_country_code"><?= tr('form_country_code') ?>:</label>
											<select class="form-medium-input-style" name="references_2_country_code" id="references_2_country_code" required></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<label for="references_2_phone"><?= tr('references_2_phone') ?>:</label>
											<input class="form-medium-input-style" type="text" name="references_2_phone" id="references_2_phone" placeholder="<?= tr('references_2_phone') ?>..." title="<?= tr('references_2_phone') ?>"/>
										</td>
									</tr>
									<tr valign="baseline" >
										<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
											<span style="display: block;"><?= tr('status') ?>:</span>
										</td>
										<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
											<label class="switch">
												<input type="checkbox" name="customer_status" id="customer_status" value="1" checked>
												<span class="slider round"></span>
											</label>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</td>
				</tr>
				<tr valign="baseline" >
					<td colspan="6" align="center" valign="middle">
						<div class="footer-buttons">
							<button
								type="button"
								class="neutral-btn"
							>
								<?= tr('cancel') ?>
							</button>
					
							<button
								type="submit"
								class="button-style-agree"
							>
								<?= tr('create') ?>
							</button>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>