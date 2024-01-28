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
    $requestSearch['artist'] = pg_escape_string($_POST['searching']);
    
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
    $tableName          = 'playlist';
    $queryColumnNames   = ['user_id', 'list_id', 'song_id', 'list_date'];
    $queryColumnValues  = [$_SESSION['mp_UserId'], $_POST['listId'], $_POST['songId'], date("Y-m-d H:i:s")];

    $inserPlaylist = insert_into($tableName, $queryColumnNames, $queryColumnValues);
}
?>