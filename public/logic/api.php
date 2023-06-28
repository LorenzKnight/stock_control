<?php
    require_once __DIR__ .'/../connections/conexion.php';

    $requestData['user_id'] = $_SESSION['mp_UserId'];
    isset($_GET['list']) ? $requestData['lid'] = $_GET['list'] : !isset($requestData['lid']);
    
    $song_list = song_list('*', $requestData);
    
    // Establece las cabeceras adecuadas para indicar que se trata de una respuesta JSON
    header('Content-Type: application/json');

    // Imprime la cadena JSON
    echo json_encode($song_list);
?>