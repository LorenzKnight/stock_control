<?php
require_once __DIR__ .'/../connections/conexion.php';
// require_once __DIR__ .'/../inc/security.php';

// The login logic //
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formsignin")) {
    if (!isset($_SESSION)) {
        session_start();
    }

    $loginFormAction = $_SERVER['PHP_SELF'];
    if (isset($_GET['accesscheck'])) {
        $_SESSION['PrevUrl'] = $_GET['accesscheck'];
    }

    $email                      = $_POST['email'];
    $password                   = $_POST['password'];
    $MM_redirectLoginSuccess    = "discover.php";
    $MM_redirectLoginFailed     = "discover.php?error=1";

    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $sql = pg_query($query);
    $totalRow_query = pg_num_rows($sql);

    if($totalRow_query > 0)
    {
        $row_query = pg_fetch_assoc($sql);

        $_SESSION['mp_UserId'] = $row_query['user_id'];
        $_SESSION['mp_Mail'] = $row_query['email'];
        $_SESSION['mp_Nivel'] = $row_query['rank'];

        if (isset($_SESSION['PrevUrl']) && false) {
            $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
        }
        header("Location: " . $MM_redirectLoginSuccess );
    }
    else
    {
        header("Location: " . $MM_redirectLoginFailed );
    }
}
// End to the login logic //

if (isset($_GET['list'])) {
	$_SESSION['list'] = $_GET['list'];
}

$requestData['user_id'] = !isset($_SESSION['mp_UserId']) ? null : $_SESSION['mp_UserId'];
$user_data = u_all_info('*', $requestData);

$requestData = [];
// isset($_SESSION['mp_UserId']) ? $requestData['user_id'] = $_SESSION['mp_UserId'] : !isset($requestData['user_id']);
isset($_GET['list']) ? $requestData['lid'] = $_GET['list'] : !isset($requestData['lid']);
$my_lists = empty($requestData) ? null : select_from('listings', [], $requestData);

$requestData = [];
$requestData['user_id'] = $my_lists[0]['user_id'];
$owner_by_list_id = select_from('users', [], $requestData, ['fetch_first' => true]);
// var_dump($owner_by_list_id);

$requestData = [];
isset($_GET['list']) ? $requestData['list_id'] = $_GET['list'] : !isset($requestData['list_id']);
$my_playlist = select_from('playlist', [], $requestData);

$requestData = [];
isset($_GET['owner']) ? $requestData['user_id'] = $_GET['owner'] : !isset($requestData['user_id']);
$my_upload_songs = select_from('song', [], $requestData);
$current_owner = select_from('users', [], $requestData);

$recent_lists = select_from('listings', [], $requestData, ['limit' => 5]);
$owner_upload_songs = select_from('song', [], $requestData, ['limit' => 10]);

$owner_lists = empty($requestData) ? null : select_from('listings', [], $requestData);
$list_owner = select_from('users', [], $requestData);

$requestData = [];
$requestData['user_id'] = !isset($_SESSION['mp_UserId']) ? null : $_SESSION['mp_UserId'];
isset($_GET['owner']) ? $requestData['is_following'] = $_GET['owner'] : !isset($requestData['is_following']);
$iFollow = select_from('followers', [], $requestData);

$requestData = [];
$requestData['user_id'] = !isset($_SESSION['mp_UserId']) ? null : $_SESSION['mp_UserId'];
$favoriteLists = select_from('favorite_lists', [], $requestData);


if (is_array($iFollow) && count($iFollow) > 0) {
	$hiddenFollow = ($iFollow[0]['user_id'] == $current_owner[0]['user_id'] && $iFollow[0]['is_following'] == $_GET['owner']) ? false : true;
} else {
	$hiddenFollow = true;
}

if (is_array($favoriteLists) && count($favoriteLists) > 0) {
	$hiddenLike = (isset($_SESSION['mp_UserId']) && $favoriteLists[0]['user_id'] == $_SESSION['mp_UserId']) ? false : true;
} else {
	$hiddenLike = true;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formsearch")) {
    $requestSearch['search'] = pg_escape_string($_POST['searching']);

	$pick_user = pick_user('*', $requestSearch);

	$userResurt = [];
	foreach($pick_user as $user) {
		$userResurt[] = [
			'userId'		=> $user['user_id'],
			'name'			=> $user['name'].' '.$user['surname'],
			// 'userImage'		=> $user['image']
		];
	}

    $my_songs = song_data('*', $requestSearch);
    
    $results = [];
    foreach($my_songs as $song) {
        $results[] = [
            'songId'        => $song['songId'],
			'userPic'		=> u_all_info('image', ['user_id' => $song['userId']]), // traer foto de perfil
            'cover'         => $song['cover'],
            'artist'        => $song['artist'],
            'songName'      => $song['songName'],
            'fileName'      => $song['fileName']
        ];
    }

	$combinedResults = array_merge($userResurt, $results);
	
    header('Content-Type: application/json');
    echo json_encode($combinedResults);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "addToList")) {
	$queryData = [
        'user_id'   	=> $_SESSION['mp_UserId'],
        'list_id'   	=> $_POST['listId'],
        'song_id'   	=> $_POST['songId'],
        'list_date'		=> date("Y-m-d H:i:s")
    ];

    $functionSelect = select_from('playlist', ['song_id', 'list_id'], ['song_id' => $_POST['songId'], 'list_id' => $_POST['listId']]);

    if ($functionSelect) {
        $updatePlaylist = update_table('playlist', $queryData, ['song_id' => $_POST['songId'], 'list_id' => $_POST['listId']]);
		
        $success = $updatePlaylist !== false;
        $message = $success ? "The song was updated successfully." : "Error updating the song.";
		$id = null;
    } else {
        $insertResult = insert_into('playlist', $queryData, ['id' => 'pid']);
        $insertResultArray = json_decode($insertResult, true);

        $success = $insertResultArray["success"] ? true : false;
        $message = $success ? "The song was inserted successfully." : "Error inserted the song.";
		$id = $insertResultArray["id"];
    }

    echo json_encode(["success" => $success, "id" => $id, "message" => $message]);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "createAndAdd")) {
	$queryDataPlaylist = [
		'user_id'		=> $_SESSION['mp_UserId'],
		'list_name'		=> $_POST['inputPlaylist'],
		'public'		=> 1,
		'list_date'		=> date("Y-m-d H:i:s")
    ];

	$newPlaylist = insert_into('listings', $queryDataPlaylist, ['id' => 'lid']);
	$insertResultArray = json_decode($newPlaylist, true);

	$queryData = [
		'user_id'		=> $_SESSION['mp_UserId'],
		'list_id'		=> $insertResultArray['id'],
		'song_id'		=> $_POST['songId'],
		'list_date'		=> date("Y-m-d H:i:s")
	];

	$insertResult = insert_into('playlist', $queryData, ['id' => 'pid']);
    $insertResultArray = json_decode($insertResult, true);

	$success = $insertResultArray["success"] ? true : false;
	$message = $success ? 'The song was inserted successfully in ' .$_POST['inputPlaylist']. ' Playlist.' : 'Error inserted the song.';
	$id = $insertResultArray["id"];

	echo json_encode(["success" => $success, "id" => $id, "message" => $message]);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "remFromList")) {
	$queryData = [
		'user_id'		=> $_SESSION['mp_UserId'],
		'pid'			=> $_POST['songId']
	];

	$deleteResult = delete_from('playlist', $queryData);
	$deleteResultArray = json_decode($deleteResult, true);

	$success = $deleteResultArray["success"] ? true : false;
	$message = $success ? 'The song was removed successfully from Playlist.' : 'Error removed the song.';

	echo json_encode(["success" => $success, "message" => $message]);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "follow")) {
	$queryData = [
		'user_id'		=> $_SESSION['mp_UserId'],
		'is_following'	=> $_POST['userIdToFollow'],
		'follow_date'	=> date("Y-m-d H:i:s")
	];

	$insertResult = insert_into('followers', $queryData, ['id' => 'fid']);
    $insertResultArray = json_decode($insertResult, true);

	$success = $insertResultArray["success"] ? true : false;
	$message = $success ? 'Now you follow this user' : 'Error follow this user.';
	$id = $insertResultArray["id"];

	echo json_encode(["success" => $success, "id" => $id, "message" => $message]);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "unfollow")) {
	$queryData = [
		'user_id'		=> $_SESSION['mp_UserId'],
		'is_following'	=> $_POST['userIdToUnfollow']
	];

	$deleteResult = delete_from('followers', $queryData);
	$deleteResultArray = json_decode($deleteResult, true);

	$success = $deleteResultArray["success"] ? true : false;
	$message = $success ? 'You have stopped following this person.' : 'Error Unfollow this user.';

	echo json_encode(["success" => $success, "message" => $message]);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "addToFav")) {
	$requestData = [
		'user_id'		=> $_SESSION['mp_UserId'],
		'list_id'		=> $_POST['albumId']
	];

	$favoriteLists = select_from('favorite_lists', [], $requestData);

	if(is_array($favoriteLists) && count($favoriteLists) > 0) {
		echo json_encode(["success" => false, "message" => "This album is already in your library."]);
	} else {
		$queryData = [
			'user_id'		=> $_SESSION['mp_UserId'],
			'list_id'		=> $_POST['albumId'],
			'list_date'		=> date("Y-m-d H:i:s")
		];
// var_dump($queryData);
		$insertResult = insert_into('favorite_lists', $queryData, ['id' => 'flid']);
		$insertResultArray = json_decode($insertResult, true);

		$success = $insertResultArray["success"] ? true : false;
		$message = $success ? 'The list is added to your library.' : 'Error adding list.';
		$id = $insertResultArray["id"];

		echo json_encode(["success" => $success, "id" => $id, "message" => $message]);
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "removeFromFav")) {
	$queryData = [
		'user_id'		=> $_SESSION['mp_UserId'],
		'list_id'		=> $_POST['albumId']
	];

	$deleteResult = delete_from('favorite_lists', $queryData);
	$deleteResultArray = json_decode($deleteResult, true);

	$success = $deleteResultArray["success"] ? true : false;
	$message = $success ? 'The list was removed successfully.' : 'Error removing this list.';

	if ($success) {
        $updatedContent = getUpdatedFavListContent();
        echo json_encode(["success" => $success, "message" => $message, "html" => $updatedContent]);
    } else {
        echo json_encode(["success" => $success, "message" => $message]);
    }
}
?>