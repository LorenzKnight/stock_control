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
				<table width="90%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Edit Product</h2>
						</td>      
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<div class="drop-area" id="edit-drop-product-area">
								<img class="image-preview" id="edit-product-image-preview" src="" alt="Product Image Preview">
								<p>Drop image here or click to select</p>
								<input type="file" name="edit_Product_image" id="edit_Product_image" accept="image/*" style="display: none;">
							</div>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<label for="edit_product_name">Name:</label>
							<input class="form-input-style" type="text" name="edit_product_name" id="edit_product_name" placeholder="Product Name..." title="Product name"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<label for="edit_product_type">Type:</label>
							<select class="form-input-style" name="edit_product_type" id="edit_product_type"></select>
						</td>
						<td colspan="3" align="center" valign="middle">
							<label for="edit_product_year">Year:</label>
							<input class="form-medium-input-style input-year-only" type="number" name="edit_product_year" id="edit_product_year" placeholder="Product Year" title="Product Year"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<select class="form-input-style" name="edit_product_mark" id="edit_product_mark"></select>
						</td>
						<td colspan="2" align="center" valign="middle">
							<select class="form-input-style" name="edit_product_model" id="edit_product_model"></select>
						</td>
						<td colspan="2" align="center" valign="middle">
							<select class="form-input-style" name="edit_product_sub_model" id="edit_product_sub_model"></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<label for="edit_quantity">Quantity:</label>
							<input class="form-medium-input-style" type="number" name="edit_quantity" id="edit_quantity" placeholder="Quantity" title="Quantity"/>
						</td>
						<td colspan="3" align="center" valign="middle">
							<label for="edit_min_quantity">Min Qty:</label>
							<input class="form-medium-input-style" type="number" name="edit_min_quantity" id="edit_min_quantity" placeholder="Min Qty = 10" title="Min Quantity"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<label for="edit_currency">Currency:</label>
							<select class="form-input-style" name="edit_currency" id="edit_currency" disabled></select>
						</td>
						<td colspan="3" align="center" valign="middle">
							<label for="edit_prise">Price:</label>
							<input class="form-medium-input-style" type="number" name="edit_prise" id="edit_prise" placeholder="Product Prise" title="Product Prise"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<label for="edit_description">Description:</label>
							<textarea class="form-input-style" id="edit_description" name="edit_description" rows="2" cols="35">
							</textarea>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<button type="button" class="neutral-btn">Cancel</button>
						</td>
						<td colspan="3" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="Update" />
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