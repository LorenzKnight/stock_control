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
$maxFileSize = 90242880; // 90 MB (este es solo un ejemplo, puedes ajustarlo según tus necesidades)


$userId = $_SESSION['mp_UserId'];
$artist = isset($_POST["artist"]) ? strtolower($_POST["artist"]) : "Unknown";
$title = isset($_POST["title"]) ? strtolower($_POST["title"]) : "No title";
$gender = isset($_POST["gender"]) ? strtolower($_POST["gender"]) : "Unknown";
$report = 0;
$public = $_POST["public"];
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

    $queryData = [
        "user_id"       => $userId,
        "artist"        => $artist,
        "song_name"     => $title,
        "file_name"     => $musicName,
        "gender"        => $gender,
        "report"        => $report,
        "public"        => $public,
        "song_date"     => $date
    ];

    if (insert_into("song", $queryData)) {
        echo json_encode(["success" => true, "message" => "File uploaded successfully: " . $musicName]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload file: " . $musicName]);
    }
    exit();
}
?>
