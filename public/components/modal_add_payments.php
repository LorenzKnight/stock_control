<div class="bg-popup" id="add-payment-form">
	<div class="formular-frame">
		<form method="post" name="formAddPayment" id="formAddPayment">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2><?= tr('crete_payment_title') ?></h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
                        <div style="position: relative; display: inline-block; width: 100%;">
							<label for="ord_no">Ord no.:</label>
							<input class="form-input-style" type="text" name="ord_no" id="ord_no" placeholder="Ord no." title="Enter a valid Ord no." autocomplete="off" required/>
							<div id="ord-no-suggestions" class="autocomplete-box"></div>
						</div>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="person_who_paid"><?= tr('person_who_pays') ?>:</label>
						<input class="form-input-style" type="text" name="person_who_paid" id="customer" placeholder="<?= tr('person_who_pays') ?>." title="<?= tr('person_who_pays') ?>"/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
                    <td width="50%" align="center" valign="middle">
						<label for="payer_document_type"><?= tr('document_type') ?>:</label>
						<select class="form-medium-input-style" name="payer_document_type" id="payer_document_type"></select>
					</td>
					<td width="50%" align="center" valign="middle">
						<label for="payer_document_no"><?= tr('document_no') ?>:</label>
                        <input class="form-medium-input-style" type="text" name="payer_document_no" id="payer_document_no" placeholder="Enter a doc no..." title="Enter a doc no"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
                    <td width="50%" align="center" valign="middle">
						<label for="currency"><?= tr('currency') ?>:</label>
						<select class="form-medium-input-style" name="currency" id="currency" required></select> 
					</td>
					<td width="50%" align="center" valign="middle">
						<label for="payment_method"><?= tr('method_of_payment') ?>:</label>
						<select class="form-medium-input-style" name="payment_method" id="payment_method" required></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<label for="amount"><?= tr('amount') ?>:</label>
						<input class="form-medium-input-style" type="text" name="amount" id="amount" placeholder="<?= tr('amount') ?>..." title="<?= tr('amount') ?>" required/>
					</td>
					<td width="50%" align="center" valign="middle">
						<label for="interest"><?= tr('interest') ?>:</label>
						<input class="form-medium-input-style" type="text" name="interest" id="interest" placeholder="<?= tr('interest') ?>" title="<?= tr('interest') ?>" disabled/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="payer_phone"><?= tr('phone') ?>:</label>
						<input class="form-input-style" type="number" name="payer_phone" id="payer_phone" placeholder="Enter a phone number..." title="Enter a valid phone number"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="customer_email">E-mail:</label>

						<input class="form-input-style" type="email" name="customer_email" id="customer_email" placeholder="Enter a E-Mail..." title="Enter a valid email" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
						<span style="display: block;"><?= tr('status') ?></span>
					</td>
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
						<label class="switch">
							<input type="checkbox" name="payment_status" id="payment_status" value="1" checked>
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" style="padding: 15px 0" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= tr('cancel') ?></button>
					</td>
					<td width="50%" style="padding: 15px 0" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= tr('confirm') ?>" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>