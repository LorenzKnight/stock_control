<div class="bg-popup" id="add-shipping-form">
	<div class="formular-frame">
		<form method="post" name="formAddShipping" id="formAddShipping" enctype="multipart/form-data">
			<table width="90%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Add Shipping</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_1" name="shipping_method" value="1" checked>
							<label for="shipping-method-1">Ground Shipping</label>
						</div>
					</td>
					<td colspan="3" align="center" valign="middle">
						<div class="product-type-selection">
							<input type="radio" id="unit_type_2" name="shipping_method" value="2">
							<label for="shipping-method-2">Air Shipping</label>
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="destination">Destination:</label>
						<input class="form-input-style" type="text" name="destination" id="destination" placeholder="Shipping Destination..." title="Shipping Destination"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="delivery_date">Estimate Arrival Date:</label>
						<input class="form-input-style" type="date" name="delivery_date" id="delivery_date" placeholder="Enter a Estimate date..."/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="description">Description:</label>
						<textarea class="form-input-style" id="description" name="description" rows="5" cols="35">
						</textarea>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
						<span style="display: block;">Status</span>
					</td>
					<td colspan="3" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
						<label class="switch">
							<input type="checkbox" name="status" id="status" value="1" checked>
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
						<input type="submit" class="button-style-agree" value="Create" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>