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
					<td style="padding: 0 1%;" width="48%" align="center" valign="middle">
						<input class="form-input-style" type="text" name="product_type" id="product_type" placeholder="Product Type" title="Product Type"/>
					</td>
					<td style="padding: 0 1%;" width="48%" align="center" valign="middle">
						<input class="form-input-style" type="text" name="product_mark" id="product_mark" placeholder="Product Mark" title="Product Mark"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td style="padding: 0 1%;" width="48%" align="center" valign="middle">
						<input class="form-input-style" type="text" name="product_model" id="product_model" placeholder="Product Model" title="Product Model"/>
					</td>
					<td style="padding: 0 1%;" width="48%" align="center" valign="middle">
						<input class="form-input-style" type="text" name="product_sub_model" id="product_sub_model" placeholder="Product Sub-Model" title="Product Sub-Model"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td style="padding: 0 1%;" width="48%" align="center" valign="middle">
						<input class="form-input-style" type="text" name="product_year" id="product_year" placeholder="Product Year" title="Product Year"/>
					</td>
					<td style="padding: 0 1%;" width="48%" align="center" valign="middle">
						<input class="form-input-style" type="text" name="prise" id="prise" placeholder="Product Prise" title="Product Prise"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" type="text" name="description" id="description" placeholder="Description" title="Description"/>
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