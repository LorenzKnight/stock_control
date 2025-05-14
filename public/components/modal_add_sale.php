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
							<div class="create-list-holder">
								<button type="button" class="button-style-agree disabled" id="" disabled>Method of Payment</button>
							</div>
							<div class="cat-all-list">
								<table width="100%" align="center" cellspacing="0">
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<input class="form-input-style" type="text" name="price_sum" id="price_sum" placeholder="Price sum..." disabled/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<input class="form-input-style" type="text" name="initial" id="initial" placeholder="Initial..."/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<input class="form-input-style" type="date" name="delivery_date" id="delivery_date" placeholder="Enter a delivery date..."/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<input class="form-input-style" type="text" name="remaining" id="remaining" placeholder="Remaining..." disabled/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td width="50%" align="center" valign="middle">
											<input class="form-medium-input-style" type="text" name="interest" id="interest" value="10" placeholder="Enter a percent..."/>
										</td>
										<td width="50%" align="center" valign="middle">
											<input class="form-medium-input-style" type="text" name="total_interest" id="total_interest" placeholder="Total Interest..." disabled/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td width="50%" align="center" valign="middle">
											<select class="form-input-style" name="installments_month" id="installments_month"></select>
										</td>
										<td width="50%" align="center" valign="middle">
											<input class="form-medium-input-style" type="date" name="payment_date" id="payment_date" placeholder="Enter a Payment date..."/>
										</td>
									</tr>
									<tr valign="baseline" class="form_height">
										<td colspan="2" align="center" valign="middle">
											<input class="form-input-style" type="text" name="due" id="due" placeholder="Due..." disabled/>
										</td>
									</tr>
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