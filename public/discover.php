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
				<div class="main-content" id="main-content"></div>
			</div>
			<div class="wrapper-home hidden" id="login">
				<div class="main-content">
					<?php include("components/modal_login_signin.php"); ?>
					<!-- <div>

					</div> -->
				</div>
			</div>
			<div class="wrapper-home hidden" id="news">
				<div class="list-content scroll">
					News
				</div>
			</div>
			<div class="wrapper-home hidden" id="library">
				<div class="list-content scroll" id="list-content">
					<?php
					if (!empty($favoriteLists)) {
						foreach ($favoriteLists as $playlist) {
							if (isset($playlist['list_id'])) {
								$listData = select_from('listings', [], ['lid' => $playlist['list_id']]);
								foreach ($listData as $listDetail) {
									$listOwner = select_from('users', ['user_id', 'name', 'surname'], ['user_id' => $listDetail['user_id']]);
									$listCover = favorite_list_cover($listDetail['lid']);
					?>
									<div class="fav-list" data-favlist="<?= htmlspecialchars($listDetail['lid'], ENT_QUOTES, 'UTF-8'); ?>"> <!-- AQUI -->
										<div class="list-cover">
											<?php if (!empty($listCover)) { ?>
												<img src="images/cover/<?= $listCover; ?>">
											<?php } ?>
											<div class="list-options">
												<ul>
													<li>
														<a href="#" class="playlist-mini-menu">...</a>
														<div class="playlist-options">
															<ul>
																<li class="album-dislike" data-albumId="<?= $listDetail['lid']; ?>">Like</li>
															</ul>
														</div>
													</li>
													<!-- <li>option</li> -->
												</ul>
											</div>
										</div>
										<div class="list-info">
											<div class="list-name"><?= htmlspecialchars($listDetail['list_name'], ENT_QUOTES, 'UTF-8'); ?></div>
											<div class="list-owner" data-ownerId="<?= $listOwner[0]['user_id']; ?>"><?= htmlspecialchars($listOwner[0]['name'].' '.$listOwner[0]['surname'], ENT_QUOTES, 'UTF-8'); ?></div>
										</div>
									</div>
					<?php
								}
							}
						}
					}
					else
					{
					?>
						<p class="frame-central">You don't have any list yet</p>
					<?php
					} 
					?>
				</div>
			</div>
			<div class="wrapper-home hidden" id="album">
				<div class="sidebar">
				</div>
				<div class="main-content">
					<?php
					if (!empty($playlists)) {
						foreach ($playlists as $list) {
							$song = select_from('playlist', [], ['list_id' => $list['lid']], ['limit' => 1]);
							$songData = !empty($song) ? select_from('song', [], ['sid' => $song[0]['song_id']]) : '';
							// var_dump($list);
					?>
							<div class="list" data-list="<?= htmlspecialchars($list['lid'], ENT_QUOTES, 'UTF-8'); ?>"> <!-- revisa aqui -->
								<div class="list-cover">
									<?php if (!empty($songData[0]['cover'])) { ?>
										<img src="images/cover/<?= $songData[0]['cover']; ?>">
									<?php } ?>
									<div class="list-options">
										<ul>
											<li>
												<a href="#" class="playlist-mini-menu">...</a>
												<div class="playlist-options">
													<ul>
														<li class="album-like" data-albumId="<?= $list['lid']; ?>">Like</li>
													</ul>
												</div>
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
					}
					else
					{
					?>
						<p class="frame-central">You don't have any list yet</p>
					<?php
					} 
					?>
				</div>
			</div>
			
			<div class="wrapper-home hidden" id="listing">
				<div class="list-content">
					<div class="list-header">
						<a href="#" onclick="history.back(); return false;">< Back</a>
						<h2><?= $playlists[0]['list_name']; ?></h2>
						<div class="list-owner" data-ownerId="<?= $list['user_id']; ?>" style="font-size: 12px;"><?= htmlspecialchars($owner_by_list_id['name'].' '.$owner_by_list_id['surname'], ENT_QUOTES, 'UTF-8'); ?></div>
					</div>
					<table class="music-list" cellspacing="0">
					<?php 
					$queueIndex = 0;
					foreach($my_playlist as $song_data) {
						$songs = select_from('song', [], ['sid' => $song_data['song_id']]);
						$song = $songs[0];
					?>
						<tr>
							<td width="5%">
								<div class="songs-cover">
									<img src="<?= empty($song['cover']) ? 'images/profile/'.$user_data['image'] : 'images/cover/'.$song['cover']; ?>" >
								</div>
							</td>
							<td width="90%" class="song-list" data-queue-index="<?= $queueIndex; ?>" data-id="<?= $song['sid']; ?>" data-song="<?= $song['song_name']; ?>" data-file="<?= $song['file_name'];?>">
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
				<form action="inc/upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
					<br>
					<div class="drop-area" id="drop-area">
						<p>Arrastra y suelta tus archivos aquí o haz clic para seleccionarlos.</p>
						<div><input type="file" name="file_name[]" id="file_name" multiple required></div>
					</div>
					<br>
					<table class="table-form" cellspacing="0">
						<tr>
							<td align="right">
								<label for="artist">Artista:</label><br>
								<input class="form-input-style" type="text" name="artist" id="artist" required>
							</td>
							<td align="left">
								<label for="title">Título:</label><br>
								<input class="form-input-style" type="text" name="title" id="title" required>
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<label for="gender">Gender:</label><br>
								<input class="form-input-style" type="text" name="gender" id="gender" required>
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
								<input class="button-style-agree" type="submit" value="Subir Archivos">
							</td>
						</tr>
					</table>
				</form>
			</div>

			<div class="wrapper-home hidden" id="owner">
				<div class="list-content scroll">
					<div class="list-header">
						<div class="profile-frame">
							<!-- <a href="discover">< Back</a> -->
							<div class="big-profile">
								<img src="images/profile/<?= $current_owner[0]['image']; ?>" alt="">
							</div>
							<h1><?= htmlspecialchars($current_owner[0]['name'].' '.$current_owner[0]['surname'], ENT_QUOTES, 'UTF-8'); ?></h1>
						</div>
					</div>
					<div class="user-menu">
						<div class="user-nav">
							<ul>
								<li><a href="discover?owner=<?= $_GET['owner']; ?>&ownercontent=1">All</a></li>
								<li><a href="discover?owner=<?= $_GET['owner']; ?>&ownercontent=2">Playlists</a></li>
								<li><a href="discover?owner=<?= $_GET['owner']; ?>&ownercontent=3">Songs</a></li>
							</ul>
						</div>
						<div class="user-option">
							<ul>
								<!-- <li>Opcion 1</li> -->
								<!-- <li>Opcion 2</li> -->
								<li class="<?= $hiddenFollow ? 'hidden' : '';?>" id="unfollow-user" data-unfollow="<?= $current_owner[0]['user_id']; ?>">Unfollow</li> 
								<li class="<?= !$hiddenFollow ? 'hidden' : '';?>" id="follow-user" data-follow="<?= $current_owner[0]['user_id']; ?>">Follow</li>
							</ul>
						</div>
					</div>
					<div class="hidden" id="owner-all">
						<div class="owners-recent-album">
							<?php
							if (!empty($recent_lists)) {
								foreach ($recent_lists as $list) {
									$song = select_from('playlist', [], ['list_id' => $list['lid']], ['limit' => 1]);
									$songData = !empty($song) ? select_from('song', [], ['sid' => $song[0]['song_id']]) : '';
									// var_dump($list);
							?>
									<div class="list" data-list="<?= htmlspecialchars($list['lid'], ENT_QUOTES, 'UTF-8'); ?>">
										<div class="list-cover">
											<?php if (!empty($songData[0]['cover'])) { ?>
												<img src="images/cover/<?= $songData[0]['cover']; ?>">
											<?php } ?>
											<div class="list-options">
												<ul>
													<li>
														<a href="#" class="playlist-mini-menu">...</a>
														<div class="playlist-options">
															<ul>
																<li class="album-like <?= $hiddenLike ? 'hidden' : '';?>" id="album-like" data-albumId="<?= $list['lid']; ?>">Like</li>
																<li class="album-dislike <?= !$hiddenLike ? 'hidden' : '';?>" id="album-dislike" data-albumId="<?= $list['lid']; ?>">Dislike</li>
															</ul>
														</div>
													</li>
													<!-- <li>option</li> -->
												</ul>
											</div>
										</div>
										<div class="list-info">
											<div class="list-name"><?= htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?></div>
											<div class="list-owner" data-ownerId="<?= $list_owner[0]['user_id']; ?>"><?= htmlspecialchars($list_owner[0]['name'].' '.$list_owner[0]['surname'], ENT_QUOTES, 'UTF-8'); ?></div>
										</div>
									</div>
							<?php
								}
							} else {
							?>
								<p class="frame-central">You don't have any list yet</p>
							<?php } ?>
						</div>
						<table class="music-list" cellspacing="0">
						<?php 
						$queueIndex = 0;
						foreach($owner_upload_songs as $song_data) {
						?>
							<tr>
								<td width="5%">
									<div class="songs-cover">
										<img src="<?= empty($song_data['cover']) ? 'images/profile/'.$user_data['image'] : 'images/cover/'.$song_data['cover']; ?>" >
									</div>
								</td width="90%">
								<td class="song-list" data-queue-index="<?= $queueIndex; ?>" data-id="<?= $song_data['sid']; ?>" data-song="<?= $song_data['song_name']; ?>" data-file="<?= $song_data['file_name'];?>">
									<?= ucwords(strtolower($song_data['artist'])).' - '.ucwords(strtolower($song_data['song_name'])); ?>
								</td>
								<td width="5%">
									<button class="actions-btn" id="actions-btn" data-menu="<?= $song_data['sid']; ?>">ooo</button>
									<div class="song-actions" id="song-actions" data-owner="<?= $song_data['user_id']; ?>">
										<ul>
											<li class="addPlaylist" data-songId="<?= $song_data['sid']; ?>">Add playlist</li>
											<!-- <li class="removeFromPlaylis" data-removeId="<?= $song_data['sid']; ?>">Remove</li> -->
											<li class="deleteFile" data-deleteId="<?= $song_data['sid']; ?>" style="display: none;">Delete</li>
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
					<div class="hidden" id="owner-library">
						<?php
						if (!empty($owner_lists)) {
							foreach ($owner_lists as $list) {
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
													<a href="#" class="playlist-mini-menu">...</a>
													<div class="playlist-options">
														<ul>
															<li data-albumId="<?= $list['lid']; ?>">Like</li>
														</ul>
													</div>
												</li>
												<!-- <li>option</li> -->
											</ul>
										</div>
									</div>
									<div class="list-info">
										<div class="list-name"><?= htmlspecialchars($list['list_name'], ENT_QUOTES, 'UTF-8'); ?></div>
										<div class="list-owner" data-ownerId="<?= $list_owner[0]['user_id']; ?>"><?= htmlspecialchars($list_owner[0]['name'].' '.$list_owner[0]['surname'], ENT_QUOTES, 'UTF-8'); ?></div>
									</div>
								</div>
						<?php
							}
						} else {
						?>
							<p class="frame-central">You don't have any list yet</p>
						<?php } ?>
					</div>
					<div class="hidden" id="owner-song">
						<table class="music-list" cellspacing="0">
						<?php 
						$queueIndex = 0;
						foreach($my_upload_songs as $song_data) {
						?>
							<tr>
								<td width="5%">
									<div class="songs-cover">
										<img src="<?= empty($song_data['cover']) ? 'images/profile/'.$user_data['image'] : 'images/cover/'.$song_data['cover']; ?>" >
									</div>
								</td width="90%">
								<td class="song-list" data-queue-index="<?= $queueIndex; ?>" data-id="<?= $song_data['sid']; ?>" data-song="<?= $song_data['song_name']; ?>" data-file="<?= $song_data['file_name'];?>">
									<?= ucwords(strtolower($song_data['artist'])).' - '.ucwords(strtolower($song_data['song_name'])); ?>
								</td>
								<td width="5%">
									<button class="actions-btn" id="actions-btn" data-menu="<?= $song_data['sid']; ?>">ooo</button>
									<div class="song-actions" id="song-actions" data-owner="<?= $song_data['user_id']; ?>">
										<ul>
											<li class="addPlaylist" data-songId="<?= $song_data['sid']; ?>">Add playlist</li>
											<!-- <li class="removeFromPlaylis" data-removeId="<?= $song_data['sid']; ?>">Remove</li> -->
											<li class="deleteFile" data-deleteId="<?= $song_data['sid']; ?>" style="display: none;">Delete</li>
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

    <script>
		<?php 
		$userData = json_encode([
						'userId'	=> !isset($_SESSION['mp_UserId']) ? 'null' : $_SESSION['mp_UserId']
					]); 
		?>
		var currentUser = <?= $userData ?>;
		
    </script>
</body>

</html>