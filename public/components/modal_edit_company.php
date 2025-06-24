<div class="bg-popup" id="edit-company-form">
	<div class="formular-medium-frame">
		<form action="stock.php" method="post" name="formEditCompany" id="formEditCompany" enctype="multipart/form-data">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Add or Edit Company o Affiliate</h2>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="3" align="center" valign="middle">
						<div class="formular-category-list">
							<div class="create-list-holder" id="clic-create-mark">
								<button type="button" class="button-style-agree" id="add-aff-btn">Create Affiliate</button>
							</div>
							<div class="cat-all-list">
								<table class="all-mark-list" id="affiliate-list" cellspacing="0">
								
								</table>
							</div>
						</div>
					</td>
					<td colspan="3" align="center" valign="middle">
						<div class="formular-category-list">
							<table class="hidden" id="company-form" width="100%" align="center" cellspacing="0">
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
								
								<input type="hidden" name="company_id" id="company_id" value="">
							</table>
							<table id="not-company-form" width="100%" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td colspan="2" align="center" valign="middle">
										<p>Company Info</p>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="3" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td colspan="3" align="center" valign="middle">
						<input type="submit" class="button-style-agree" id="company-action-btn" value="Ok" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>