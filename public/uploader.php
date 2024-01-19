<?php require_once('logic/discover_be.php');?>

<!DOCTYPE html>
<html class="no-js" lang="sw">

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Subir Archivos de Música</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styles.css">
    <script defer src="js/uploader.js"></script>
</head>

<body>
    <?php include("components/header.php"); ?>

    <div class="container">
        <div class="wrapper-home" style="background-color: #fff;">
            <form action="inc/upload.php" method="post" enctype="multipart/form-data">
                <br>
                <div class="drop-area" id="drop-area">
                    <p>Arrastra y suelta tus archivos aquí o haz clic para seleccionarlos.</p>
                    <div><input type="file" name="file_name[]" id="file_name" multiple required></div>
                    <!-- <div class="file-input-container">
                        <label for="fileInput" class="custom-file-label">Seleccionar archivos</label>
                        <input type="file" name="file_name[]" id="fileInput" multiple required>
                    </div> -->
                </div>
                <br>
                <table class="table-form" cellspacing="0">
                    <tr>
                        <td align="right">
                            <label for="artist">Artista:</label><br>
                            <input type="text" name="artist" id="artist" required>
                        </td>
                        <td align="left">
                            <label for="title">Título:</label><br>
                            <input type="text" name="title" id="title" required>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            <input type="radio" id="public" name="public" value="1" checked>
                            <label for="public">Public</label>
                        </td>
                        <td align="left">
                            <input type="radio" id="private" name="public" value="0">
                            <label for="private">Private</label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="submit" value="Subir Archivos">
                        </td>
                    </tr>
                </table>

                
            </form>
        </div>
    </div>

    <div class="status-message" id="status-message"></div>
</body>
</html>