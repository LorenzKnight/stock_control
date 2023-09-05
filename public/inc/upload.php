<?php
require_once __DIR__ .'/../connections/conexion.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("A POST request was not received.");
}

$uploadDir = "../audio/";
if (!isset($_FILES["file_name"])) {
    die("No files received for upload.");
}

$files = $_FILES["file_name"];
$allowedExtensions = ["mp3", "wma", "png"];
$maxFileSize = 5242880; // 5 MB (este es solo un ejemplo, puedes ajustarlo según tus necesidades)


$userId = $_SESSION['mp_UserId'];
$listId = null;
$artist = isset($_POST["artist"]) ? $_POST["artist"] : "Unknown";
$title = isset($_POST["title"]) ? $_POST["title"] : "No title";
$gender = 'test';
$report = 0;
$public = 1;
$date = date("Y-m-d H:i:s");

for ($i = 0; $i < count($files["name"]); $i++) {
    $fileExtension = pathinfo($files["name"][$i], PATHINFO_EXTENSION);
    $fileSize = $files["size"][$i];

    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        echo $fileExtension . " is an invalid format: " . $files["name"][$i] . "<br>";
        exit();
    }

    if ($fileSize > $maxFileSize) {
        echo "File size exceeds the maximum allowed limit: " . $files["name"][$i] . "<br>";
        continue;
    }

    if ($files["error"][$i] != UPLOAD_ERR_OK) {
        echo "Error loading file: " . $files["name"][$i] . "<br>";
        continue;
    }

    $musicName = $artist . " - " . $title . ".mp3";
    $musicFile = $uploadDir . basename($musicName);

    if (!move_uploaded_file($files["tmp_name"][$i], $musicFile)) {
        echo "Error moving file: " . $musicName . "<br>";
        continue;
    }

    $musicName = pg_escape_string($musicName);
    $musicFileName = pg_escape_string($files["name"][$i]);

    $queryColumnNames = [
        "user_id",
        "artist",
        "song_name",
        "file_name",
        "gender",
        "report",
        "public",
        "song_date"
    ];

    $queryColumnValues = [
        $userId,
        $artist,
        $title,
        $musicName,
        $gender,
        $report,
        $public,
        $date
    ];

    if (insert_into("song", $queryColumnNames, $queryColumnValues)) {
        echo "File uploaded successfully: " . $musicName . "<br>";
    } else {
        echo "Failed to upload file: " . $musicName . "<br>";
    }
}
?>
