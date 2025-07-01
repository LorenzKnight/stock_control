<div class="bg-popup" id="add-customers-form">
	<div class="formular-frame">
		<form method="post" name="formCustomers" id="formCustomers" enctype="multipart/form-data">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Add Customer</h2>
					</td>      
				</tr>
				<tr valign="baseline" class="form_height">
					<td colspan="2" align="center" valign="middle">
						<div class="drop-area" id="customer-drop-area">
							<img class="image-preview" id="customer-pic-preview" src="" alt="customer pic preview">
							<p>Drop image here or click to select</p>
							<input type="file" name="customer_image" id="customer_image" accept="image/*" style="display: none;">
						</div>
					</td>
				</tr>
			</table>
			<div class="customer-form-menu">
				<ul>
					<li id="tab-customer-data">customers data</li>
					<li id="tab-customer-reference">customers Reference</li>
				</ul>
			</div>
			<div id="customer-data">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="text" name="customer_name" id="customer_name" placeholder="Enter a name..." title="Enter a valid name"/>
						</td>
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="text" name="customer_surname" id="customer_surname" placeholder="Enter a surname..." title="Enter a valid surname"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<input class="form-input-style" type="text" name="customer_email" id="customer_email" placeholder="Enter a email..." title="Enter a valid name"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<input class="form-input-style" type="text" name="customer_address" id="customer_address" placeholder="Enter a address..." title="Enter a valid address"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="text" name="customer_phone" id="customer_phone" placeholder="Enter a phone..." title="Enter a valid phone"/>
						</td>
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="date" name="customer_birthday" id="customer_birthday" placeholder="Enter a birthday date..." title="Enter a birthday date"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="customer_document_type" id="customer_document_type"></select>
						</td>
						<td width="50%" align="center" valign="middle">
							<input class="form-medium-input-style" type="text" name="customer_document_no" id="customer_document_no" placeholder="Document no..." title="Document no"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="customer_type" id="customer_type"></select>
						</td>
						<td width="50%" align="center" valign="middle">
							<select class="form-input-style" name="customer_status" id="customer_status"></select>
						</td>
					</tr>
				</table>
			</div>
			<div class="" id="customer-reference">
				<table width="80%" align="center" cellspacing="0">
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<input class="form-input-style" type="text" name="references_1" id="references_1" placeholder="Reference 1 Name and Surname..." title="Enter a valid Name and Surname"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<input class="form-input-style" type="text" name="references_1_phone" id="references_1_phone" placeholder="Reference 1 Phone..." title="Enter a valid phone"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<input class="form-input-style" type="text" name="references_2" id="references_2" placeholder="Reference 2 Name and Surname..." title="Enter a valid Name and Surname"/>
						</td>
					</tr>
					<tr valign="baseline" class="form_height">
						<td colspan="2" align="center" valign="middle">
							<input class="form-input-style" type="text" name="references_2_phone" id="references_2_phone" placeholder="Reference 2 Phone..." title="Enter a valid phone"/>
						</td>
					</tr>
				</table>
			</div>
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline" class="form_height">
					<td colspan="1" align="center" valign="middle">
						<button type="button" class="neutral-btn">Cancel</button>
					</td>
					<td colspan="1" align="center" valign="middle">
						<input type="submit" class="button-style-agree" value="Create" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>