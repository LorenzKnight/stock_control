<?php
require_once __DIR__ .'/../connections/conexion.php';
require_once __DIR__ .'/../logic/global_arrays.php';
require_once __DIR__ .'/../logic/qr_builder.php';

if (is_file(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
} elseif (is_file(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    throw new Exception("vendor/autoload.php not found.");
}

require_once __DIR__ .'/../inc/jwt_config.php';
?>