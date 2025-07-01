<div class="bg-popup" id="edit-my_info-form">
	<div class="formular-frame">
        <form action="stock.php" method="post" name="formEditMyInfo" id="formEditMyInfo" enctype="multipart/form-data">
            <table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="2" align="center" valign="middle">
						<h2>Edit My Info</h2>
					</td>      
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<div class="drop-area" id="profile-drop-area">
							<img class="image-preview" id="profile-pic-preview" src="" alt="profile pic preview">
							<p>Drop image here or click to select</p>
							<input type="file" name="image" id="image" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="user_name" id="user_name" placeholder="Enter your name..." title="Enter a valid name" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="user_surname" id="user_surname" placeholder="Enter your surname..." title="Enter a valid surname" required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="fecha">Birthdate:</label>
						<input class="form-input-style" type="date" name="user_birthday" id="user_birthday" placeholder="yyyy-mm-dd" title="Birthdate (yyyy-mm-dd)"/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="number" name="user_phone" id="user_phone" placeholder="Enter your phone number..." title="Enter a valid phone number"/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="email" name="user_email" id="user_email" placeholder="Enter your E-Mail..." title="Enter a valid email" required/>
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