<div class="bg-popup" id="product-options">
	<div class="formular-frame">
		<div id="product-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= $t['product_options'] ?></h2>
						<p id="product-name"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="requestProductBtn"><?= $t['request_product'] ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editProductBtn"><?= $t['edit_product'] ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteProductBtn"><?= $t['delete_product'] ?></button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= $t['close'] ?></button>
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
							<h2><?= $t['edit_product_title'] ?></h2>
						</td>      
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<div class="drop-area" id="edit-drop-product-area">
								<img class="image-preview" id="edit-product-image-preview" src="" alt="Product Image Preview">
								<p><?= $t['form_drop_image'] ?></p>
								<input type="file" name="edit_Product_image" id="edit_Product_image" accept="image/*" style="display: none;">
							</div>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<div class="product-type-selection">
								<input type="radio" id="edit_unit_type_1" name="edit_unit_type" value="1" checked>
								<label for="edit_unit_type_1"><?= $t['single_unit'] ?></label>
							</div>
						</td>
						<td colspan="3" align="center" valign="middle">
							<div class="product-type-selection">
								<input type="radio" id="edit_unit_type_2" name="edit_unit_type" value="2">
								<label for="edit_unit_type_2"><?= $t['multi_pack'] ?></label>
							</div>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<label for="edit_units"><?= $t['units'] ?>:</label>
							<input class="form-small-input-style" type="number" name="edit_units" id="edit_units" placeholder="1 <?= $t['units'] ?>" title="<?= $t['units'] ?>" disabled/>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_weight_unit"><?= $t['weight_unit'] ?>:</label>
							<input class="form-small-input-style" type="text" name="edit_weight_unit" id="edit_weight_unit" placeholder="<?= $t['weight_unit'] ?>" title="<?= $t['weight_unit'] ?>"/>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_total_weight"><?= $t['total_weight'] ?>:</label>
							<input class="form-small-input-style" type="text" name="edit_total_weight" id="edit_total_weight" placeholder="<?= $t['total_weight'] ?>" title="<?= $t['total_weight'] ?>" disabled/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<label for="edit_product_name"><?= $t['product_name'] ?>:</label>
							<input class="form-medium-input-style" type="text" name="edit_product_name" id="edit_product_name" placeholder="<?= $t['product_name'] ?>" title="<?= $t['product_name'] ?>"/>
						</td>
						<td colspan="3" align="center" valign="middle">
							<label for="edit_hs_code"><?= $t['hs_code'] ?>:</label>
							<input class="form-medium-input-style" type="text" name="edit_hs_code" id="edit_hs_code" placeholder="<?= $t['hs_code'] ?>" title="<?= $t['hs_code'] ?>"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<label for="edit_product_type"><?= $t['type'] ?>:</label>
							<select class="form-input-style" name="edit_product_type" id="edit_product_type"></select>
						</td>
						<td colspan="3" align="center" valign="middle">
							<label for="edit_product_year"><?= $t['year'] ?>:</label>
							<input class="form-medium-input-style input-year-only" type="number" name="edit_product_year" id="edit_product_year" placeholder="<?= $t['year'] ?>" title="<?= $t['year'] ?>"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<label for="edit_product_mark"><?= $t['mark_category'] ?>:</label>
							<select class="form-input-style" name="edit_product_mark" id="edit_product_mark"></select>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_product_model"><?= $t['model'] ?>:</label>
							<select class="form-input-style" name="edit_product_model" id="edit_product_model"></select>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_product_sub_model"><?= $t['sub_model'] ?>:</label>
							<select class="form-input-style" name="edit_product_sub_model" id="edit_product_sub_model"></select>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<label for="edit_product_purpose"><?= $t['purpose'] ?>:</label>
							<select class="form-medium-input-style" name="edit_product_purpose" id="edit_product_purpose" required></select>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_quantity"><?= $t['quantity'] ?>:</label>
							<input class="form-medium-input-style" type="number" name="edit_quantity" id="edit_quantity" placeholder="<?= $t['quantity'] ?>" title="<?= $t['quantity'] ?>"/>
						</td>
						<td colspan="2" align="center" valign="middle">
							<label for="edit_min_quantity"><?= $t['min_quantity'] ?>:</label>
							<input class="form-medium-input-style" type="number" name="edit_min_quantity" id="edit_min_quantity" placeholder="<?= $t['min_quantity'] ?>" title="<?= $t['min_quantity'] ?>"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<label for="edit_currency"><?= $t['currency'] ?>:</label>
							<select class="form-input-style" name="edit_currency" id="edit_currency" disabled></select>
						</td>
						<td colspan="3" align="center" valign="middle">
							<label for="edit_price"><?= $t['price'] ?>:</label>
							<input class="form-medium-input-style" type="number" name="edit_price" id="edit_price" placeholder="<?= $t['price'] ?>" title="<?= $t['price'] ?>"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<label for="edit_description"><?= $t['description'] ?>:</label>
							<textarea class="form-input-style" id="edit_description" name="edit_description" rows="2" cols="35">
							</textarea>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="3" align="center" valign="middle">
							<button type="button" class="neutral-btn"><?= $t['cancel'] ?></button>
						</td>
						<td colspan="3" align="center" valign="middle">
							<input type="submit" class="button-style-agree" value="<?= $t['update'] ?>" />
						</td>
					</tr>
				</table> 
			</form>
		</div>
		<div id="receive-as-initial" style="display: none;">
			<div class="back-to-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formRequestProduct" id="formRequestProduct">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Receive as an initial</h2>
						</td>      
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>