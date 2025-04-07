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
						<button type="button" class="button-style-agree" id="receiveInitialBtn">Receive as an initial </button>
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
		<div id="edit-product-modal" style="display: none;">
			<div class="back-to-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditProduct" id="formEditProduct">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Edit Product</h2>
						</td>      
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<div class="drop-area" id="drop-product-area">
								<img class="image-preview" id="edit-product-image-preview" src="" alt="Product Image Preview">
								<p>Drop logo image here or click to select</p>
								<input type="file" name="edit_Product_image" id="edit_Product_image" accept="image/*" style="display: none;">
							</div>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<input class="form-input-style" type="text" name="edit_product_name" id="edit_product_name" placeholder="Product Name..." title="Product name"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="edit_product_type" id="edit_product_type">
								<option value="">Select a Typ</option>
								<option value="1">Motorcycle</option>
								<option value="2">Car</option>
								<option value="3">SUV</option>
								<option value="4">Pickup Truck</option>
								<option value="5">Van</option>
								<option value="6">Minibus</option>
								<option value="7">Bus</option>
								<option value="8">Light Truck</option>
								<option value="9">Medium Truck</option>
								<option value="10">Heavy Truck</option>
								<option value="11">Trailer Truck / Articulated Lorry</option>
								<option value="12">Construction Vehicle</option>
								<option value="13">Agricultural Vehicle</option>
							</select>
						</td>
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="edit_product_mark" id="edit_product_mark"></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="edit_product_model" id="edit_product_model"></select>
						</td>
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="edit_product_sub_model" id="edit_product_sub_model"></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style input-year-only" type="number" name="edit_product_year" id="edit_product_year" placeholder="Product Year" title="Product Year"/>
						</td>
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="number" name="edit_prise" id="edit_prise" placeholder="Product Prise" title="Product Prise"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<textarea class="form-input-style" id="edit_description" name="edit_description" rows="5" cols="35">
							</textarea>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="1" align="center" valign="middle">
							<button type="button" class="neutral-btn">Cancel</button>
						</td>
						<td colspan="1" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="Create" />
						</td>
					</tr>
				</table> 
			</form>
		</div>
		<div id="receive-as-initial" style="display: none;">
			<div class="back-to-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditProduct" id="formEditProduct">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Receive as an initial</h2>
						</td>      
					</tr>
				</table>
			</form>
		</div>
		<div id="assign-sale-section" style="display: none;">
			<div class="back-to-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditProduct" id="formEditProduct">
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