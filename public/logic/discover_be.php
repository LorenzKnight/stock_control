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

$current_user = u_all_info('*', $requestData);
$my_lists = listings('*', $requestData);
$my_songs = playlist_details('*', $requestData);


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formsearch")) {
    $requestSearch['search'] = pg_escape_string($_POST['searching']);
    
    $my_songs = song_data('*', $requestSearch);
    
    $results = [];

    foreach($my_songs as $song) {
        $results[] = [
            'songId'        => $song['songId'],
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

	$fuctionSelect = select_from('playlist', ['song_id'], ['song_id' => $_POST['songId']]);

	$inserPlaylist = false;
    $updatePlaylist = false;

	if ($fuctionSelect) {
		$updatePlaylist = update_table('playlist', $queryData, ['song_id' => $_POST['songId']]);
	} else {
		$inserPlaylist = insert_into('playlist', $queryData, ['echo_query']);
	}

	if ($inserPlaylist || $updatePlaylist) {
		echo json_encode(["success" => true, "message" => "The song was added to the list successfully."]);
	}
}
?>