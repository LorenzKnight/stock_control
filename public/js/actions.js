document.getElementById('formsignin').addEventListener('submit', async function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    try {
        let response = await fetch('api/submit_form.php', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        });

        let data = await response.json();
        console.log('Server response:', data);

        if (data.success) {
            // document.getElementById('status-message').innerText = data.message;
            // window.location.href = 'success.php';
            let banner = document.getElementById('status-message');
            banner.innerText = data.message;
            banner.style.display = 'block';
            banner.style.opacity = '1';

            setTimeout(() => {
                banner.style.opacity = '0';
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
            }, 4000);
        } else {
            document.getElementById('status-message').innerText = "Error: " + data.message;
            document.getElementById('status-message').style.display = 'block';
        }
    } catch (error) {
        console.error('Error en la solicitud:', error);
        document.getElementById('status-message').innerText = "Error processing request.";
        document.getElementById('status-message').style.display = 'block';
    }
});