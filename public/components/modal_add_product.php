<div class="bg-popup" id="add-product-form">
	<div class="formular-frame">
		<form action="stock.php" method="post" name="formAddProduct" id="formAddProduct" enctype="multipart/form-data">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="2" align="center" valign="middle">
						<h2>Add Product</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<div class="drop-area" id="drop-product-area">
							<img class="image-preview" id="product-image-preview" src="" alt="Product Image Preview">
							<p>Drop logo image here or click to select</p>
							<input type="file" name="Product_image" id="Product_image" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" type="text" name="product_name" id="product_name" placeholder="Product Name..." title="Product name"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<select class="form-input-style" name="product_type" id="product_type">
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
						<select class="form-input-style" name="product_mark" id="product_mark">

						</select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<select class="form-input-style" name="product_model" id="product_model">

						</select>
					</td>
					<td width="50%" align="center" valign="middle">
						<select class="form-input-style" name="product_sub_model" id="product_sub_model">

						</select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<input class="form-medium-input-style input-year-only" type="number" name="product_year" id="product_year" placeholder="Product Year" title="Product Year"/>
					</td>
					<td width="50%" align="center" valign="middle">
						<input class="form-medium-input-style" type="number" name="prise" id="prise" placeholder="Product Prise" title="Product Prise"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<textarea class="form-input-style" id="description" name="description" rows="5" cols="35">
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
</div>