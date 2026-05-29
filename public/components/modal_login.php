<div class="formular-front-frame positioning" id="formular-login" style="display: block;">
	<div class="formular_front">
		<form action="stock.php" method="post" name="formlogin" id="formlogin">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2><?= htmlspecialchars(tr('login_title')) ?></h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input 
							class="form-input-style" 
							type="email" 
							name="login_email" 
							id="login_email" 
							placeholder="<?= htmlspecialchars(tr('login_email_ph')) ?>" 
							title="<?= htmlspecialchars(tr('login_email_title')) ?>" 
							required
						/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input 
							class="form-input-style" 
							type="password" 
							name="login_password" 
							id="login_password" 
							placeholder="<?= htmlspecialchars(tr('login_password_ph')) ?>" 
							required
						/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td nowrap="nowrap" align="center" valign="middle">
						<input 
							type="submit" 
							class="button-style-agree" 
							value="<?= htmlspecialchars(tr('login_submit')) ?>" 
						/>
					</td>
				</tr>
				<input type="hidden" name="lang" value="<?= htmlspecialchars($lang ?? 'en') ?>">
			</table>
		</form>
	</div>
</div>