<footer class="footer">
	<div class="footer-seo">
		<h3><?= htmlspecialchars($t['footer_seo_title']) ?></h3>
		<ul>
			<li>
				<a class="gdpr-link" href="/<?= htmlspecialchars($lang) ?>/gdpr" target="_blank">
					<?= htmlspecialchars($t['gdpr_title']) ?>
				</a>
			</li>
			<!-- <li>
				<a href="<?= htmlspecialchars(url_with_lang($lang)) ?>#features">
					<?= htmlspecialchars($t['footer_seo_smallbiz']) ?>
				</a>
			</li>
			<li>
				<a href="<?= htmlspecialchars(url_with_lang($lang)) ?>#features">
					<?= htmlspecialchars($t['footer_seo_cloud']) ?>
				</a>
			</li>
			<li>
				<a href="<?= htmlspecialchars(url_with_lang($lang)) ?>#features">
					<?= htmlspecialchars($t['footer_seo_multilocation']) ?>
				</a>
			</li>
			<li>
				<a href="<?= htmlspecialchars(url_with_lang($lang)) ?>#pricing">
					<?= htmlspecialchars($t['footer_seo_pricing']) ?>
				</a>
			</li> -->
		</ul>
	</div>
	
	<div class="footer-content">
		<p>
			&copy; <?php echo date("Y"); ?> AllStockControl.
			<?= htmlspecialchars($t['footer_rights']) ?>
		</p>
	</div>
	<div class="contact-us" id="contactBox">
		<img src="../images/sys-img/email.gif" alt="e-mail" class="">
		
		<form id="contactForm" method="POST" action="/api/send_email.php">
			<button type="button" id="closeContactForm" class="close-btn">&times;</button>
			<table width="100%" align="center" cellspacing="0">
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<h2><?= htmlspecialchars($t['footer_contact_title']) ?></h2>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input
							class="form-input-style"
							name="contact-us-name"
							type="text"
							placeholder="<?= htmlspecialchars($t['footer_name']) ?>"
							required
						/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input
							class="form-input-style"
							name="contact-us-email"
							type="email"
							placeholder="<?= htmlspecialchars($t['footer_email']) ?>"
							required
						/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<textarea
							class="form-input-style"
							name="contact-us-message"
							placeholder="<?= htmlspecialchars($t['footer_message']) ?>"
							rows="4"
							required
						></textarea>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<button type="submit" class="button-style-agree">
							<?= htmlspecialchars($t['footer_send']) ?>
						</button>
					</td>
				</tr>
			</table>
			<!-- Honeypot field for spam prevention -->
			<input type="text" name="company" style="display:none">
		</form>
	</div>
</footer>