<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<div class="data-container product-container-height">
		<h2 style="margin-left: 10px;"><?= $t['products_list'] ?></h2>
		<div class="product-table flex">
			<div class="product-sidebar">
				<form>
					<table width="90%" align="center" cellspacing="0" style="margin-top: 15px;">
						<tr valign="baseline" class="form_height">
							<td colspan="4" style="padding-bottom: 5px;" align="center" valign="middle">
								<label for="searchField"><?= $t['product_search'] ?>:</label>
								<input class="form-input-style" type="text" name="searchField" id="searchField" class="search-field" placeholder="<?= $t['product_search'] ?>..." title="<?= $t['product_search'] ?>">
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="4" style="border-top: 1px solid var(--border-light); padding-top: 5px;" align="center" valign="middle">
								<label for="search_product_category"><?= $t['mark_category'] ?>:</label>
								<select class="form-input-style" name="search_product_mark" id="search_product_mark"></select>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td width="50%" style="padding-bottom: 5px;" align="center" valign="middle">
								<label for="search_product_model"><?= $t['model'] ?>:</label>
								<select class="form-input-style" name="search_product_model" id="search_product_model"></select>
							</td>
							<td width="50%" style="padding-bottom: 5px;" align="center" valign="middle">
								<label for="search_product_sub_model"><?= $t['sub_model'] ?>:</label>
								<select class="form-input-style" name="search_product_sub_model" id="search_product_sub_model"></select>
							</td>
						</tr>
						<!-- <tr valign="baseline" class="form_height">
							<td colspan="4" style="border-top: 1px solid var(--border-light); padding: 5px 0;" align="center" valign="middle">
								<select class="form-input-style" name="products-order-by" id="products-order-by">
									<option value="">* Order by</option>
								</select>
							</td>
						</tr> -->
						<tr valign="baseline" class="form_height">
							<td colspan="4" style="border-top: 1px solid var(--border-light); padding: 5px 0;" align="center" valign="middle">
								<label for="select-company"><?= $t['company_name'] ?>:</label>
								<select class="form-input-style" name="select-company" id="select-company"></select>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="4" style="border-top: 1px solid var(--border-light); padding-top: 5px;" align="center" valign="middle">
								<p id="selection-notice" class="hidden" style="color:var(--main-bg-blue); font-size: 10px;">Select a company or affiliate to continue create</br>categories or products</p>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="4" align="center" valign="middle">
								<button class="button-style-agree" id="add-product-btn"><?= $t['create_product'] ?></button>
							</td>
						</tr>
						<tr valign="baseline" class="form_height">
							<td colspan="4" style="padding-top: 5px;" align="center" valign="middle">
								<button class="button-style-agree" id="add-category-btn"><?= $t['create_category'] ?></button>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<div class="product-list" id="product-list"></div>
		</div>
	</div>
</div>