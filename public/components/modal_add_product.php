<!-- <div class="bg-popup" id="add-product-form">
	<div class="formular-frame">
		<form method="post" name="formAddProduct" id="formAddProduct" enctype="multipart/form-data">
			<table width="90%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2><?= tr('add_product_title') ?></h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<div class="drop-area" id="drop-product-area">
							<img class="image-preview" id="product-image-preview" src="" alt="Product Image Preview">
							<p><?= tr('form_drop_image') ?></p>
							<input type="file" name="product_image" id="product_image" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_1" name="unit_type" value="1" checked>
							<label for="unit_type_1"><?= tr('single_unit') ?></label>
						</div>
					</td>
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_2" name="unit_type" value="2">
							<label for="unit_type_2"><?= tr('multi_pack') ?></label>
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="units"><?= tr('units') ?>:</label>
						<input class="form-small-input-style" type="number" name="units" id="units" placeholder="1 units" title="units" disabled/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="weight_unit"><?= tr('weight_unit') ?>:</label>
						 <input class="form-small-input-style" type="text" name="weight_unit" id="weight_unit" placeholder="<?= tr('weight_unit') ?>" title="<?= tr('weight_unit') ?>"/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="total_weight"><?= tr('total_weight') ?>:</label>
						 <input class="form-small-input-style" type="text" name="total_weight" id="total_weight" placeholder="<?= tr('total_weight') ?>" title="<?= tr('total_weight') ?>" disabled/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="product_name"><?= tr('product_name') ?>:</label>
						<input class="form-medium-input-style" type="text" name="product_name" id="product_name" placeholder="<?= tr('product_name') ?>..." title="<?= tr('product_name') ?>"/>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="hs_code"><?= tr('hs_code') ?>:</label>
						<input class="form-medium-input-style" type="text" name="hs_code" id="hs_code" placeholder="<?= tr('hs_code') ?>..." title="<?= tr('hs_code') ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="product_type"><?= tr('type') ?>:</label>
						<select class="form-input-style" name="product_type" id="product_type"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="product_year"><?= tr('year') ?>:</label>
						<input class="form-medium-input-style input-year-only" type="number" name="product_year" id="product_year" placeholder="<?= tr('year') ?>" title="<?= tr('year') ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="product_mark"><?= tr('mark_category') ?>:</label>
						<select class="form-medium-input-style" name="product_mark" id="product_mark"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="product_model"><?= tr('model') ?>:</label>
						<select class="form-medium-input-style" name="product_model" id="product_model"></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="product_sub_model"><?= tr('sub_model') ?>:</label>
						<select class="form-medium-input-style" name="product_sub_model" id="product_sub_model"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="product_purpose"><?= tr('purpose') ?>:</label>
						<select class="form-medium-input-style" name="product_purpose" id="product_purpose" required></select>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="quantity"><?= tr('quantity') ?>:</label>
						<input class="form-medium-input-style" type="number" name="quantity" id="quantity" placeholder="<?= tr('quantity') ?>" title="<?= tr('quantity') ?>"/>
					</td>
					<td colspan="2" align="center" valign="middle">
						<label for="min_quantity"><?= tr('min_quantity') ?>:</label>
						<input class="form-small-input-style" type="number" name="min_quantity" id="min_quantity" placeholder="<?= tr('min_quantity') ?> = 10" title="<?= tr('min_quantity') ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<label for="currency"><?= tr('currency') ?>:</label>
						<select class="form-input-style" name="currency" id="currency"></select>
					</td>
					<td colspan="3" align="center" valign="middle">
						<label for="price"><?= tr('price') ?>:</label>
						<input class="form-medium-input-style" type="number" name="price" id="price" placeholder="<?= tr('price') ?>" title="<?= tr('price') ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="description"><?= tr('description') ?>:</label>
						<textarea class="form-input-style" id="description" name="description" rows="2" cols="35">
						</textarea>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= tr('cancel') ?></button>
					</td>
					<td colspan="3" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= tr('create') ?>" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div> -->



<div class="bg-popup" id="add-product-form">
	<div class="formular-frame">
		<table width="90%" align="center" cellspacing="0">
			<tr valign="baseline">
				<td colspan="6" align="center" valign="middle">
					<h2><?= tr('add_product_title') ?></h2>

					<p class="product-form-intro">
						<?= tr('product_form_simple_intro') ?>
					</p>
				</td>
			</tr>
		</table>
		
		<form method="post" name="formAddProduct" id="formAddProduct" enctype="multipart/form-data">
			<div class="form-scrollable">
				<table width="95%" align="center" cellspacing="0">
					<!-- CAMPO BÁSICO: NOMBRE -->
					<tr valign="baseline" class="form_height product-basic-row">
						<td colspan="6" align="center" valign="middle">
							<label for="product_name">
								<?= tr('product_name') ?>:
							</label>

							<input
								class="form-input-style"
								type="text"
								name="product_name"
								id="product_name"
								placeholder="<?= tr('product_name') ?>..."
								title="<?= tr('product_name') ?>"
								required
							/>
						</td>
					</tr>

					<!-- CAMPOS BÁSICOS: CANTIDAD Y PRECIO -->
					<tr valign="baseline" class="form_height product-basic-row">
						<td colspan="2" align="center" valign="middle">
							<label for="quantity">
								<?= tr('quantity') ?>:
							</label>

							<input
								class="form-medium-input-style"
								type="number"
								name="quantity"
								id="quantity"
								min="0"
								value="0"
								placeholder="<?= tr('quantity') ?>"
								title="<?= tr('quantity') ?>"
							/>
						</td>

						<td colspan="2" align="center" valign="middle">
							<label for="currency">
								<?= tr('currency') ?>:
							</label>

							<select
								class="form-medium-input-style"
								name="currency"
								id="currency"
							></select>
						</td>

						<td colspan="2" align="center" valign="middle">
							<label for="price">
								<?= tr('price') ?>:
							</label>

							<input
								class="form-medium-input-style"
								type="number"
								name="price"
								id="price"
								min="0"
								step="0.01"
								placeholder="<?= tr('price') ?>"
								title="<?= tr('price') ?>"
							/>
						</td>
					</tr>

					<!-- AVANZADO: IMAGEN -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row"
					>
						<td colspan="6" align="center" valign="middle">
							<div class="drop-area" id="drop-product-area">
								<img
									class="image-preview"
									id="product-image-preview"
									src=""
									alt="Product Image Preview"
								>

								<p><?= tr('form_drop_image') ?></p>

								<input
									type="file"
									name="product_image"
									id="product_image"
									accept="image/*"
									style="display:none;"
								>
							</div>
						</td>
					</tr>

					<!-- AVANZADO: UNIDAD SIMPLE O MULTIPACK -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row"
					>
						<td colspan="3" align="center" valign="middle">
							<div class="product-type-selection">
								<input
									type="radio"
									id="unit_type_1"
									name="unit_type"
									value="1"
									checked
								>

								<label for="unit_type_1">
									<?= tr('single_unit') ?>
								</label>
							</div>
						</td>

						<td colspan="3" align="center" valign="middle">
							<div class="product-type-selection">
								<input
									type="radio"
									id="unit_type_2"
									name="unit_type"
									value="2"
								>

								<label for="unit_type_2">
									<?= tr('multi_pack') ?>
								</label>
							</div>
						</td>
					</tr>

					<!-- SOLO MULTIPACK -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row product-pack-row"
					>
						<td colspan="2" align="center" valign="middle">
							<label for="units">
								<?= tr('units') ?>:
							</label>

							<input
								class="form-small-input-style"
								type="number"
								name="units"
								id="units"
								min="1"
								value="1"
								placeholder="1"
								title="<?= tr('units') ?>"
								disabled
							/>
						</td>

						<td colspan="2" align="center" valign="middle">
							<label for="weight_unit">
								<?= tr('weight_unit') ?>:
							</label>

							<input
								class="form-small-input-style"
								type="text"
								name="weight_unit"
								id="weight_unit"
								placeholder="<?= tr('weight_unit') ?>"
								title="<?= tr('weight_unit') ?>"
							/>
						</td>

						<td colspan="2" align="center" valign="middle">
							<label for="total_weight">
								<?= tr('total_weight') ?>:
							</label>

							<input
								class="form-small-input-style"
								type="text"
								name="total_weight"
								id="total_weight"
								placeholder="<?= tr('total_weight') ?>"
								title="<?= tr('total_weight') ?>"
								disabled
							/>
						</td>
					</tr>

					<!-- AVANZADO: HS CODE Y AÑO -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row"
					>
						<td colspan="3" align="center" valign="middle">
							<label for="hs_code">
								<?= tr('hs_code') ?>:
							</label>

							<input
								class="form-medium-input-style"
								type="text"
								name="hs_code"
								id="hs_code"
								placeholder="<?= tr('hs_code') ?>..."
								title="<?= tr('hs_code') ?>"
							/>
						</td>

						<td colspan="3" align="center" valign="middle">
							<label for="product_year">
								<?= tr('year') ?>:
							</label>

							<input
								class="form-medium-input-style input-year-only"
								type="number"
								name="product_year"
								id="product_year"
								placeholder="<?= tr('year') ?>"
								title="<?= tr('year') ?>"
							/>
						</td>
					</tr>

					<!-- AVANZADO: TIPO Y PROPÓSITO -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row"
					>
						<td colspan="3" align="center" valign="middle">
							<label for="product_type">
								<?= tr('type') ?>:
							</label>

							<select
								class="form-input-style"
								name="product_type"
								id="product_type"
							></select>
						</td>

						<td colspan="3" align="center" valign="middle">
							<label for="product_purpose">
								<?= tr('purpose') ?>:
							</label>

							<select
								class="form-input-style"
								name="product_purpose"
								id="product_purpose"
								required
							></select>
						</td>
					</tr>

					<!-- AVANZADO: CATEGORÍA -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row"
					>
						<td colspan="2" align="center" valign="middle">
							<label for="product_mark">
								<?= tr('mark_category') ?>:
							</label>

							<select
								class="form-medium-input-style"
								name="product_mark"
								id="product_mark"
							></select>
						</td>

						<td
							colspan="2"
							align="center"
							valign="middle"
							class="product-model-field"
						>
							<label for="product_model">
								<?= tr('model') ?>:
							</label>

							<select
								class="form-medium-input-style"
								name="product_model"
								id="product_model"
							></select>
						</td>

						<td
							colspan="2"
							align="center"
							valign="middle"
							class="product-submodel-field"
						>
							<label for="product_sub_model">
								<?= tr('sub_model') ?>:
							</label>

							<select
								class="form-medium-input-style"
								name="product_sub_model"
								id="product_sub_model"
							></select>
						</td>
					</tr>

					<!-- AVANZADO: CANTIDAD MÍNIMA -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row"
					>
						<td colspan="6" align="center" valign="middle">
							<label for="min_quantity">
								<?= tr('min_quantity') ?>:
							</label>

							<input
								class="form-input-style"
								type="number"
								name="min_quantity"
								id="min_quantity"
								min="0"
								placeholder="<?= tr('min_quantity') ?>"
								title="<?= tr('min_quantity') ?>"
							/>
						</td>
					</tr>

					<!-- AVANZADO: DESCRIPCIÓN -->
					<tr
						valign="baseline"
						class="form_height product-advanced-row"
					>
						<td colspan="6" align="center" valign="middle">
							<label for="description">
								<?= tr('description') ?>:
							</label>

							<textarea
								class="form-input-style"
								id="description"
								name="description"
								rows="3"
								cols="35"
							></textarea>
						</td>
					</tr>
				</table>
			</div>

			<table width="90%" align="center" cellspacing="0">
				<!-- BOTÓN PARA MOSTRAR CAMPOS AVANZADOS -->
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<button
							type="button"
							class="product-advanced-toggle"
							id="product-advanced-toggle"
							aria-expanded="false"
							data-show-label="<?= tr('show_advanced_options') ?>"
							data-hide-label="<?= tr('hide_advanced_options') ?>"
						>
							<span class="product-advanced-toggle-text">
								<?= tr('show_advanced_options') ?>
							</span>

							<span
								class="product-advanced-toggle-icon"
								aria-hidden="true"
							>
								⌄
							</span>
						</button>
					</td>
				</tr>

				<!-- ACCIONES -->
				<tr valign="baseline" class="form_height product-form-actions">
					<td colspan="3" align="center" valign="middle">
						<button type="button" class="neutral-btn">
							<?= tr('cancel') ?>
						</button>
					</td>

					<td colspan="3" align="center" valign="middle">
						<input
							type="submit"
							class="button-style-agree"
							value="<?= tr('create') ?>"
						/>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>