<div class="bg-popup" id="add-slot-form">
	<div class="formular-frame">
		<form method="post" name="formAddSlot" id="formAddSlot" enctype="multipart/form-data">
			<table width="90%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Add Slot</h2>
					</td>      
				</tr>
				<!-- <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="company_id">Company:</label>
						<select class="form-input-style" name="storage_company_id" id="storage_company_id"></select>
					</td>
				</tr> -->
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="slot_name">Slot Name:</label>
						<input class="form-input-style" type="text" name="slot_name" id="slot_name" placeholder="Slot Name..." title="Slot Name"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="current_capacity">Current Capacity:</label>
						<input class="form-input-style" type="number" name="current_capacity" id="current_capacity" placeholder="Current Capacity..." title="Current Capacity"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="max_capacity">Max Capacity:</label>
						<input class="form-input-style" type="number" name="max_capacity" id="max_capacity" placeholder="Max Capacity..." title="Max Capacity"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="slot_description">Description:</label>
						<textarea class="form-input-style" id="slot_description" name="slot_description" rows="5" cols="35">
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