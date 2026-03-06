<div class="formular-front-frame positioning" id="formular-signup" style="display: none;">
	<div class="formular_front">
		<form method="post" name="formsignup" id="formsignup">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2><?= htmlspecialchars($t['signup_title']) ?></h2>
						<p><?= htmlspecialchars($t['signup_subtitle']) ?></p>
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<label for="name"><?= htmlspecialchars($t['signup_name_label']) ?></label>
						<input class="form-input-style"
							type="text"
							name="name"
							id="name"
							placeholder="<?= htmlspecialchars($t['signup_name_ph']) ?>"
							title="<?= htmlspecialchars($t['signup_name_title']) ?>"
							required
						/>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<label for="surname"><?= htmlspecialchars($t['signup_surname_label']) ?></label>
						<input class="form-input-style"
							type="text"
							name="surname"
							id="surname"
							placeholder="<?= htmlspecialchars($t['signup_surname_ph']) ?>"
							title="<?= htmlspecialchars($t['signup_surname_title']) ?>"
							required
						/>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<label for="email"><?= htmlspecialchars($t['signup_email_label']) ?></label>
						<input class="form-input-style"
							type="email"
							name="email"
							id="email"
							placeholder="<?= htmlspecialchars($t['signup_email_ph']) ?>"
							title="<?= htmlspecialchars($t['signup_email_title']) ?>"
							required
						/>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<label for="password"><?= htmlspecialchars($t['signup_password_label']) ?></label>
						<input class="form-input-style"
							type="password"
							name="password"
							id="password"
							placeholder="<?= htmlspecialchars($t['signup_password_ph']) ?>"
							required
						/>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<label for="confirm_password"><?= htmlspecialchars($t['signup_repeat_label']) ?></label>
						<input class="form-input-style"
							type="password"
							id="confirm_password"
							placeholder="<?= htmlspecialchars($t['signup_repeat_ph']) ?>"
							required
						/>
					</td>
				</tr>
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<table width="100%" align="center" cellspacing="0">
							<tr valign="baseline" height="45px">
								<td colspan="3" align="center" valign="middle">
									<input type="checkbox" id="signup-terms-check" name="acepto_terms" value="1" required>
								</td>
								<td colspan="5" align="left" valign="middle">
									<?= htmlspecialchars($t['signup_terms_prefix']) ?>
									<a href="/<?= htmlspecialchars($lang ?? 'en') ?>/terms" target="_blank" style="color: var(--warning-red); text-decoration: none;">
										<?= htmlspecialchars($t['signup_terms_link']) ?>
									</a>
									<?= htmlspecialchars($t['signup_terms_suffix']) ?>
								</td>
							</tr>
							<tr valign="baseline" height="55px">
								<td colspan="3" align="center" valign="middle">
									<input type="checkbox" id="signup-privacy-check" name="acepto_privacy" value="1" required>
								</td>
								<td colspan="5" align="left" valign="middle">
									<?= htmlspecialchars($t['signup_terms_prefix']) ?>
									<a href="/<?= htmlspecialchars($lang ?? 'en') ?>/gdpr" target="_blank" style="color: var(--warning-red); text-decoration: none;">
										<?= htmlspecialchars($t['gdpr_title']) ?>
									</a>
									<?= htmlspecialchars($t['signup_privacy_text']) ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<input type="submit"
							class="button-style-agree button-ghost"
							id="submit-signup"
							value="<?= htmlspecialchars($t['signup_submit']) ?>"
						/>
					</td>
				</tr>
				<tr valign="baseline" height="30px">
					<td colspan="6" align="center" valign="middle">
						<a href="#" class="close-link">
							<?= htmlspecialchars($t['signup_have_account']) ?>
						</a>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>