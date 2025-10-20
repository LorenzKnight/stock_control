<div class="bg-popup" id="add-product-form">
	<div class="formular-frame">
		<form method="post" name="formAddProduct" id="formAddProduct" enctype="multipart/form-data">
			<table width="90%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Add Product</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<div class="drop-area" id="drop-product-area">
							<img class="image-preview" id="product-image-preview" src="" alt="Product Image Preview">
							<p>Drop image here or click to select</p>
							<input type="file" name="product_image" id="product_image" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_1" name="unit_type" value="1" checked>
							<label for="unit-type-1">Single Unit</label>
						</div>
					</td>
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_2" name="unit_type" value="2">
							<label for="unit-type-2">Multi Pack</label>
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="units">Units:</label>
						<input class="form-small-input-style" type="number" name="units" id="units" placeholder="1 units" title="units" disabled/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="weight_unit">Weight/unit (kg):</label>
						 <input class="form-small-input-style" type="text" name="weight_unit" id="weight_unit" placeholder="Weight/unit" title="Weight/unit"/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="total_weight">Total Weight (kg):</label>
						 <input class="form-small-input-style" type="text" name="total_weight" id="total_weight" placeholder="Total Weight" title="Total Weight" disabled/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="product_name">Name:</label>
						<input class="form-medium-input-style" type="text" name="product_name" id="product_name" placeholder="Product Name..." title="Product name"/>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="hs_code">Tariff fraction (HS Code):</label>
						<input class="form-medium-input-style" type="text" name="hs_code" id="hs_code" placeholder="Fraction (HS Code)..." title="fraction (HS Code)"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="product_type">Type:</label>
						<select class="form-input-style" name="product_type" id="product_type"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="product_year">Year:</label>
						<input class="form-medium-input-style input-year-only" type="number" name="product_year" id="product_year" placeholder="Product Year" title="Product Year"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="product_mark">Mark:</label>
						<select class="form-medium-input-style" name="product_mark" id="product_mark"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="product_model">Model:</label>
						<select class="form-medium-input-style" name="product_model" id="product_model"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="product_sub_model">Sub-model:</label>
						<select class="form-medium-input-style" name="product_sub_model" id="product_sub_model"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="product_purpose">Purpose:</label>
						<select class="form-medium-input-style" name="product_purpose" id="product_purpose" required></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="quantity">Quantity:</label>
						<input class="form-medium-input-style" type="number" name="quantity" id="quantity" placeholder="Quantity" title="Quantity"/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="min_quantity">Min Qty:</label>
						<input class="form-small-input-style" type="number" name="min_quantity" id="min_quantity" placeholder="Min Qty = 10" title="Min Quantity"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="currency">Currency:</label>
						<select class="form-input-style" name="currency" id="currency"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="price">Price:</label>
						<input class="form-medium-input-style" type="number" name="price" id="price" placeholder="Product Price" title="Product Price"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="description">Description:</label>
						<textarea class="form-input-style" id="description" name="description" rows="2" cols="35">
						</textarea>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td colspan="3" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Create" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>