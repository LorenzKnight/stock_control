<div class="bg-popup" id="edit-members-form">
	<div class="formular-frame">
		<form action="stock.php" method="post" name="formEditMembers" id="formEditMembers" enctype="multipart/form-data">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= $t['edit_member_title'] ?></h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="edit_name"><?= $t['form_name'] ?>:</label>
						<input class="form-input-style" type="text" name="edit_name" id="edit_name" placeholder="<?= $t['form_name'] ?>..." title="<?= $t['form_name'] ?>" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="edit_surname"><?= $t['form_surname'] ?>:</label>
						<input class="form-input-style" type="text" name="edit_surname" id="edit_surname" placeholder="<?= $t['form_surname'] ?>..." title="<?= $t['form_surname'] ?>" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="edit_birthday"><?= $t['form_birthday'] ?>:</label>
						<input class="form-input-style" type="date" name="edit_birthday" id="edit_birthday" placeholder="<?= $t['form_birthday'] ?>..." title="<?= $t['form_birthday'] ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<label for="country_code"><?= $t['form_country_code'] ?>:</label>
						<select class="form-medium-input-style" name="edit_member_country_code" id="edit_member_country_code" required></select>
					</td>
					<td width="50%" align="center" valign="middle">
						<label for="edit_phone"><?= $t['phone'] ?>:</label>
						<input class="form-medium-input-style" type="text" name="edit_phone" id="edit_phone" placeholder="<?= $t['phone'] ?>..." title="<?= $t['phone'] ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="edit_company"><?= $t['company_name'] ?>:</label>
						<select class="form-input-style" name="edit_company" id="edit_company" required></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="edit_rank"><?= $t['form_user_role'] ?>:</label>
						<select class="form-input-style" name="edit_rank" id="edit_rank"></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" style="padding-bottom: 5px" align="center" valign="middle">
						<label for="edit_email">E-Mail:</label>
						<input class="form-input-style" type="email" name="edit_email" id="edit_email" placeholder="E-Mail..." title="email" required/>
					</td>
				</tr>
				<!-- <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="password" name="edit_password" id="edit_password" placeholder="Enter a Password..."/>
					</td>
				</tr> -->
				<tr valign="baseline" class="form_height">
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
						<span style="display: block;"><?= $t['status'] ?></span>
					</td>
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
						<label class="switch">
							<input type="checkbox" name="edit_status" id="edit_status" value="1">
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteAccountBtn"><?= $t['delete_account'] ?></button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= $t['update'] ?>" />
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= $t['cancel'] ?></button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>