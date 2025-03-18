<header>
	<div class="container">
		<nav>
			<ul class="menu">
				<li>
					<div class="logo">
					    <h3>StockControl</h3>
					</div>
				</li>
                <li class="search-holder">
					<!-- <input type="text" name="searchField" id="searchField" class="search-field"> -->
				</li>
				<li><a href="<?= isset($_SESSION['sc_UserId']) ? 'discover?news=1' : 'discover?login=1' ?>" style="<?= $_GET['news'] == 1 ? 'color: orange;' : '' ?>">Sales</a></li>
				<li><a href="<?= isset($_SESSION['sc_UserId']) ? 'discover?album=1' : 'discover?login=1' ?>" style="<?= $_GET['album'] == 1 ? 'color: orange;' : '' ?>">Products</a></li>
				
				<li><a href="<?= isset($_SESSION['sc_UserId']) ? 'discover?library=1' : 'discover?login=1' ?>" style="<?= $_GET['library'] == 1 ? 'color: orange;' : '' ?>">Customers</a></li>
				<li><a href="<?= isset($_SESSION['sc_UserId']) ? 'discover?uploader=1' : 'discover?login=1' ?>" style="<?= $_GET['uploader'] == 1 ? 'color: orange;' : '' ?>">Payments</a></li>
				<li>
					<div class="profile" id="profileTrigger">
						<img id="header-profile-pic" src="" alt="header profile pic">
					</div>
					<div id="profileDropdown" style="display: none;">
						<ul>
							<li><a href="#" id="my-name"></a></li>
							<li><a href="#">Settings</a></li>
							<li><a href="#" id="logout-button">Logout</a></li>
						</ul>
					</div>
				</li>
			</ul>
		</nav>
	</div>
</header>