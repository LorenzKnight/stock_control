<div class="container-landscape">
	<img src="images/sys-img/rotate_device.gif" alt="Landscape Mode" width="250px">
</div>
<div class="container">
	<h1 id="hi-user" style="margin-left: 15px;"></h1>
	<div class="data-container flex profile-detail-height" id="profile-data">
		<div class="info-box-container">
			<div class="info-box">
				<h2><?= htmlspecialchars(tr('welcome_title') ?? 'Welcome to') ?></h2>
				<h4>All Stock Control</h4>
				<p style="line-height: 2;"><?= htmlspecialchars(tr('welcome_desc') ?? 'You now have full access to our stock management platform, giving you complete control over inventory tracking and optimization. Where will efficiency take your business today?') ?></p>
			</div>
		</div>
		<div class="small-box-container">
			<div class="small-box">
				<div class="box-title">
					<strong>
						<div class="box-title-icon" style="background: var(--alert-blue); color: var(--max-blue);">
							<svg
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="2"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<circle cx="12" cy="7" r="4"></circle>
								<path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
							</svg>
						</div>
					</strong>
					<span>
						<h3><?= htmlspecialchars(tr('smallbox_my_info') ?? 'My Info') ?></h3>
					</span>
				</div>
				<span id="my-data"></span>
				<button class="button-style-neutral" id="edit-my-data"><?= htmlspecialchars(tr('edit_my_data') ?? 'Update Info') ?></button>
			</div>
			<div class="small-box">
				<div class="box-title">
					<strong>
						<div class="box-title-icon" style="background: var(--warning-red); color: var(--cancel-red);">
							<svg
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="2"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z"></path>
								<path d="M4 7.5l8 4.5 8-4.5"></path>
								<path d="M12 12v9"></path>
							</svg>
						</div>
					</strong>
					<span>
						<h3><?= htmlspecialchars(tr('smallbox_selected_pack') ?? 'Selected Pack') ?></h3>
					</span>
				</div>
				<div id="subsc"></div>
				<button class="button-style-neutral" id="subsc-button"><?= htmlspecialchars(tr('subscription') ?? 'Subscription') ?></button>
			</div>
			<div class="small-box">
				<div class="box-title">
					<strong>
						<div class="box-title-icon" style="background: var(--alert-green); color: var(--agree-green);">
							<svg
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="2"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<rect x="5" y="3" width="14" height="18" rx="1"></rect>
								<path d="M9 7h2"></path>
								<path d="M13 7h2"></path>
								<path d="M9 11h2"></path>
								<path d="M13 11h2"></path>
								<path d="M9 15h2"></path>
								<path d="M13 15h2"></path>
								<path d="M10 21v-3h4v3"></path>
							</svg>
						</div>
					</strong>
					<span>
						<h3><?= htmlspecialchars(tr('smallbox_company_data') ?? 'Company Data') ?></h3>
					</span>
				</div>
				<span id="company-data"></span>
				<button class="button-style-neutral" id="manage-comp-button"><?= htmlspecialchars(tr('manage_companies') ?? 'Manage companies') ?></button>
			</div>
			<div class="small-box">
				<div class="box-title">
					<strong>
						<div class="box-title-icon" style="background: var(--warning-yellow); color: var(--warning-orange);">
							<svg
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								stroke-width="2"
								stroke-linecap="round"
								stroke-linejoin="round"
							>
								<circle cx="9" cy="8" r="3"></circle>
								<circle cx="17" cy="12" r="2.5"></circle>
								<path d="M3 21a6 6 0 0 1 12 0"></path>
								<path d="M14 21a5 5 0 0 1 7 0"></path>
							</svg>
						</div>
					</strong>
					<span>
						<h3><?= htmlspecialchars(tr('smallbox_spot') ?? 'Spot') ?></h3>
					</span>
				</div>
				<h2><span id="spot">0</span> / <span id="total-spot">0</span></h2>
				<button class="button-style-neutral" id="add-members-button"><?= htmlspecialchars(tr('add_members') ?? 'Add Members') ?></button>
			</div>
		</div>
	</div>
	<div class="data-container members-container-height">
		<h2 style="margin-left: 10px;"><?= htmlspecialchars(tr('user_list') ?? 'User List') ?></h2>
		<div class="members-table" id="child-user-table"><?= htmlspecialchars(tr('loading') ?? 'Loading...') ?></div>
	</div>
</div>