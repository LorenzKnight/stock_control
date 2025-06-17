<footer class="footer">
	<div class="footer-content">
		<p>&copy; <?php echo date("Y"); ?> All Stock Control. All rights reserved.</p>
		<!-- <ul class="footer-links">
			<li><a href="#">Privacy Policy</a></li>
			<li><a href="#">Terms of Service</a></li>
			<li><a href="#">Contact Us</a></li>
		</ul> -->
	</div>
	<div class="contact-us" id="contactBox">
		<img src="../images/sys-img/email.gif" alt="e-mail" class="">
		
		<form id="contactForm" method="POST">
			<button id="closeContactForm" class="close-btn">&times;</button>
			<table width="100%" align="center" cellspacing="0">
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<h2>Contact us</h2>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" name="contact-us-name" type="text" placeholder="Your name" required />
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<input class="form-input-style" name="contact-us-email" type="email" placeholder="Your email" required />
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<textarea class="form-input-style" name="contact-us-message" placeholder="Your menssage" rows="4" cols="35" required></textarea>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<button type="submit" class="button-style-agree">Send</button>
					</td>
				</tr>
			</table>
		</form>
	</div>
</footer>