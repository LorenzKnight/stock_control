<?php
require_once __DIR__ .'/../connections/conexion.php';

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


!isset($_SESSION['mp_UserId']) ? $requestData['user_id'] = null : $requestData['user_id'] = $_SESSION['mp_UserId'];
isset($_GET['list']) ? $requestData['lid'] = $_GET['list'] : !isset($requestData['lid']);

$user_data = u_all_info('*', $requestData);
$my_lists = listings('*', $requestData);
$my_playlist = playlist_details('*', $requestData);


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formsearch")) {
    $requestSearch['search'] = pg_escape_string($_POST['searching']);
    
    $my_songs = song_data('*', $requestSearch);
    
    $results = [];

    foreach($my_songs as $song) {
        $results[] = [
            'songId'        => $song['songId'],
			'userData'		=> u_all_info('image', ['user_id' => $song['userId']]), // traer foto de perfil 
            'cover'         => $song['cover'],
            'artist'        => $song['artist'],
            'songName'      => $song['songName'],
            'fileName'      => $song['fileName']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($results);
}

$requestData = [];
$requestData['user_id'] = null ? $_SESSION['mp_UserId'] : null;
$all_my_lists = listings('*', $requestData);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "addToList")) {
	$queryData = [
        'user_id'   => $_SESSION['mp_UserId'],
        'list_id'   => $_POST['listId'],
        'song_id'   => $_POST['songId'],
        'list_date' => date("Y-m-d H:i:s")
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
?>