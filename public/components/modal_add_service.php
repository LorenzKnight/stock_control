<div class="bg-popup" id="add-services-form">
	<div class="formular-frame">
		<form method="post" name="formAddServices" id="formAddServices">
			<input type="hidden" name="user_id" id="service_user_id" value="">
			
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<h2>Create a Service</h2>
					</td>      
				</tr>
                <tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="service_name" id="service_name" placeholder="Name the service." title="Name the service."/>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="6" align="center" valign="middle">
						<input class="form-input-style" type="text" name="service_price" id="service_price" placeholder="Enter a amount..." title="Enter a amount" required/>
					</td>
				</tr>
				
				<tr valign="baseline" class="form_height">
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
						<span style="display: block;">Status</span>
					</td>
					<td width="50%" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
						<label class="switch">
							<input type="checkbox" name="service_status" id="service_status" value="1" checked>
							<span class="slider round"></span>
						</label>
					</td>
				</tr>
				<tr valign="baseline" class="form_height">
					<td width="50%" style="padding: 15px 0" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td width="50%" style="padding: 15px 0" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Add Service" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>