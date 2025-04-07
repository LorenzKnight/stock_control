<div class="container">
	<div class="data-container" style="height: 780px;">
		<h2 style="margin-left: 10px;">Products List</h2>
		<div class="product-table flex">
			<div class="product-sidebar">
				<table width="90%" align="center" cellspacing="0" style="margin-top: 15px;">
					<tr valign="baseline" class="form_height">
						<td colspan="4" align="center" valign="middle">
							<input type="text" name="searchField" id="searchField" class="search-field" placeholder="Product No.">
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="4" align="center" valign="middle">
							<select class="form-input-style" name="sarch_product_mark" id="sarch_product_mark"></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="sarch_product_model" id="sarch_product_model"></select>
						</td>
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="sarch_product_sub_model" id="sarch_product_sub_model"></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="4" style="border-top: 1px solid #ccc; padding-top: 10px;" align="center" valign="middle">
							<button class="button-style-agree" id="add-product-btn">Create Product</button>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="4" align="center" valign="middle">
							<button class="button-style-agree" id="add-category-btn">Create Sub/Category</button>
						</td>
					</tr>
				</table>
			</div>
			<div class="product-list" id="product-list"></div>
		</div>
	</div>
</div>