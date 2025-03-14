document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch('api/upload.php', {
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