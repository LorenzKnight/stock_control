<?php
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once __DIR__ .'/../connections/conexion.php';
require_once __DIR__ .'/../logic/global_arrays.php';
require_once __DIR__ .'/../logic/qr_builder.php';

$possiblePaths = [
    __DIR__ . '/../../vendor/autoload.php',     // estructura normal (root/vendor)
    __DIR__ . '/../../../vendor/autoload.php',  // si se ejecuta desde subcarpetas adicionales
    __DIR__ . '/vendor/autoload.php'            // fallback directo
];

$autoloadFound = false;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    throw new Exception("❌ vendor/autoload.php not found in any known path.");
}

require_once __DIR__ .'/../inc/jwt_config.php';

if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
    error_log("✅ stock_be.php loaded successfully with autoload.");
}

define('VAPID_PUBLIC_KEY', 'BKqWcNgpm_rAhyuhrXKvDl7LksLNuhA-KQhanWJlBsOC34-ScYjy8p0xBb3h6YMOyT9kenbv_OTnEg5yWG-Kx5k');
define('VAPID_PRIVATE_KEY', 'gF251UD2mJU2ITv0qhUbTK_kDcaAdA4HEUYstmXggnE');
define('VAPID_SUBJECT', 'mailto:admin@allstockcontrol.com');