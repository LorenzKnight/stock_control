<div class="bg-popup" id="add-members-form">
	<div class="formular-frame">
        <form action="stock.php" method="post" name="formMembers" id="formMembers">
            <table width="80%" align="center" cellspacing="0">
                <tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Add your co-Workers</h2>
					</td>      
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="name" id="name" placeholder="Enter a name..." title="Enter a valid name" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="surname" id="surname" placeholder="Enter a surname..." title="Enter a valid surname" required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<label for="fecha">Birthdate:</label>
						<input class="form-input-style" type="date" name="birthday" id="birthday" placeholder="" title="" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="number" name="phone" id="phone" placeholder="Enter a phone number..." title="Enter a valid phone number" required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<label for="rank">User Role / Permissions:</label>
						<select class="form-input-style" name="rank" id="rank">
							<option value="">Select user role</option>
							<option value="1">Administrator (Full Access)</option>
							<option value="2">Manager (Manage teams & data)</option>
							<option value="3">Supervisor (Oversee operations)</option>
							<option value="4">Operator (Limited operational access)</option>
							<option value="5">Viewer (Read-only access)</option>
						</select>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="email" name="email" id="email" placeholder="Enter a E-Mail..." title="Enter a valid email" required/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="password" name="password" id="password" placeholder="Enter a Password..." required/>
					</td>
				</tr>
                <tr valign="baseline" class="form_height">
					<td width="50%" align="center" valign="middle">
						<button type="button" class="cancel-btn">Cancel</button>
					</td>
					<td width="50%" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Create" />
					</td>
				</tr>
            </table>
        </form>
    </div>
</div>