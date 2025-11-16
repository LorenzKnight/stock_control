<div class="bg-popup" id="extra-services-options">
	<div class="formular-frame">
        <div id="extra-services-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Extra Services Options</h2>
						<!-- <p id="ord-no-name"></p> -->
					</td>      
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="editExtraServiceBtn">Edit Extra Service</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="cancel-btn" id="deleteExtraServiceBtn">Delete Extra Service</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn">Close</button>
					</td>
				</tr>
			</table>
		</div>
        <div id="edit-extra-services-modal" style="display: none;">
			<div class="edit-back-to-services-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
			<form method="post" name="formEditExtraServices" id="formEditExtraServices" data-extra-service-id="">
			    <table width="80%" align="center" cellspacing="0">
					<tr valign="baseline">
						<td colspan="6" align="center" valign="middle">
							<h2>Edit Extra Service</h2>
						</td>      
					</tr>
                    <tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<label for="edit_extra_service_name">Extra Service Name:</label>
							<input class="form-input-style" type="text" name="edit_extra_service_name" id="edit_extra_service_name" placeholder="Name the service." title="Name the service."/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="6" align="center" valign="middle">
							<label for="edit_extra_service_price">Extra Service Price:</label>
							<input class="form-input-style" type="text" name="edit_extra_service_price" id="edit_extra_service_price" placeholder="Enter a amount..." title="Enter a amount" required/>
						</td>
					</tr>
					
					<tr valign="baseline" class="form_height">
						<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
							<span style="display: block;">Status</span>
						</td>
						<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
							<label class="switch">
								<input type="checkbox" name="edit_service_status" id="edit_service_status" value="1" checked>
								<span class="slider round"></span>
							</label>
						</td>
					</tr>
                    <tr valign="baseline" class="form_height">
                        <td width="50%" style="padding: 15px 0" align="center" valign="middle">
                            <button type="button" class="neutral-btn">Cancel</button>
                        </td>
                        <td width="50%" style="padding: 15px 0" align="center" valign="middle">
                            <input type="submit" class="button-style-agree" value="Update Service" />
                        </td>
                    </tr>
				</table>
			</form>
		</div>

    </div>
</div>