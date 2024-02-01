<header>
	<div class="container">
		<nav>
			<ul class="menu">
				<li>
					<div class="logo">
					<h3>Music Drive</h3>
					</div>
				</li>
				<li><a href="#">News</a></li>
				<li><a href="#">Explore</a></li>
				<li class="search-holder">
					<input type="text" name="searchField" id="searchField" class="search-field">
				</li>
				<li><a href="discover">Library</a></li>
				<li><a href="discover?uploader=1">Upload</a></li>
				<?php if(isset($_SESSION['mp_UserId']) && $_SESSION['mp_UserId'] != null) { ?>
				<li>
					<div class="profile" id="profileTrigger">
						<img src="images/profile/<?php ?>perfil.png" alt="">
					</div>
					<div id="profileDropdown" style="display: none;">
						<ul>
							<li><a href="#">Name</a></li>
							<li><a href="#">Settings</a></li>
							<li><a href="logout.php">Logout</a></li>
						</ul>
					</div>
				</li>
				<?php } else { ?>
				<li><a href="#" onclick="login()">Log in</a></li>
				<?php } ?>
			</ul>
		</nav>
	</div>
</header>