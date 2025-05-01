<div class="bg-popup" id="add-sale-form">
	<div class="formular-big-frame">
        <form method="post" name="formAddSale" id="formAddSale">
            <table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Create a Sale</h2>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="2" align="center" valign="middle">
						<div class="formular-customers-list">
							<div class="create-list-holder">
                                <input class="form-input-style" type="text" name="search-customer" id="search-customer" placeholder="Enter a name or Doc No..." title="Enter a valid name"/>
							</div>
							<div class="cat-all-list">
								<table id="select-customers-list" cellspacing="0"></table>
							</div>
						</div>
					</td>
					<td colspan="2" align="center" valign="middle">
						<div class="formular-customers-list">
							<div class="create-list-holder">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline">
										<td width="60%" align="center" valign="middle">
											<input type="text" class="form-medium-input-style" name="search-product-purchase" id="search-product-purchase" placeholder="Enter Product No..." title="Enter a valid Product No."/>
										</td>
										<td width="40%" align="center" valign="middle">
											<select class="form-input-style" name="search-product-mark" id="search-product-mark"></select>
										</td>
									</tr>
								</table>
							</div>
							<div class="cat-all-list">
								<table id="select-product-list" cellspacing="0"></table>
							</div>
						</div>
					</td>
					<td colspan="2" align="center" valign="middle">
						<div class="formular-customers-list">
							<div class="create-list-holder" id="clic-create-submodel">
								<button type="button" class="button-style-agree disabled" id="add-submodel-btn" disabled>New Submodel</button>
							</div>
							<div class="create-list-holder" id="input-submodel" style="display: none;">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline">
										<td width="70%" align="center" valign="middle">
											<input type="text" class="form-medium-input-style" name="input-product-submodel" id="input-product-submodel" />
										</td>
										<td width="30%" align="center" valign="middle">
											<button type="button" class="button-style-agree" id="btn-create-submodel">Create</button>
										</td>
									</tr>
								</table>
							</div>
							<div class="cat-all-list">
								<table class="all-submodel-list" id="submodel-list" cellspacing="0">
								
								</table>
							</div>
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
					</td>
					<td colspan="1" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td colspan="1" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Create" />
					</td>
					<td colspan="2" align="center" valign="middle">
					</td>
				</tr>
			</table>
        </form>
    </div>
</div>