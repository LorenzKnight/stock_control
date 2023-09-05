<!DOCTYPE html>
<html>
<head>
    <title>Subir Archivos de Música</title>
</head>
<body>
    <form action="inc/upload.php" method="post" enctype="multipart/form-data">
        <label for="artist">Artista:</label>
        <input type="text" name="artist" id="artist" required><br>

        <label for="title">Título de la Canción:</label>
        <input type="text" name="title" id="title" required><br>

        <input type="file" name="file_name[]" id="file_name" multiple required><br>

        <input type="submit" value="Subir Archivos">
    </form>
</body>
</html>