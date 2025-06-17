<div class="bg-popup" id="subsc-form">
	<div class="formular-frame">
		<form action="stock.php" method="post" name="formSubscription" id="formSubscription">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Upgrade your Subscription</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="packs">Member Package:</label>
						<select class="form-input-style" name="packs" id="packs"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<div id="estimated"></div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Upgrade" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="estimated_cost" id="estimated_cost">
		</form>
	</div>
</div>