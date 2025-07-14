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
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="product_name" id="product_name" placeholder="Product Name..." title="Product name"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<select class="form-input-style" name="product_type" id="product_type"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<input class="form-medium-input-style input-year-only" type="number" name="product_year" id="product_year" placeholder="Product Year" title="Product Year"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<select class="form-input-style" name="product_mark" id="product_mark"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<select class="form-input-style" name="product_model" id="product_model"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<select class="form-input-style" name="product_sub_model" id="product_sub_model"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<input class="form-medium-input-style" type="number" name="quantity" id="quantity" placeholder="Quantity" title="Quantity"/>
					</td>
					<td colspan="3" align="center" valign="middle">
						<input class="form-medium-input-style" type="number" name="min_quantity" id="min_quantity" placeholder="Min Qty = 10" title="Min Quantity"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<select class="form-input-style" name="currency" id="currency"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<input class="form-medium-input-style" type="number" name="prise" id="prise" placeholder="Product Prise" title="Product Prise"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
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