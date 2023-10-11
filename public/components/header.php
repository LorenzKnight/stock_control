<header>
    <div class="container">
        <nav>
            <ul class="menu">
                <li>
                    <div class="logo">
                    <h3>Music Player</h3>
                    </div>
                </li>
                <li><a href="discover">Home</a></li>
                <li><a href="#">Explore</a></li>
                <li><a href="#">Library</a></li>
                <li>
                    <input type="text" name="searchField" id="search-field" class="search-field">
                </li>
                <li><a href="uploader">Upload</a></li>
                <li><a href="#">Settings</a></li>
                <?php if($_SESSION['mp_UserId'] != '') { ?>
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