<?php // phpinfo(); ?>
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

	<div class="container" id="result-container">
		<div class="wrapper-search">
			<div class="main-content" id="main-content">
			
			</div>
		</div>

        <div class="wrapper-home hidden" id="album">
			<div class="sidebar">
			</div>
			<div class="main-content">
				<?php
				foreach ($my_lists as $list) {
					// cdebug($list);
					$song = select_from('playlist', [], ['list_id' => $list['lid']], ['limit' => 1]);
					$songData = !empty($song) ? select_from('song', [], ['sid' => $song[0]['song_id']]) : '';
				?>
					<div class="list" data-list="<?= htmlspecialchars($list['lid'], ENT_QUOTES, 'UTF-8'); ?>">
						<div class="list-cover">
							<?php if (!empty($songData[0]['cover'])) { ?>
								<img src="images/cover/<?= $songData[0]['cover']; ?>">
							<?php } ?>
							<div class="list-options">
								<ul>
									<li>
										<a href="#" class="playlist-mini-menu" data-listId="<?= $list['lid']; ?>">...</a>
										<div class="playlist-options"></div>
									</li>
									<!-- <li>option</li> -->
								</ul>
							</div>
						</div>
						<div class="list-info">
							<div class="list-name"><?= htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?></div>
							<div class="list-owner" data-ownerId="<?= $list['user_id']; ?>"><?= htmlspecialchars($user_data['name'].' '.$user_data['surname'], ENT_QUOTES, 'UTF-8'); ?></div>
						</div>
					</div>
				<?php
				}
				?>
			</div>
        </div>
        
        <div class="wrapper-home hidden" id="listing">
            <div class="list-content">
				<div class="list-header">
					<a href="discover">< Back</a>
					<h2><?= $my_lists[0]['list_name']; ?></h2>
					<div class="list-owner" data-ownerId="<?= $list['user_id']; ?>" style="font-size: 12px;"><?= htmlspecialchars($user_data['name'].' '.$user_data['surname'], ENT_QUOTES, 'UTF-8'); ?></div>
				</div>
				<table class="music-list" cellspacing="0">
				<?php 
				$queueIndex = 0;
				foreach($my_playlist as $song_data) {
					$songs = select_from('song', [], ['sid' => $song_data['song_id']]);
					$song = $songs[0];
				?>
					<tr>
						<td>
							<div class="songs-cover">
								<img src="<?= empty($song['cover']) ? 'images/profile/'.$user_data['image'] : 'images/cover/'.$song['cover']; ?>" >
							</div>
						</td>
						<td class="song-list" data-queue-index="<?= $queueIndex; ?>" data-id="<?= $song['sid']; ?>" data-song="<?= $song['song_name']; ?>" data-file="<?= $song['file_name'];?>">
							<?= ucwords(strtolower($song['artist'])).' - '.ucwords(strtolower($song['song_name'])); ?>
						</td>
						<td width="5%">
							<button class="actions-btn" id="actions-btn" data-menu="<?= $song['song_id']; ?>">ooo</button>
							<div class="song-actions" id="song-actions" data-owner="<?= $song['user_id']; ?>">
								<ul>
									<li class="addPlaylist" data-songId="<?= $song_data['song_id']; ?>">Add playlist</li>
									<li class="removeFromPlaylis" data-removeId="<?= $song_data['pid']; ?>">Remove</li>
									<li class="deleteFile" data-deleteId="<?= $song_data['song_id']; ?>" style="display: none;">Delete</li>
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

        <div class="wrapper-home hidden" id="uploader-container" style="background-color: #fff;">
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

		<div class="wrapper-home hidden" id="owner">
			<div class="list-content">
				AQUI!
			</div>
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
    <!-- <div class="bg-overlayer" style="display: none;"></div> -->

    <script>
		var currentUser = <?= json_encode($user_data); ?>;
    </script>
</body>

</html>