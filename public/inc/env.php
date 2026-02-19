<?php
$envPath = __DIR__ . '/../../.env';

if (!file_exists($envPath)) {
    throw new Exception('.env file not found');
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {

    $line = trim($line);

    if ($line === '' || strpos($line, '#') === 0) {
        continue;
    }

    if (strpos($line, '=') === false) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);

    $key = trim($key);
    $value = trim($value);

    if (!array_key_exists($key, $_ENV)) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}