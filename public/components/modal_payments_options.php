<div class="bg-popup" id="payments-options">
	<div class="formular-frame">
		<div id="payments-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Payments Options</h2>
						<p id="ord-no-name"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editPaymentBtn">Edit Payment</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deletePaymentBtn">Delete Payment</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
					</td>
				</tr>
			</table>
		</div>
		<div id="edit-payments-modal" style="display: none;">
			<!-- <div class="edit-back-to-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditPayment" id="formEditPayment">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td colspan="6" align="center" valign="middle">
							<h2>Edit Payment</h2>
						</td>      
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<div style="position: relative; display: inline-block; width: 100%;">
								<input class="form-input-style" type="text" name="ord_no" id="ord_no" placeholder="Ord no." title="Enter a valid Ord no." autocomplete="off" required/>
								<div id="ord-no-suggestions" class="autocomplete-box"></div>
							</div>
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
							<select class="form-medium-input-style" name="currency" id="currency" required></select> 
						</td>
						<td width="50%" align="center" valign="middle">
							<select class="form-medium-input-style" name="payment_method" id="payment_method" required></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="number" name="amount" id="amount" placeholder="Enter a amount..." title="Enter a amount" required/>
						</td>
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="text" name="interest" id="interest" placeholder="interest" title="interest" disabled/>
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
								<input type="checkbox" name="payment_status" id="payment_status" value="1" checked>
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
			</form> -->
		</div>
	</div>
</div>