<?php require_once('logic/discover_be.php');?>
<!DOCTYPE html>
<html class="no-js" lang="sw">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Music Player</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/components.css">
  <script src="https://kit.fontawesome.com/5e05ee9535.js" crossorigin="anonymous"></script>
  <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet"> -->
  <script defer src="js/music_player.js"></script>
  <script defer src="js/actions.js"></script>
</head>

<body>
  <?php // var_dump($my_lists[0]); ?>
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
          <li><a href="#">Library</a></li>
          <li>
            <input type="text" name="searchField" id="search-field" class="search-field">
          </li>
          <li><a href="#">Upload</a></li>
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

  <div class="container" id="result-container">
    <?php if(!isset($_GET['list'])) { ?>
    <div class="wrapper-home">
      <div class="sidebar">
      </div>
      <div class="main-content" id="main-content">
        <?php 
        foreach($my_lists as $list) {
        ?>
        <div class="list">
          <div class="list-cover"></div>
          <div class="list-info">
            <div class="list-name" data-list="<?= $list['listingsId']; ?>"><?= $list['listName']; ?></div>
            <?= $current_user['name'].' '.$current_user['surname']; ?>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php } else { ?>
    <div class="wrapper-list" id="wrapper-list">
      <div class="list-content" id="list-content">
        <h2><?= $my_lists[0]['listName']; ?></h2>
        <table class="music-list" cellspacing="0">
        <?php 
        $queueIndex = 0;
        foreach($my_songs as $song_data) {
          $song = song_list($song_data['songId']);
        ?>
          <tr>
            <td>
              <div class="songs-cover">
                <img src="images/profile/<?php ?>perfil.png" alt="">
              </div>
            </td>
            <td class="song-list" data-queue-index="<?= $queueIndex; ?>" data-id="<?= $song['songId']; ?>" data-song="<?= $song['songName']; ?>" data-file="<?= $song['fileName'];?>"><?= $song['artist'].' - '.$song['songName']; ?></td>
            <td width="5%">
              <button class="actions-btn" id="actions-btn" data-menu="<?= $song['songId']; ?>">o o o</button>
              <div class="song-actions" id="song-actions">
                <ul>
                  <li class="addPlaylist" data-songId="<?= $song['songId']; ?>">Add playlist</li>
                  <li>Action 2</li>
                  <li>Action 3</li>
                </ul>
              </div>
            </td>
          </tr>
        <?php 
        $queueIndex++;
        } 
        ?>
        </table>
      </div>
    </div>
    <?php } ?>
  </div>

  <footer>
    <div class="container">
      <div class="music-player">
        <!-- Equalizer animation -->
        <div class="equalizer-container" id="equalizer">
          <div class="bar1"></div>
          <div class="bar2"></div>
          <div class="bar3"></div>
          <div class="bar4"></div>
          <div class="bar5"></div>
        </div>
        <!-- Music player controls -->
        <div class="music-player-btns">
          <ul class="player-btns" id="player-btns">
            <li data-btn="stop">
              <div class="music-player-btn" data-btn="backward"><i class="fas fa-step-backward" data-btn="backward"></i>
              </div>
            </li>
            <li>
              <div class="music-player-btn" data-btn="pause"><i class="fas fa-pause" data-btn="pause"></i></div>
            </li>
            <li>
              <div class="music-player-btn" data-btn="play"><i class="fas fa-play" data-btn="play"></i></div>
            </li>
            <li data-btn="stop">
              <div class="music-player-btn" data-btn="stop"><i class="fas fa-stop" data-btn="stop"></i></div>
            </li>
            <li data-btn="stop">
              <div class="music-player-btn" data-btn="forward"><i class="fas fa-step-forward" data-btn="forward"></i>
              </div>
            </li>
          </ul>
        </div>
        <div class="slidecontainer">
          <input type="range" min="0" max="100" value="50" class="volumeSlider" id="volume-slider">
          <span id="volume-value">50</span>
        </div>
        <div class="slide-playing-now">
          <div class="playing-now">
            <span class="playing-now-text" id="playing-now">No music is played!</span>
          </div>
        </div>
      </div>
    </div>
  </footer>
  
  <?php include("components/popup_bg.php"); ?>
  <div class="bg-overlayer" style="display: none;">

  </div>

</body>

</html>