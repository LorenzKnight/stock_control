<div class="bg-popup" id="add-product-form">
	<div class="formular-frame">
		<form method="post" name="formAddProduct" id="formAddProduct" enctype="multipart/form-data">
			<table width="90%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2><?= $t['add_product_title'] ?></h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<div class="drop-area" id="drop-product-area">
							<img class="image-preview" id="product-image-preview" src="" alt="Product Image Preview">
							<p><?= $t['form_drop_image'] ?></p>
							<input type="file" name="product_image" id="product_image" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_1" name="unit_type" value="1" checked>
							<label for="unit_type_1"><?= $t['single_unit'] ?></label>
						</div>
					</td>
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_2" name="unit_type" value="2">
							<label for="unit_type_2"><?= $t['multi_pack'] ?></label>
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="units"><?= $t['units'] ?>:</label>
						<input class="form-small-input-style" type="number" name="units" id="units" placeholder="1 units" title="units" disabled/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="weight_unit"><?= $t['weight_unit'] ?>:</label>
						 <input class="form-small-input-style" type="text" name="weight_unit" id="weight_unit" placeholder="<?= $t['weight_unit'] ?>" title="<?= $t['weight_unit'] ?>"/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="total_weight"><?= $t['total_weight'] ?>:</label>
						 <input class="form-small-input-style" type="text" name="total_weight" id="total_weight" placeholder="<?= $t['total_weight'] ?>" title="<?= $t['total_weight'] ?>" disabled/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="product_name"><?= $t['product_name'] ?>:</label>
						<input class="form-medium-input-style" type="text" name="product_name" id="product_name" placeholder="<?= $t['product_name'] ?>..." title="<?= $t['product_name'] ?>"/>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="hs_code"><?= $t['hs_code'] ?>:</label>
						<input class="form-medium-input-style" type="text" name="hs_code" id="hs_code" placeholder="<?= $t['hs_code'] ?>..." title="<?= $t['hs_code'] ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="product_type"><?= $t['type'] ?>:</label>
						<select class="form-input-style" name="product_type" id="product_type"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="product_year"><?= $t['year'] ?>:</label>
						<input class="form-medium-input-style input-year-only" type="number" name="product_year" id="product_year" placeholder="<?= $t['year'] ?>" title="<?= $t['year'] ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="product_mark"><?= $t['mark_category'] ?>:</label>
						<select class="form-medium-input-style" name="product_mark" id="product_mark"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="product_model"><?= $t['model'] ?>:</label>
						<select class="form-medium-input-style" name="product_model" id="product_model"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="product_sub_model"><?= $t['sub_model'] ?>:</label>
						<select class="form-medium-input-style" name="product_sub_model" id="product_sub_model"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="product_purpose"><?= $t['purpose'] ?>:</label>
						<select class="form-medium-input-style" name="product_purpose" id="product_purpose" required></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="quantity"><?= $t['quantity'] ?>:</label>
						<input class="form-medium-input-style" type="number" name="quantity" id="quantity" placeholder="<?= $t['quantity'] ?>" title="<?= $t['quantity'] ?>"/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="min_quantity"><?= $t['min_quantity'] ?>:</label>
						<input class="form-small-input-style" type="number" name="min_quantity" id="min_quantity" placeholder="<?= $t['min_quantity'] ?> = 10" title="<?= $t['min_quantity'] ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="currency"><?= $t['currency'] ?>:</label>
						<select class="form-input-style" name="currency" id="currency"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="price"><?= $t['price'] ?>:</label>
						<input class="form-medium-input-style" type="number" name="price" id="price" placeholder="<?= $t['price'] ?>" title="<?= $t['price'] ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="description"><?= $t['description'] ?>:</label>
						<textarea class="form-input-style" id="description" name="description" rows="2" cols="35">
						</textarea>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= $t['cancel'] ?></button>
					</td>
					<td colspan="3" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= $t['create'] ?>" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>