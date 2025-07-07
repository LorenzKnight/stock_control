<div class="bg-popup" id="subsc-form">
	<div class="formular-medium-frame">
		<form action="stock.php" method="post" name="formSubscription" id="formSubscription">
			<table width="95%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="4" align="center" valign="middle">
						<h2>Upgrade your Subscription</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="4" align="center" valign="middle">
						<label for="packs">Member Package:</label>
						<!-- <select class="form-input-style" name="packs" id="packs"></select> -->
						<div class="pack-container" id="packs"></div> 
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="4" align="center" valign="middle">
						<div id="estimated">Estimated cost: <strong>$ 0</strong></div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="25%" align="center" valign="middle">
					</td>
					<td width="25%" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td width="25%" align="center" valign="middle">
						<input type="submit" class="button-style-agree disabled" id="packUpgradeBtn" value="Upgrade"/>
					</td>
					<td width="25%" align="center" valign="middle">
					</td>
				</tr>
			</table>
			<input type="hidden" name="estimated_cost" id="estimated_cost">
		</form>
	</div>
</div>