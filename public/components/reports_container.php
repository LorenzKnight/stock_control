<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<div class="data-container wide-search-bar-height">
		<div class="centralize auxlabel" style="width: 100%;">
			<table width="100%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td width="10%" align="center" valign="middle" style="border-right: 1px solid var(--gray-300);">
						<select class="form-input-style" name="reports_select_company" id="reports_select_company"></select>
					</td>
					<td width="24%" align="center" valign="middle" style="border-right: 1px solid var(--gray-300);">
						<input type="text" name="reportsSearchField" id="reportsSearchField" class="big-search-field" placeholder="Search Report..." title="Search Report">
					</td>
					<td width="10%" align="center" valign="middle">
						<select class="form-input-style" name="reports_product_mark" id="reports_product_mark"></select>
					</td>
					<td width="10%" align="center" valign="middle">
						<select class="form-input-style" name="reports_product_model" id="reports_product_model"></select>
					</td>
					<td width="10%" align="center" valign="middle">
						<select class="form-input-style" name="reports_product_sub_model" id="reports_product_sub_model"></select>
					</td>
					<td width="18%" align="center" valign="middle" style="padding-left: 5px; border-left: 1px solid var(--gray-300);">
						<label for="reports_from_date">From Date:</label>
						<input class="form-input-style" type="date" name="reports_from_date" id="reports_from_date" placeholder="Enter a Estimate date..."/>
					</td>
					<td width="18%" align="center" valign="middle" style="padding-left: 10px;">
						<label for="reports_to_date">To Date:</label>
						<input class="form-input-style" type="date" name="reports_to_date" id="reports_to_date" placeholder="Enter a Estimate date..."/>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="data-container report-container-height">
		<h2 style="margin-left: 10px;">Reports</h2>
		<div class="centralize" style="width: 100%;">
			<div class="report-list" id="reports-list"></div>
			<div class="report-sidebar" id="report-sidebar"></div>
		</div>
	</div>
</div>