<header>
	<div class="container">
		<nav>
			<ul class="menu">
				<li>
					<div class="logo">
					<h3>Music Drive</h3>
					</div>
				</li>
				<li><a href="discover?home=1">News</a></li>
				<li><a href="#">Library</a></li>
				<li class="search-holder">
					<input type="text" name="searchField" id="searchField" class="search-field">
				</li>
				<li><a href="discover?album=1">Explore</a></li>
				<li><a href="discover?uploader=1">Upload</a></li>
				<?php if(isset($_SESSION['mp_UserId']) && $_SESSION['mp_UserId'] != null) { ?>
				<li>
					<div class="profile" id="profileTrigger">
						<img src="images/profile/<?= $user_data['image']; ?>" alt="">
					</div>
					<div id="profileDropdown" style="display: none;">
						<ul>
							<li><a href="#"><?= $user_data['name'].' '.$user_data['surname']; ?></a></li>
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