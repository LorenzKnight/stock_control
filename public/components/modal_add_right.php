<div class="bg-popup" id="add-rights-form">
	<div class="formular-frame">
		<form method="post" name="formAddRights" id="formAddRights">
			<input type="hidden" name="user_id" id="right_user_id" value="">
			
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Create a Service</h2>
					</td>      
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<select class="form-input-style" name="service_name" id="service_name"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
						<span style="display: block;">Status</span>
					</td>
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
						<label class="switch">
							<input type="checkbox" name="can_access" id="can_access" value="1" checked>
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" style="padding: 15px 0" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td width="50%" style="padding: 15px 0" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Add Right" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>