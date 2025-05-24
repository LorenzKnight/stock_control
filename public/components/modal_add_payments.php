<div class="bg-popup" id="add-payment-form">
	<div class="formular-frame">
		<form method="post" name="formAddPayment" id="formAddPayment">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Create a Payment</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="ord_no" id="ord_no" placeholder="Ord no." title="Enter a valid Ord no." required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="person_who_paid" id="customer" placeholder="Person Who Is Paying." title="Enter a valid Customer Name."/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
                    <td width="50%" align="center" valign="middle">
						<select class="form-medium-input-style" name="payer_document_type" id="payer_document_type"></select>
					</td>
					<td width="50%" align="center" valign="middle">
                        <input class="form-medium-input-style" type="text" name="payer_document_no" id="payer_document_no" placeholder="Enter a doc no..." title="Enter a doc no"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
                    <td width="50%" align="center" valign="middle">
						<select class="form-medium-input-style" name="currency" id="currency"></select>
					</td>
					<td width="50%" align="center" valign="middle">
						<select class="form-medium-input-style" name="payment_method" id="payment_method"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<input class="form-medium-input-style" type="number" name="amount" id="amount" placeholder="Enter a amount..." title="Enter a amount"/>
					</td>
					<td width="50%" align="center" valign="middle">
						<input class="form-medium-input-style" type="number" name="interest" id="interest" placeholder="interest" title="interest" disabled/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="number" name="payer_phone" id="payer_phone" placeholder="Enter a phone number..." title="Enter a valid phone number"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="email" name="customer_email" id="customer_email" placeholder="Enter a E-Mail..." title="Enter a valid email" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<span style="display: block; margin: 15px 0 5px;">Status</span>
						<label class="switch" style="margin-bottom: 20px;">
							<input type="checkbox" name="edit_status" id="edit_status" value="1" checked>
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Make Payment" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>