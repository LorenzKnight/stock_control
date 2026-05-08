<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<h1 id="hi-user" style="margin-left: 15px;"></h1>
	<div class="data-container flex profile-detail-height" id="profile-data">
		<div class="info-box-container">
			<div class="info-box">
				<h2><?= htmlspecialchars($t['welcome_title'] ?? 'Welcome to') ?></h2>
				<h4>All Stock Control</h4>
				<p style="line-height: 2;"><?= htmlspecialchars($t['welcome_desc'] ?? 'You now have full access to our stock management platform, giving you complete control over inventory tracking and optimization. Where will efficiency take your business today?') ?></p>
			</div>
		</div>
		<div class="small-box-container">
			<div class="small-box">
				<div class="box-title">
					<h2><?= htmlspecialchars($t['smallbox_my_info'] ?? 'My Info') ?></h2>
				</div>
				<span id="my-data"></span>
				<button class="button-style-neutral" id="edit-my-data"><?= htmlspecialchars($t['edit_my_data'] ?? 'Update Info') ?></button>
			</div>
			<div class="small-box">
				<div class="box-title">
					<h2><?= htmlspecialchars($t['smallbox_selected_pack'] ?? 'Selected Pack') ?></h2>
				</div>
				<div id="subsc"></div>
				<button class="button-style-neutral" id="subsc-button"><?= htmlspecialchars($t['subscription'] ?? 'Subscription') ?></button>
			</div>
			<div class="small-box">
				<div class="box-title">
					<h2><?= htmlspecialchars($t['smallbox_company_data'] ?? 'Company Data') ?></h2>
				</div>
				<span id="company-data"></span>
				<button class="button-style-neutral" id="manage-comp-button"><?= htmlspecialchars($t['manage'] ?? 'Manage') ?></button>
			</div>
			<div class="small-box">
				<div class="box-title">
					<h2><?= htmlspecialchars($t['smallbox_spot'] ?? 'Spot') ?></h2>
				</div>
				<h2><span id="spot">0</span> / <span id="total-spot">0</span></h2>
				<button class="button-style-neutral" id="add-members-button"><?= htmlspecialchars($t['add_members'] ?? 'Add Members') ?></button>
			</div>
		</div>
	</div>
	<div class="data-container members-container-height">
		<h2 style="margin-left: 10px;"><?= htmlspecialchars($t['user_list'] ?? 'User List') ?></h2>
		<div class="members-table" id="child-user-table"><?= htmlspecialchars($t['loading'] ?? 'Loading...') ?></div>
	</div>
</div>