<?php
// phpinfo();
?>
<?php require_once('logic/discover_be.php'); ?>


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
    <script src="https://kit.fontawesome.com/5e05ee9535.js" crossorigin="anonymous"></script> <!-- iconos del reproductor -->
    <script defer src="js/music_player.js"></script>
	<script defer src="js/actions.js"></script>
	<script defer src="js/uploader.js"></script>
</head>

<body>
    <?php include("components/header.php"); ?>

    <div class="container hidden" id="result-container">
        <?php 
        if(!isset($_GET['list'])) {
        ?>
        <div class="wrapper-home">
            <div class="sidebar">
            </div>
            <div class="main-content" id="main-content">
            <?php
            foreach ($my_lists as $list) {
            ?>
                <div class="list" data-list="<?= htmlspecialchars($list['listingsId'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="list-cover"></div>
                    <div class="list-info">
                        <div class="list-name"><?= htmlspecialchars($list['listName'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <?= htmlspecialchars($current_user['name'].' '.$current_user['surname'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            <?php
            }
            ?>
            </div>
        </div>
        <?php
        } else {
            // var_dump($my_lists);
        ?>
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
        <?php
        }
        ?>
    </div>

    <div class="container hidden" id="uploader-container">
        <div class="wrapper-home" style="background-color: #fff;">
            <form action="inc/upload.php" method="post" enctype="multipart/form-data">
                <br>
                <div class="drop-area" id="drop-area">
                <p>Arrastra y suelta tus archivos aquí o haz clic para seleccionarlos.</p>
                <div><input type="file" name="file_name[]" id="file_name" multiple required></div>
                <!-- <div class="file-input-container">
                    <label for="fileInput" class="custom-file-label">Seleccionar archivos</label>
                    <input type="file" name="file_name[]" id="fileInput" multiple required>
                </div> -->
                </div>
                <br>
                <table class="table-form" cellspacing="0">
                <tr>
                    <td align="right">
                        <label for="artist">Artista:</label><br>
                        <input type="text" name="artist" id="artist" required>
                    </td>
                    <td align="left">
                        <label for="title">Título:</label><br>
                        <input type="text" name="title" id="title" required>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <label for="gender">Gender:</label><br>
                        <input type="text" name="gender" id="gender" required>
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <input type="radio" id="public" name="public" value="1" checked>
                        <label for="public">Public</label>
                    </td>
                    <td align="left">
                        <input type="radio" id="private" name="public" value="0">
                        <label for="private">Private</label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="Subir Archivos">
                    </td>
                </tr>
                </table>

                
            </form>
        </div>
    </div>

    <div class="status-message" id="status-message"></div>

    <footer>
        <div class="container">
            <div class="music-player">
                <!-- Music player controls -->
                <div class="music-player-btns">
                    <ul class="player-btns" id="player-btns">
                        <li data-btn="stop">
                            <div class="music-player-btn" data-btn="backward">
                                <i class="fas fa-step-backward" data-btn="backward"></i>
                            </div>
                        </li>
                        <li>
                            <div class="music-player-btn" data-btn="pause">
                                <i class="fas fa-pause" data-btn="pause"></i>
                            </div>
                        </li>
                        <li>
                            <div class="music-player-btn" data-btn="play">
                                <i class="fas fa-play" data-btn="play"></i>
                            </div>
                        </li>
                        <li data-btn="stop">
                            <div class="music-player-btn" data-btn="stop">
                                <i class="fas fa-stop" data-btn="stop"></i>
                            </div>
                        </li>
                        <li data-btn="stop">
                            <div class="music-player-btn" data-btn="forward">
                                <i class="fas fa-step-forward" data-btn="forward"></i>
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
                <!-- Equalizer animation -->
                <div class="equalizer-container hidden" id="equalizer">
                    <div class="bar1"></div>
                    <div class="bar2"></div>
                    <div class="bar3"></div>
                    <div class="bar4"></div>
                    <div class="bar5"></div>
                </div>
            </div>
        </div>
    </footer>
    
    <?php include("components/popup_bg.php"); ?>
    <div class="bg-overlayer" style="display: none;">

    </div>

    <script>
        // Función para mostrar la sección basada en la URL
        function showSectionBasedOnURL() {
            let section = 'result-container';

            // Si la URL termina en "/upload", cambia la sección a mostrar
            if (window.location.pathname.endsWith('/uploader')) {
                section = 'uploader-container';
            }

            document.getElementById(section).style.display = 'block';
        }

        showSectionBasedOnURL();
    </script>
</body>

</html>