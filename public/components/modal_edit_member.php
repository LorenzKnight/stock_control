<div class="bg-popup" id="edit-members-form">
	<div class="formular-frame">
		<form action="stock.php" method="post" name="formEditMembers" id="formEditMembers" enctype="multipart/form-data">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= tr('edit_member_title') ?></h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="edit_name"><?= tr('form_name') ?>:</label>
						<input class="form-input-style" type="text" name="edit_name" id="edit_name" placeholder="<?= tr('form_name') ?>..." title="<?= tr('form_name') ?>" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="edit_surname"><?= tr('form_surname') ?>:</label>
						<input class="form-input-style" type="text" name="edit_surname" id="edit_surname" placeholder="<?= tr('form_surname') ?>..." title="<?= tr('form_surname') ?>" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="edit_birthday"><?= tr('form_birthday') ?>:</label>
						<input class="form-input-style" type="date" name="edit_birthday" id="edit_birthday" placeholder="<?= tr('form_birthday') ?>..." title="<?= tr('form_birthday') ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<label for="country_code"><?= tr('form_country_code') ?>:</label>
						<select class="form-medium-input-style" name="edit_member_country_code" id="edit_member_country_code" required></select>
					</td>
					<td width="50%" align="center" valign="middle">
						<label for="edit_phone"><?= tr('phone') ?>:</label>
						<input class="form-medium-input-style" type="text" name="edit_phone" id="edit_phone" placeholder="<?= tr('phone') ?>..." title="<?= tr('phone') ?>"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="edit_company"><?= tr('company_name') ?>:</label>
						<select class="form-input-style" name="edit_company" id="edit_company" required></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="edit_rank"><?= tr('form_user_role') ?>:</label>
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
						<span style="display: block;"><?= tr('status') ?></span>
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
						<button type="button" class="cancel-btn" id="deleteAccountBtn"><?= tr('delete_account') ?></button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= tr('update') ?>" />
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= tr('cancel') ?></button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>