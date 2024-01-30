<header>
    <div class="container">
        <nav>
            <ul class="menu">
                <li>
                    <div class="logo">
                    <h3>Music Player</h3>
                    </div>
                </li>
                <li><a href="#">Home</a></li>
                <li><a href="#">Explore</a></li>
                <li><a href="discover">Library</a></li>
                <li>
                    <input type="text" name="searchField" id="searchField" class="search-field">
                </li>
                <li><a href="discover?uploader=1">Upload</a></li>
                <li><a href="#">Settings</a></li>
                <?php if(isset($_SESSION['mp_UserId']) && $_SESSION['mp_UserId'] != null) { ?>
                <li><a href="logout.php">Logout</a></li>
                <li>
                    <div class="profile">
                    <img src="images/profile/<?php ?>perfil.png" alt="">
                    </div>
                </li>
                <?php } else { ?>
                <li><a href="#" onclick="login()">Log in</a></li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</header>