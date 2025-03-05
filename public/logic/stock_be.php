<?php
require_once __DIR__ .'/../connections/conexion.php';

$requestData['user_id'] = !isset($_SESSION['sc_UserId']) ? null : $_SESSION['sc_UserId'];

$user_data = select_from("users", ['user_id', 'name', 'surname', 'image'], $requestData, ["fetch_first" => true]);

$user_data_array = json_decode($user_data, true);

if ($user_data_array["success"] && !empty($user_data_array["data"])) {
    $user = $user_data_array["data"];
} else {
    echo "Error: " . $user_data_array["message"];
}

// cdebug($user);
?>