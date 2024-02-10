<?php
    require_once __DIR__ .'/../connections/conexion.php';

    $requestData['user_id'] = $_SESSION['mp_UserId'];
    isset($_GET['list']) ? $requestData['lid'] = $_GET['list'] : !isset($requestData['lid']);
    
    $song_list = select_from('song', [], $requestData);
    
    $results = [];

    foreach($song_list as $song) {
        $results[] = [
            'song_id'        => $song['sid'],
			'user_data'		=> u_all_info('*', ['user_id' => $song['user_id']]),
            'cover'         => $song['cover'],
            'artist'        => $song['artist'],
            'song_name'      => $song['song_name'],
            'file_name'      => $song['file_name']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($results);
?>