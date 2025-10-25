<div class="bg-popup" id="load-options">
	<div id="formular-frame-2" class="formular-frame">
		<div id="load-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Load Options</h2>
						<p id="load-no"></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editLoadBtn">Edit Load</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="">Print Products Labels</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteLoadBtn">Delete Load</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
					</td>
				</tr>
			</table>
		</div>
		<div id="edit-load-modal" style="display: none;">
			<div class="back-to-load-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditLoad" id="formEditLoad">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
							<h2>Edit Load</h2>
						</td>      
					</tr>
					<tr valign="baseline">
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<input class="form-input-style" type="text" name="search-shipping-customer" id="search-shipping-customer" placeholder="Enter a name or Doc No..." title="Enter a valid name"/>
								</div>
								<div class="cat-all-list">
									<table id="select-shipping-customers-list" cellspacing="0"></table>
								</div>
							</div>
						</td>
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline">
											<td width="60%" align="center" valign="middle">
												<input type="text" class="form-medium-input-style" name="search-product-for-shipping" id="search-product-for-shipping" placeholder="Enter Product No..." title="Enter a valid Product No."/>
											</td>
											<td width="40%" align="center" valign="middle">
												<select class="form-input-style" name="search-product-mark-for-shipping" id="search-product-mark-for-shipping"></select>
											</td>
										</tr>
									</table>
								</div>
								<div class="cat-all-list">
									<table id="select-product-list-for-shipping" cellspacing="0"></table>
								</div>
							</div>
						</td>
						<td colspan="2" align="center" valign="middle">
							<div class="formular-customers-list">
								<div class="create-list-holder" style="padding: 5px 0 15px;">
									<button type="button" class="button-style-agree disabled" style="pointer-events: none" disabled>Load Details</button>
								</div>
								<div class="cat-all-list">
									<table width="100%" align="center" cellspacing="0">
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="shipping_from_currency">From currency:</label>
												<select class="form-input-style" name="shipping_from_currency" id="shipping_from_currency" required></select>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="shipping_price">Price/kg:</label>
												<input class="form-medium-input-style" type="text" name="shipping_price" id="shipping_price" placeholder="Price/kg..."/>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="total_kg">Total kg:</label>
												<input class="form-medium-input-style" type="text" name="total_kg" id="total_kg" placeholder="Total kg..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="price_sum">Price Sum:</label>
												<input class="form-medium-input-style" type="text" name="price_sum" id="price_sum" placeholder="Price sum..." disabled/>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="discount">Discount:</label>
												<input class="form-medium-input-style" type="text" name="discount" id="discount" placeholder="Discount..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="taxes">Taxes %:</label>
												<input class="form-medium-input-style" type="text" name="taxes" id="taxes" placeholder="Taxes %..."/>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="total">Price Total:</label>
												<input class="form-medium-input-style" type="text" name="total" id="total" placeholder="Total..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td width="50%" align="center" valign="middle">
												<label for="shipping_to_currency">To Currency:</label>
												<select class="form-medium-input-style" name="shipping_to_currency" id="shipping_to_currency" required></select>
											</td>
											<td width="50%" align="center" valign="middle">
												<label for="total_exchanged">Total Exchanged:</label>
												<input class="form-medium-input-style" type="text" name="total_exchanged" id="total_exchanged" placeholder="Total exchanged..." disabled/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="destination">Customer destination:</label>
												<input class="form-input-style" type="text" name="destination" id="destination" placeholder="Customer destination..."/>
											</td>
										</tr>
										<tr valign="baseline" class="form_height">
											<td colspan="2" align="center" valign="middle">
												<label for="comment">Comment:</label>
												<textarea class="form-input-style" id="comment" name="comment" rows="5" cols="35">
												</textarea>
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
</div>