<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<!-- <div class="data-container" style="height: 50px;">
		<div class="centralize" style="width: 100%;">
			<table width="100%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td width="15%" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="add-shipping-btn">Create Shipping</button>
					</td>
					<td width="85%" align="center" valign="middle">
						<input type="text" name="searchShippingField" id="searchShippingField" class="big-search-field" placeholder="Search Shipping..." title="Search Shipping">
					</td>
				</tr>
			</table>
		</div>
	</div> -->
	<div class="data-container shipping-container-height">
		<h2 style="margin-left: 10px;">Shippings List</h2>
		<div class="product-table flex">
			<div class="shipping-sidebar">
				<table width="90%" align="center" cellspacing="0" style="margin-top: 15px;">
					<tr valign="baseline" class="form_height">
						<td colspan="4" style="border-bottom: 1px solid var(--clr-border); padding-bottom: 10px;" align="center" valign="middle">
							<input type="text" name="searchShippingField" id="searchShippingField" class="big-search-field" placeholder="Search Shipping..." title="Search Shipping">
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="4" style="border-bottom: 1px solid var(--clr-border); padding: 10px 0;" align="center" valign="middle">
							<button type="button" class="button-style-agree" id="add-shipping-btn">Create Shipping</button>
						</td>
					</tr>
				</table>
				<div class="shipping-list">
					<table class="shipping-table" id="shippingTable" width="96%" align="right" cellspacing="0" cellpadding="0"></table>
				</div>
			</div>
			<div class="shipping-details" id="shippingDetails"></div>
			<div class="shipping-sidebar" id="shippingSummary"></div>
		</div>
	</div>
</div>