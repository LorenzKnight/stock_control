<div class="bg-popup" id="storage-options">
	<div id="formular-medium-frame" class="formular-frame">
        <div id="storage-menu-buttons">
			<table width="80%" align="center" cellspacing="0">
				<tr valign="baseline">
					<td style="font-size: 12px;" colspan="6" align="center" valign="middle">
						<h2>Storage Options</h2>
					</td>      
				</tr>
                <tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="manageSlotBtn">Manage Slot</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="button-style-agree" id="manageStorageBtn">Manage Storage</button>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="6" align="center" valign="middle">
						<button type="button" class="neutral-btn" style="margin-top: 10px;">Close</button>
					</td>
				</tr>
			</table>
		</div>
        <!-- <div id="manage-slot-modal" style="display: none;">
            <div class="back-to-storage-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
            <form method="post" name="formManageSlot" id="formManageSlot" enctype="multipart/form-data">
                <table width="90%" align="center" cellspacing="0">
                    <tr valign="baseline">
                        <td colspan="6" align="center" valign="middle">
                            <h2>Add or Edit Slot</h2>
                        </td>      
                    </tr>
                    <tr valign="baseline">
                        <td colspan="3" align="center" valign="middle">
                            <div class="formular-category-list">
                                <div class="create-list-holder" id="clic-create-mark">
                                    <table width="100%" align="center" cellspacing="0">
                                        <tr valign="baseline">
                                            <td width="70%" align="center" valign="middle">
                                                <input type="text" class="form-medium-input-style" name="input-search-slot" id="input-search-slot" placeholder="Search slot"/>
                                            </td>
                                            <td width="30%" align="center" valign="middle">
                                                <button type="button" class="button-style-agree" id="add-slot-btn">Create slot</button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="cat-all-list">
                                    <table class="all-mark-list" id="slot-list" cellspacing="0">
                                    
                                    </table>
                                </div>
                            </div>
                        </td>
                        <td colspan="3" align="center" valign="middle">
                            <div class="formular-category-list">
                                <table class="hidden" id="slot-form" width="100%" align="center" cellspacing="0">
                                    
                                    <tr valign="baseline">
                                        <td colspan="6" align="center" valign="middle">
                                            <label for="slot_name">Slot Name:</label>
                                            <input class="form-input-style" type="text" name="slot_name" id="slot_name" placeholder="Slot Name..." title="Slot Name"/>
                                        </td>
                                    </tr>
                                    <tr valign="baseline">
                                        <td colspan="6" align="center" valign="middle">
                                            <label for="current_capacity">Current Capacity:</label>
                                            <input class="form-input-style" type="number" name="current_capacity" id="current_capacity" placeholder="Current Capacity..." title="Current Capacity"/>
                                        </td>
                                    </tr>
                                    <tr valign="baseline">
                                        <td colspan="6" align="center" valign="middle">
                                            <label for="max_capacity">Max Capacity:</label>
                                            <input class="form-input-style" type="number" name="max_capacity" id="max_capacity" placeholder="Max Capacity..." title="Max Capacity"/>
                                        </td>
                                    </tr>
                                    <tr valign="baseline">
                                        <td colspan="6" align="center" valign="middle">
                                            <label for="slot_description">Description:</label>
                                            <textarea class="form-input-style" id="slot_description" name="slot_description" rows="5" cols="35">
                                            </textarea>
                                        </td>
                                    </tr>
                                    <tr valign="baseline">
                                        <td colspan="3" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="left" valign="middle">
                                            <span style="display: block;">Status</span>
                                        </td>
                                        <td colspan="3" style="border-block: 1px solid var(--clr-border); padding: 5px 10px;" align="right" valign="middle">
                                            <label class="switch">
                                                <input type="checkbox" name="slot_status" id="slot_status" value="1" checked>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                    </tr>

                                    <input type="hidden" name="slot_id" id="slot_id" value="">
                                </table>
                                <table id="not-slot-form" width="100%" align="center" cellspacing="0">
                                    <tr valign="baseline" class="form_height">
                                        <td colspan="2" align="center" valign="middle">
                                            <p>Slot Info</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
                            <button type="button" class="neutral-btn">Cancel</button>
                        </td>
                        <td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
                            <input type="submit" class="button-style-agree" id="slot-action-btn" value="Ok" />
                        </td>
                    </tr>
                </table>
            </form>
        </div> -->
        <div id="manage-storage-modal" style="display: none;">
            <div class="back-to-storage-menu-btn">
				<img src="images/sys-img/backward.png" alt="back">
			</div>
            <form method="post" name="formManageStorage" id="formManageStorage" enctype="multipart/form-data">
                <table width="90%" align="center" cellspacing="0">
                    <tr valign="baseline">
                        <td colspan="6" align="center" valign="middle">
                            <h2>Add or Edit Storage</h2>
                        </td>      
                    </tr>
                    <tr valign="baseline">
                        <td colspan="3" align="center" valign="middle">
                            <div class="formular-category-list">
                                <div class="create-list-holder" id="clic-create-mark">
                                    <input type="text" class="form-medium-input-style" name="input-search-storage" id="input-search-storage" placeholder="Search slot"/>
                                </div>
                                <div class="cat-all-list">
                                    <table class="all-mark-list" id="storages-list" cellspacing="0">
                                    
                                    </table>
                                </div>
                            </div>
                        </td>
                        <td colspan="3" align="center" valign="middle">
                            <div class="formular-category-list">
                                <div class="create-list-holder" id="clic-create-mark">
                                    <input type="text" class="form-medium-input-style" name="input-search-product" id="input-search-product" placeholder="Search product"/>
                                </div>
                                <div class="cat-all-list">
                                    <table class="all-mark-list" id="products-list" cellspacing="0">
                                    
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr valign="baseline">
                        <td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
                            <button type="button" class="neutral-btn">Cancel</button>
                        </td>
                        <td colspan="3" align="center" valign="middle" style="padding-top: 10px;">
                            <input type="submit" class="button-style-agree" id="storage-action-btn" value="Ok" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>