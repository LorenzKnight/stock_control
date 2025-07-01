<div class="bg-popup" id="customers-options">
	<div class="formular-frame">
		<div id="customers-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Customers Options</h2>
						<p id="customers-name"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="assignCustomerSaleBtn">Assign to a sale</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editCustomerBtn">Edit Customer</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteCustomerBtn">Delete Customer</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
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
							<h2>Edit Customer</h2>
						</td>      
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<div class="drop-area" id="edit-customer-drop-area">
								<img class="image-preview" id="edit-customer-pic-preview" src="" alt="customer pic preview">
								<p>Drop image here or click to select</p>
								<input type="file" name="customer_image" id="edit_customer_image" accept="image/*" style="display: none;">
							</div>
						</td>
					</tr>
				</table>
				<div class="customer-form-menu">
					<ul>
						<li id="tab-edit-customer-data">customers data</li>
						<li id="tab-edit-customer-reference">customers Reference</li>
					</ul>
				</div>
				<div id="edit-customer-data">
					<table width="80%" align="center" cellspacing="0">
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<input class="form-medium-input-style" type="text" name="edit_customer_name" id="edit_customer_name" placeholder="Enter a name..." title="Enter a valid name"/>
							</td>
							<td width="50%" align="center" valign="middle">
								<input class="form-medium-input-style" type="text" name="edit_customer_surname" id="edit_customer_surname" placeholder="Enter a surname..." title="Enter a valid surname"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<input class="form-input-style" type="text" name="edit_customer_email" id="edit_customer_email" placeholder="Enter a email..." title="Enter a valid name"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<input class="form-input-style" type="text" name="edit_customer_address" id="edit_customer_address" placeholder="Enter a address..." title="Enter a valid address"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<input class="form-medium-input-style" type="text" name="edit_customer_phone" id="edit_customer_phone" placeholder="Enter a phone..." title="Enter a valid phone"/>
							</td>
							<td width="50%" align="center" valign="middle">
								<input class="form-medium-input-style" type="date" name="edit_customer_birthday" id="edit_customer_birthday" placeholder="Enter a birthday date..." title="Enter a birthday date"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<select class="form-input-style" name="edit_customer_document_type" id="edit_customer_document_type"></select>
							</td>
							<td width="50%" align="center" valign="middle">
								<input class="form-medium-input-style" type="text" name="edit_customer_document_no" id="edit_customer_document_no" placeholder="Document no..." title="Document no"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" align="center" valign="middle">
								<select class="form-input-style" name="edit_customer_type" id="edit_customer_type"></select>
							</td>
							<td width="50%" align="center" valign="middle">
								<select class="form-input-style" name="edit_customer_status" id="edit_customer_status"></select>
							</td>
						</tr>
					</table>
				</div>
				<div id="edit-customer-reference">
					<table width="80%" align="center" cellspacing="0">
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<input class="form-input-style" type="text" name="edit_references_1" id="edit_references_1" placeholder="Reference 1 Name and Surname..." title="Enter a valid Name and Surname"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<input class="form-input-style" type="text" name="edit_references_1_phone" id="edit_references_1_phone" placeholder="Reference 1 Phone..." title="Enter a valid phone"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<input class="form-input-style" type="text" name="edit_references_2" id="edit_references_2" placeholder="Reference 2 Name and Surname..." title="Enter a valid Name and Surname"/>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="2" align="center" valign="middle">
								<input class="form-input-style" type="text" name="edit_references_2_phone" id="edit_references_2_phone" placeholder="Reference 2 Phone..." title="Enter a valid phone"/>
							</td>
						</tr>
					</table>
				</div>
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline" class="form_height">
						<td colspan="1" align="center" valign="middle">
							<button type="button" class="neutral-btn">Cancel</button>
						</td>
						<td colspan="1" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="Update" />
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