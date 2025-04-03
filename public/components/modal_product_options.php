<div class="bg-popup" id="product-options">
	<div class="formular-frame">
		<div id="product-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Product Options</h2>
						<p id="product-name"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="assignSaleBtn">Assign to a sale</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="recaiveInitialBtn">Receive as an initial </button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editProductBtn">Edit Product</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteProductBtn">Delete Product</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
					</td>
				</tr>
			</table>
		</div>
		<div id="edit-product-modal">
			<div style="text-align: center; margin-top: 10px;">
				<button type="button" class="neutral-btn" id="backToMenuBtn">Back</button>
			</div>
			<form method="post" name="formEditProduct" id="formEditProduct">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Edit Product</h2>
						</td>      
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<input class="form-input-style" type="text" name="edit_name" id="edit_name" placeholder="Enter a name..." title="Enter a valid name" required/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<input class="form-input-style" type="text" name="edit_surname" id="edit_surname" placeholder="Enter a surname..." title="Enter a valid surname" required/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<label for="fecha">Birthdate:</label>
							<input class="form-input-style" type="date" name="edit_birthday" id="edit_birthday" placeholder="" title=""/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<input class="form-input-style" type="number" name="edit_phone" id="edit_phone" placeholder="Enter a phone number..." title="Enter a valid phone number"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<label for="rank">User Role / Permissions:</label>
							<select class="form-input-style" name="edit_rank" id="edit_rank"></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<input class="form-input-style" type="email" name="edit_email" id="edit_email" placeholder="Enter a E-Mail..." title="Enter a valid email" required/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<span style="display: block; margin-bottom: 5px;">Status</span>
							<label class="switch">
								<input type="checkbox" name="edit_status" id="edit_status" value="1">
								<span class="slider round"></span>
							</label>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<button type="button" class="neutral-btn">Cancel</button>
						</td>
						<td width="50%" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="Update" />
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<button type="button" class="cancel-btn" id="deleteAccountBtn">Delete Account</button>
						</td>
					</tr> 
				</table> 
			</form>
		</div>
	</div>
</div>