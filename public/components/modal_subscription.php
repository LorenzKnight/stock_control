<div class="bg-popup" id="subsc-form">
	<div class="formular-medium-frame">
		<form action="stock.php" method="post" name="formSubscription" id="formSubscription">
			<table width="95%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="4" align="center" valign="middle">
						<h2><?= tr('upgrade_your_plan'); ?></h2>
						<p><?= tr('select_a_subscription_pack') ?></p>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="4" align="center" valign="middle">
						<div class="pack-container" id="packs"></div> 
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="4" align="center" valign="middle">
						<select class="form-input-style" name="extra_pack" id="extra_pack"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="4" align="center" valign="middle">
						<div class="estimated" id="estimated"><?= tr('estimated_cost'); ?>: <strong>$ 0</strong></div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="25%" align="center" valign="middle">
					</td>
					<td width="25%" align="center" valign="middle">
						<button type="button" class="cancel-btn logout-button hidden" id="subs-logout-btn"><?= tr('header_logout'); ?></button>
						<button type="button" class="neutral-btn" id="subs-cancel-btn"><?= tr('cancel'); ?></button>
					</td>
					<td width="25%" align="center" valign="middle">
						<input type="submit" class="button-style-agree disabled" id="packUpgradeBtn" value="<?= tr('upgrade'); ?>"/>
					</td>
					<td width="25%" align="center" valign="middle">
					</td>
				</tr>
			</table>
			<input type="hidden" name="estimated_cost" id="estimated_cost">
		</form>
	</div>
</div>