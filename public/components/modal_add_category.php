<div class="bg-popup" id="add-category-form">
	<div class="formular-big-frame">
		<form method="post" name="formAddCategory" id="formAddCategory">
			<table width="95%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2><?= tr('add_category_or_subcategory') ?></h2>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<div class="lists-container">
							<div class="formular-list">
								<div class="create-list-holder" id="clic-create-mark">
									<button type="button" class="button-style-agree" id="add-mark-btn"><?= tr('new_mark') ?></button>
								</div>
								<div class="create-list-holder flex" id="input-mark" style="display: none;">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td width="70%" align="center" valign="middle">
												<input type="text" class="form-medium-input-style" name="input-product-mark" id="input-product-mark" />
											</td>
											<td width="30%" align="center" valign="middle">
												<button type="button" class="button-style-agree" id="btn-create-mark"><?= tr('create') ?></button>
											</td>
										</tr>
									</table>
								</div>
								<div class="cat-all-list">
									<table class="all-mark-list" id="mark-list" cellspacing="0"></table>
								</div>
							</div>
						
							<div class="formular-list">
								<div class="create-list-holder" id="clic-create-model">
									<button type="button" class="button-style-agree disabled" id="add-model-btn" disabled><?= tr('new_model') ?></button>
								</div>
								<div class="create-list-holder" id="input-model" style="display: none;">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td width="70%" align="center" valign="middle">
												<input type="text" class="form-medium-input-style" name="input-product-model" id="input-product-model" />
											</td>
											<td width="30%" align="center" valign="middle">
												<button type="button" class="button-style-agree" id="btn-create-model"><?= tr('create') ?></button>
											</td>
										</tr>
									</table>
								</div>
								<div class="cat-all-list">
									<table class="all-model-list" id="model-list" cellspacing="0"></table>
								</div>
							</div>
						
							<div class="formular-list">
								<div class="create-list-holder" id="clic-create-submodel">
									<button type="button" class="button-style-agree disabled" id="add-submodel-btn" disabled><?= tr('new_submodel') ?></button>
								</div>
								<div class="create-list-holder" id="input-submodel" style="display: none;">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td width="70%" align="center" valign="middle">
												<input type="text" class="form-medium-input-style" name="input-product-submodel" id="input-product-submodel" />
											</td>
											<td width="30%" align="center" valign="middle">
												<button type="button" class="button-style-agree" id="btn-create-submodel"><?= tr('create') ?></button>
											</td>
										</tr>
									</table>
								</div>
								<div class="cat-all-list">
									<table class="all-submodel-list" id="submodel-list" cellspacing="0"></table>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr valign="baseline" >
					<td colspan="6" align="center" valign="middle">
						<div class="footer-buttons">
							<button type="button" class="neutral-btn"><?= tr('cancel') ?></button>
					
							<button 
								type="submit"
								class="button-style-agree"
							>
								<?= tr('create') ?>
							</button>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>