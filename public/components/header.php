<header>
	<div class="header-container">
		<nav>
			<div style="flex: 1;">
				<div class="logo">
					<a href="profile.php"><h3>All<strong>Stock</strong>Control</h3></a>
				</div>
			</div>
			<ul class="menu" id="header-menu">
				<li class="no-redirect" id="home-site">
					<img src="images/sys-img/home-icon.png" alt="Home" id="notification-icon" class="home-icon">
				</li>
				<li>Sales</li>
				<li>Products</li>
				<li>Customers</li>
				<li class="hidden" id="shipping-seccion">Shipping</li>
				<li>Payments</li>
				<li class="no-redirect" id="notification-site">
					<img src="images/sys-img/notif.png" alt="Notification" id="notification-icon" class="notification-icon">
					<div class="notifications-no" id="notif-count" style="display: none;"></div>
				</li>
				<li class="no-redirect">
					<div class="profile" id="profileTrigger">
						<img id="header-profile-pic" src="" alt="header profile pic">
					</div>
					<div id="profileDropdown" style="display: none;">
						<ul>
							<li class="no-redirect" style="border-bottom: 1px solid var(--clr-light-border);"><a href="profile.php" style="margin: 0 auto; font-weight: bold;" id="my-name"></a></li>
							<li id="system-admin-site"><a href="#">System-Admin</a></li>
							<!-- <li id="reports-site"><a href="#">Reports</a></li> -->
							<li><a href="#">Settings</a></li>
							<li class="no-redirect" style="border-top: 1px solid var(--clr-light-border);"><a href="#" style="margin: 0 auto;" class="logout-button turn-off">Log Out</a></li>
						</ul>
					</div>
				</li>
			</ul>
		</nav>
	</div>
</header>