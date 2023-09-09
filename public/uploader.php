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
                <br>
                <div class="drop-area" id="drop-area">
                    <p>Arrastra y suelta tus archivos aquí o haz clic para seleccionarlos.</p>
                    <input type="file" name="file_name[]" id="file_name" multiple required>
                    <!-- <div class="file-input-container">
                        <label for="fileInput" class="custom-file-label">Seleccionar archivos</label>
                        <input type="file" name="file_name[]" id="fileInput" multiple required>
                    </div> -->
                </div>
                <br>

                <div class="">
                    <label for="artist">Artista:</label>
                    <input type="text" name="artist" id="artist" required>

                    <label for="title">Título:</label>
                    <input type="text" name="title" id="title" required><br>
                </div>
                <br>

                <div class="">
                    <input type="radio" id="public" name="public" value="1" checked>
                    <label for="public">Public</label>
                    <input type="radio" id="private" name="public" value="0">
                    <label for="private">Private</label>
                </div>
                <br>

                <input type="submit" value="Subir Archivos">
            </form>
        </div>
    </div>

    <div id="status-message"></div>
</body>
</html>
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch('inc/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const statusMessage = document.getElementById('status-message');
            statusMessage.innerText = data.message;

            statusMessage.style.display = 'block';

            setTimeout(() => {
                statusMessage.style.opacity = '0';
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                    statusMessage.style.opacity = '0.9'; // Restablece la opacidad para la próxima vez
                }, 1000); // 0.4 segundos (400ms) es la duración de la transición que configuramos en CSS
            }, 2000);

            document.getElementById('status-message').innerText = data.message;
            console.log(data);
            
            // Verifica si el archivo se subió con éxito y limpia el formulario
            if (data.success) {
                e.target.reset();
                document.getElementById('drop-area').classList.remove('active');  // Opcional: si deseas restablecer la apariencia de la zona de arrastre
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('file_name').value = '';
            document.getElementById('status-message').innerText = "Error al cargar los archivos.";
        });
    });

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