document.addEventListener("DOMContentLoaded", () => {
	var profileTrigger = document.getElementById('profileTrigger');
	
	if (profileTrigger) {
		profileTrigger.addEventListener('click', function() {
			var profileDropdown = document.getElementById('profileDropdown');
			if (profileDropdown) {
				profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
			}
		});

		document.addEventListener('click', function(event) {
			var isClickInsideMenu = profileTrigger.contains(event.target) || profileDropdown.contains(event.target);
			if (!isClickInsideMenu) {
				profileDropdown.style.display = 'none';
			}
		});
	}


	let formsignin = document.getElementById('formsignin');
    if (formsignin) {
        formsignin.addEventListener('submit', async function (e) {
            e.preventDefault();

			let formData = new FormData(this);

			try {
				let response = await fetch('api/signup.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();
				console.log('Server response:', data);

				if (data.success) {
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
    }

	let formlogin = document.getElementById('formlogin');
	if (formlogin) {
		formlogin.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			try {
				let response = await fetch('api/login.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();
				console.log('Server response:', data);

				if (data.success) {
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
	}

	let logoutButton = document.getElementById('logout-button');
	if (logoutButton) {
		logoutButton.addEventListener('click', async function (e) {
			e.preventDefault();

			try {
				let response = await fetch('api/logout.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' }
				});

				let data = await response.json();
				console.log('Server response:', data);

				if (data.success) {
					let banner = document.getElementById('status-message');
					if (banner) {
						banner.innerText = "Su sesión está siendo cerrada...";
						banner.style.display = 'block';
						banner.style.opacity = '1';

						setTimeout(() => {
							banner.style.opacity = '0';
							setTimeout(() => {
								window.location.href = data.redirect_url;
							}, 1000);
						}, 4000);
					} else {
						// Si no hay banner, usar un alert
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 4000);
					}
				} else {
					alert("Error al cerrar sesión: " + data.message);
				}
			} catch (error) {
				console.error('Error en la solicitud:', error);
				alert("Error al procesar la solicitud.");
			}
		});
	}

	let toggleLink = document.getElementById("toggle-link");
    let formLogin = document.getElementById("formular-Login");
    let formSignup = document.getElementById("formular-Signup");

    if (toggleLink) {
        toggleLink.addEventListener("click", function (e) {
            e.preventDefault();

            if (formLogin && formSignup) {
                formLogin.style.display = "none";
                formSignup.style.display = "block";
            }
        });
    }

});