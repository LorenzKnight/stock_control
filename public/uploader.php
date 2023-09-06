<!DOCTYPE html>
<html class="no-js" lang="sw">

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Subir Archivos de Música</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="container">
        
        <div class="wrapper-home" style="background-color: #fff;">
            <form action="inc/upload.php" method="post" enctype="multipart/form-data">
                <label for="artist">Artista:</label>
                <input type="text" name="artist" id="artist" required><br>

                <label for="title">Título de la Canción:</label>
                <input type="text" name="title" id="title" required><br>

                <br>
                <input type="radio" id="public" name="public" value="1" checked>
                <label for="public">Public</label><br>
                <input type="radio" id="private" name="public" value="0">
                <label for="private">Private</label><br>
                <br>

                <!-- <input type="file" name="file_name[]" id="file_name" multiple required><br> -->
                <div id="drop-area">
                    <p>Arrastra y suelta tus archivos aquí o haz clic para seleccionarlos.</p>
                    <input type="file" name="file_name[]" id="file_name" multiple required>
                </div>

                <input type="submit" value="Subir Archivos">
            </form>
        </div>
    </div>
</body>
</html>
<script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file_name');

    // Evitar el comportamiento predeterminado de arrastrar y soltar
    dropArea.addEventListener('dragenter', (e) => {
        e.preventDefault();
        dropArea.classList.add('active');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('active');
    });

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('active');

        const files = e.dataTransfer.files;
        fileInput.files = files;

        // También puedes realizar alguna acción aquí con los archivos, si es necesario
    });

    // Opcional: Escuchar el evento de cambio en el input de archivo
    fileInput.addEventListener('change', () => {
        // Realizar alguna acción aquí si es necesario
    });
</script>