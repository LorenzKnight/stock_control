<div class="bg-popup" id="edit-company-form">
	<div class="formular-frame">
		<form action="stock.php" method="post" name="formEditCompany" id="formEditCompany" enctype="multipart/form-data">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="2" align="center" valign="middle">
						<h2>Edit your Company Info</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<div class="drop-area" id="company-logo-drop-area">
							<img class="image-preview" id="logo-preview" src="" alt="Logo preview">
							<p>Drop logo image here or click to select</p>
							<input type="file" name="company_logo" id="company_logo" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" type="text" name="company_name" id="company_name" placeholder="Company Name..." title="Company name"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" type="text" name="organization_no" id="organization_no" placeholder="Organization No." title="Organization No."/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" type="text" name="company_address" id="company_address" placeholder="Company Address..." title="Company Address"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" type="text" name="company_phone" id="company_phone" placeholder="Company Phone..." title="Company Phone"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Update" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>