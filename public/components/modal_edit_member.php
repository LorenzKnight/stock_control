<div class="bg-popup" id="edit-members-form">
	<div class="formular-frame">
		<form action="stock.php" method="post" name="formEditMembers" id="formEditMembers" enctype="multipart/form-data">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Edit co-Workers</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="edit_name" id="edit_name" placeholder="Enter a name..." title="Enter a valid name" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="edit_surname" id="edit_surname" placeholder="Enter a surname..." title="Enter a valid surname" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="fecha">Birthdate:</label>
						<input class="form-input-style" type="date" name="edit_birthday" id="edit_birthday" placeholder="" title=""/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="number" name="edit_phone" id="edit_phone" placeholder="Enter a phone number..." title="Enter a valid phone number"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="rank">Company / Affiliate:</label>
						<select class="form-input-style" name="edit_company" id="edit_company" required></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="rank">User Role / Permissions:</label>
						<select class="form-input-style" name="edit_rank" id="edit_rank"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" style="padding-bottom: 5px" align="center" valign="middle">
						<input class="form-input-style" type="email" name="edit_email" id="edit_email" placeholder="Enter a E-Mail..." title="Enter a valid email" required/>
					</td>
				</tr>
				<!-- <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="password" name="edit_password" id="edit_password" placeholder="Enter a Password..."/>
					</td>
				</tr> -->
				<tr valign="baseline" class="form_height">
					<td width="50%" style="border-top: 1px solid var(--clr-border); border-bottom: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
						<span style="display: block;">Status</span>
					</td>
					<td width="50%" style="border-top: 1px solid var(--clr-border); border-bottom: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
						<label class="switch">
							<input type="checkbox" name="edit_status" id="edit_status" value="1">
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteAccountBtn">Delete Account</button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Update" />
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>