<div class="bg-popup" id="edit-slot-form">
	<div class="formular-medium-frame">
		<form method="post" name="formAddSlot" id="formAddSlot" enctype="multipart/form-data">
			<table width="90%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Add or Edit Slot</h2>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="3" align="center" valign="middle">
						<div class="formular-category-list">
							<div class="create-list-holder" id="clic-create-mark">
								<button type="button" class="button-style-agree" id="add-slot-btn">Create slot</button>
							</div>
							<div class="cat-all-list">
								<table class="all-mark-list" id="slot-list" cellspacing="0">
								
								</table>
							</div>
						</div>
					</td>
					<td colspan="3" align="center" valign="middle">
						<div class="formular-category-list">
							<table class="hidden" id="slot-form" width="100%" align="center" cellspacing="0">
								<!-- <tr valign="baseline">
									<td colspan="6" align="center" valign="middle">
										<label for="company_id">Company:</label>
										<select class="form-input-style" name="storage_company_id" id="storage_company_id"></select>
									</td>
								</tr> -->
								<tr valign="baseline">
									<td colspan="6" align="center" valign="middle">
										<label for="slot_name">Slot Name:</label>
										<input class="form-input-style" type="text" name="slot_name" id="slot_name" placeholder="Slot Name..." title="Slot Name"/>
									</td>
								</tr>
								<tr valign="baseline">
									<td colspan="6" align="center" valign="middle">
										<label for="current_capacity">Current Capacity:</label>
										<input class="form-input-style" type="number" name="current_capacity" id="current_capacity" placeholder="Current Capacity..." title="Current Capacity"/>
									</td>
								</tr>
								<tr valign="baseline">
									<td colspan="6" align="center" valign="middle">
										<label for="max_capacity">Max Capacity:</label>
										<input class="form-input-style" type="number" name="max_capacity" id="max_capacity" placeholder="Max Capacity..." title="Max Capacity"/>
									</td>
								</tr>
								<tr valign="baseline">
									<td colspan="6" align="center" valign="middle">
										<label for="slot_description">Description:</label>
										<textarea class="form-input-style" id="slot_description" name="slot_description" rows="5" cols="35">
										</textarea>
									</td>
								</tr>
								<tr valign="baseline">
									<td colspan="3" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
										<span style="display: block;">Status</span>
									</td>
									<td colspan="3" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
										<label class="switch">
											<input type="checkbox" name="slot_status" id="slot_status" value="1" checked>
											<span class="slider round"></span>
										</label>
									</td>
								</tr>

								<input type="hidden" name="slot_id" id="slot_id" value="">
							</table>
							<table id="not-slot-form" width="100%" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td colspan="2" align="center" valign="middle">
										<p>Slot Info</p>
									</td>
								</tr>
							</table>
						</div>
					</td>
				<tr valign="baseline">
					<td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
						<input type="submit" class="button-style-agree" id="slot-action-btn" value="Ok" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>