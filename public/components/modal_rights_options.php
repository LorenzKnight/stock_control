<div class="bg-popup" id="rights-options">
	<div class="formular-frame">
        <div id="rights-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Right Options</h2>
						<!-- <p id="ord-no-name"></p> -->
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editRightBtn">Edit Right</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deletePaymentBtn">Delete Right</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
					</td>
				</tr>
			</table>
		</div>
        <div id="edit-right-modal" style="display: none;">
			<div class="edit-back-to-right-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditRight" id="formEditRight">
			    <table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td colspan="6" align="center" valign="middle">
							<h2>Edit Right</h2>
						</td>      
					</tr>
                    <tr valign="baseline" class="form_height">
                        <td colspan="6" align="center" valign="middle">
                            <select class="form-input-style" name="edit_service_name" id="edit_service_name"></select>
                        </td>
                    </tr>
                    <tr valign="baseline" class="form_height">
                        <td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
                            <span style="display: block;">Status</span>
                        </td>
                        <td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
                            <label class="switch">
                                <input type="checkbox" name="edit_can_access" id="edit_can_access" value="1" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    <tr valign="baseline" class="form_height">
                        <td width="50%" style="padding: 15px 0" align="center" valign="middle">
                            <button type="button" class="neutral-btn">Cancel</button>
                        </td>
                        <td width="50%" style="padding: 15px 0" align="center" valign="middle">
                            <input type="submit" class="button-style-agree" value="Update Right" />
                        </td>
                    </tr>
				</table>
			</form>
		</div>

    </div>
</div>