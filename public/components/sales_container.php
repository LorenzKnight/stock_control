<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<div class="data-container wide-search-bar-height">
		<div class="centralize" style="width: 100%;">
			<table width="100%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td width="15%" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="add-sale-btn"><?= $t['create_sale'] ?></button>
					</td>
					<td width="85%" align="center" valign="middle">
						<input type="text" name="salesSearchField" id="salesSearchField" class="big-search-field" placeholder="<?= $t['search_sale'] ?>..." title="<?= $t['search_sale'] ?>">
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="data-container sales-container-height">
		<h2 style="margin-left: 10px;"><?= $t['sales_list'] ?></h2>
		<div class="centralize" style="width: 100%;">
			<div class="sales-list" id="sales-list"></div>
		</div>
	</div>
</div>