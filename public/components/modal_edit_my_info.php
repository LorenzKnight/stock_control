<div class="bg-popup" id="edit-my_info-form">
	<div class="formular-frame">
        <form method="post" name="formEditMyInfo" id="formEditMyInfo" enctype="multipart/form-data">
            <table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="2" align="center" valign="middle">
						<h2><?= $t['edit_profile_title']; ?></h2>
					</td>      
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<div class="drop-area" id="profile-drop-area">
							<img class="image-preview" id="profile-pic-preview" src="" alt="profile pic preview">
							<p><?= $t['form_drop_image']; ?></p>
							<input type="file" name="image" id="profile-img" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="user_name"><?= $t['form_name']; ?></label>
						<input class="form-input-style" type="text" name="user_name" id="user_name" placeholder="<?= $t['form_name']; ?>" title="<?= $t['form_name']; ?>" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="user_surname"><?= $t['form_surname']; ?></label>
						<input class="form-input-style" type="text" name="user_surname" id="user_surname" placeholder="<?= $t['form_surname']; ?>" title="<?= $t['form_surname']; ?>" required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="user_birthday"><?= $t['form_birthday']; ?></label>
						<input class="form-input-style" type="date" name="user_birthday" id="user_birthday" placeholder="<?= $t['form_birthday']; ?>" title="<?= $t['form_birthday']; ?>"/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<label for="country_code"><?= $t['form_country_code']; ?></label>
						<select class="form-medium-input-style" name="country_code" id="country_code" required></select>
					</td>
					<td width="50%" align="center" valign="middle">
						<label for="user_phone"><?= $t['phone']; ?></label>
						<input class="form-medium-input-style" type="text" name="user_phone" id="user_phone" placeholder="<?= $t['phone']; ?>" title="<?= $t['phone']; ?>"/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="user_email">E-Mail:</label>
						<input class="form-input-style" type="email" name="user_email" id="user_email" placeholder="E-Mail..." title="email" required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
                    <td width="50%" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= $t['cancel']; ?></button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= $t['update']; ?>" />
					</td>
				</tr>
            </table>
        </form>
    </div>
</div>