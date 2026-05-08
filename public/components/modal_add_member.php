<div class="bg-popup" id="add-members-form">
	<div class="formular-frame">
        <form method="post" name="formMembers" id="formMembers">
            <table width="80%" align="center" cellspacing="0">
                <tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= $t['add_member_title'] ?></h2>
					</td>      
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="name"><?= $t['form_name'] ?>:</label>
						<input class="form-input-style" type="text" name="name" id="name" placeholder="Enter a name..." title="Enter a valid name" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="surname"><?= $t['form_surname'] ?>:</label>
						<input class="form-input-style" type="text" name="surname" id="surname" placeholder="Enter a surname..." title="Enter a valid surname" required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="birthday"><?= $t['form_birthday'] ?>:</label>
						<input class="form-input-style" type="date" name="birthday" id="birthday" placeholder="" title="" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<label for="country_code"><?= $t['form_country_code'] ?>:</label>
						<select class="form-medium-input-style" name="member_country_code" id="member_country_code" required></select>
					</td>
					<td width="50%" align="center" valign="middle">
						<label for="phone"><?= $t['phone'] ?>:</label>
						<input class="form-medium-input-style" type="text" name="phone" id="phone" placeholder="Enter a phone number..." title="Enter a valid phone number"/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="company"><?= $t['company_name'] ?>:</label>
						<select class="form-input-style" name="company" id="company" required></select>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="rank"><?= $t['form_user_role'] ?>:</label>
						<select class="form-input-style" name="rank" id="rank" required></select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="email">E-Mail:</label>
						<input class="form-input-style" type="email" name="email" id="email" placeholder="Enter a E-Mail..." title="Enter a valid email" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="password"><?= $t['password'] ?>:</label>
						<input class="form-input-style" type="password" name="password" id="password" placeholder="Enter a Password..." required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<button type="button" class="neutral-btn"><?= $t['cancel'] ?></button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="<?= $t['create'] ?>" />
					</td>
				</tr>
            </table>
        </form>
    </div>
</div>