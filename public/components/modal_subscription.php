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
					<td colspan="6" align="center" valign="middle">
						<label for="packs">Member Package:</label>
						<select class="form-input-style" name="packs" id="packs">
							<option value="">Select a pack</option>
							<option value="5">Mini Pack (5 members)</option>
							<option value="10">Smart Pack (10 members)</option>
							<option value="15">Plus Pack (15 members)</option>
							<option value="20">Growth Pack (20 members)</option>
							<option value="25">Boost Pack (25 members)</option>
							<option value="30">Power Pack (30 members)</option>
							<option value="35">Max Pack (35 members)</option>
							<option value="40">Super Pack (40 members)</option>
							<option value="45">Mega Pack (45 members)</option>
							<option value="50">Ultra Pack (50 members)</option>
						</select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<div id="estimated"></div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Upgrade" />
					</td>
					<td width="50%" align="center" valign="middle">
						<button class="cancel-btn" id="cancel-subsc-btn">Cancel</button>
					</td>
				</tr>
			</table>
			<input type="hidden" name="estimated_cost" id="estimated_cost">
		</form>
	</div>
</div>