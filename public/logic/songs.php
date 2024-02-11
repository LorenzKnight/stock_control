<?php
    require_once __DIR__ .'/../connections/conexion.php';

    !isset($_SESSION['mp_UserId']) ? $requestData['user_id'] = null : $requestData['user_id'] = $_SESSION['mp_UserId'];
    $listings = select_from('listings', [], $requestData);
    
    $results = [];

    foreach($listings as $list) {
        $results[] = [
            'lid'			=> $list['lid'],
            'user_id'		=> $list['user_id'],
            'listName'		=> $list['list_name'],
			'public'		=> $list['public'],
			'list_date'		=> $list['list_date'],
			'userData'     => u_all_info('*', ['user_id' => $list['user_id']]),
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($results);
?>