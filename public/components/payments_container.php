<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<div class="data-container wide-search-bar-height">
		<div class="centralize" style="width: 100%;">
			<table width="100%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td width="15%" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="add-payments-btn"><?= tr('make_payment') ?></button>
					</td>
					<td width="85%" align="center" valign="middle">
						<input type="text" name="paymentsSearchField" id="paymentsSearchField" class="big-search-field" placeholder="<?= tr('search_payment') ?>" title="<?= tr('search_payment') ?>">
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="data-container payments-container-height">
		<h2 style="margin-left: 10px;"><?= tr('payments_list') ?></h2>
		<div class="centralize" style="width: 100%;">
			<div class="payments-list" id="payments-list"></div>
		</div>
	</div>
</div>