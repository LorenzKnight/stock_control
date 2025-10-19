<div class="bg-popup" id="shipping-options">
	<div id="formular-frame" class="formular-frame">
		<div id="shipping-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Shipping Options</h2>
						<p id="shipping-no"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editShippingBtn">Edit Shipping</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="addLoadBtn">Add Load</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="">Print Label</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteShippingBtn">Delete Shipping</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
					</td>
				</tr>
			</table>
		</div>
		<div id="edit-shipping-modal" style="display: none;">
			<div class="back-to-shipping-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditShipping" id="formEditShipping">
				<!-- <table width="90%" align="center" cellspacing="0">
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
						<td colspan="3" align="center" valign="middle">
							<div class="product-type-selection">
								<input type="radio" id="edit_unit_type_1" name="edit_unit_type" value="1" checked>
								<label for="edit_unit_type_1">Single Unit</label>
							</div>
						</td>
						<td colspan="3" align="center" valign="middle">
							<div class="product-type-selection">
								<input type="radio" id="edit_unit_type_2" name="edit_unit_type" value="2">
								<label for="edit_unit_type_2">Multi Pack</label>
							</div>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<label for="units">Units:</label>
							<input class="form-small-input-style" type="number" name="edit_units" id="edit_units" placeholder="1 units" title="units" disabled/>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="weight_unit">Weight/unit (kg):</label>
							<input class="form-small-input-style" type="text" name="edit_weight_unit" id="edit_weight_unit" placeholder="Weight/unit" title="Weight/unit"/>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="total_weight">Total Weight (kg):</label>
							<input class="form-small-input-style" type="text" name="edit_total_weight" id="edit_total_weight" placeholder="Total Weight" title="Total Weight" disabled/>
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
							<label for="edit_product_mark">Mark:</label>
							<select class="form-input-style" name="edit_product_mark" id="edit_product_mark"></select>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_product_model">Model:</label>
							<select class="form-input-style" name="edit_product_model" id="edit_product_model"></select>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_product_sub_model">Sub-model:</label>
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
							<label for="edit_price">Price:</label>
							<input class="form-medium-input-style" type="number" name="edit_price" id="edit_price" placeholder="Product Price" title="Product Price"/>
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
				</table>  -->
			</form>
		</div>
		<div id="add-load-modal" style="display: none;">
			<div class="back-to-shipping-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formAddLoad" id="formAddLoad">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Add Load</h2>
						</td>      
					</tr>
					<tr valign="baseline">
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<input class="form-input-style" type="text" name="search-shipping-customer" id="search-shipping-customer" placeholder="Enter a name or Doc No..." title="Enter a valid name"/>
								</div>
								<div class="cat-all-list">
									<table id="select-shipping-customers-list" cellspacing="0"></table>
								</div>
							</div>
						</td>
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td width="60%" align="center" valign="middle">
												<input type="text" class="form-medium-input-style" name="search-product-for-shipping" id="search-product-for-shipping" placeholder="Enter Product No..." title="Enter a valid Product No."/>
											</td>
											<td width="40%" align="center" valign="middle">
												<select class="form-input-style" name="search-product-mark-for-shipping" id="search-product-mark-for-shipping"></select>
											</td>
										</tr>
									</table>
								</div>
								<div class="cat-all-list">
									<table id="select-product-list-for-shipping" cellspacing="0"></table>
								</div>
							</div>
						</td>
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder" style="padding: 5px 0 15px;">
									<button type="button" class="button-style-agree disabled" style="pointer-events: none" disabled>Load Details</button>
								</div>
								<div class="cat-all-list">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="shipping_from_currency">From currency:</label>
												<select class="form-input-style" name="shipping_from_currency" id="shipping_from_currency" required></select>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="shipping_price">Price/kg:</label>
												<input class="form-medium-input-style" type="text" name="shipping_price" id="shipping_price" placeholder="Price/kg..."/>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="total_kg">Total kg:</label>
												<input class="form-medium-input-style" type="text" name="total_kg" id="total_kg" placeholder="Total kg..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="price_sum">Price Sum:</label>
												<input class="form-medium-input-style" type="text" name="price_sum" id="price_sum" placeholder="Price sum..." disabled/>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="discount">Discount:</label>
												<input class="form-medium-input-style" type="text" name="discount" id="discount" placeholder="Discount..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="taxes">Taxes %:</label>
												<input class="form-medium-input-style" type="text" name="taxes" id="taxes" placeholder="Taxes %..."/>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="total">Price Total:</label>
												<input class="form-medium-input-style" type="text" name="total" id="total" placeholder="Total..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="shipping_to_currency">To Currency:</label>
												<select class="form-medium-input-style" name="shipping_to_currency" id="shipping_to_currency" required></select>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="total_exchanged">Total Exchanged:</label>
												<input class="form-medium-input-style" type="text" name="total_exchanged" id="total_exchanged" placeholder="Total exchanged..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="destination">Customer destination:</label>
												<input class="form-input-style" type="text" name="destination" id="destination" placeholder="Customer destination..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="comment">Comment:</label>
												<textarea class="form-input-style" id="comment" name="comment" rows="5" cols="35">
												</textarea>
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
							<button type="button" class="neutral-btn">Cancel</button>
						</td>
						<td colspan="1" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="Create" />
						</td>
						<td colspan="2" align="center" valign="middle">
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div id="assign-sale-section" style="display: none;">
			<div class="back-to-shipping-menu-btn">
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