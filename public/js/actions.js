document.addEventListener("DOMContentLoaded", async function () {
	// 📌 Mostrar y ocultar menú de perfil
	var profileTrigger = document.getElementById('profileTrigger');
	var profileDropdown = document.getElementById('profileDropdown');
	
	if (profileTrigger && profileDropdown) {
		profileTrigger.addEventListener('click', function() {
			profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
		});

		document.addEventListener('click', function(event) {
			if (!profileTrigger.contains(event.target) && !profileDropdown.contains(event.target)) {
				profileDropdown.style.display = 'none';
			}
		});
	}

	// 📌 Manejo del formulario de registro
	let formsignin = document.getElementById('formsignup');
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

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					banner.style.display = 'block';
					banner.style.opacity = '1';

					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 1000);
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					banner.style.display = 'block';
				}
			} catch (error) {
				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = data.img_gif;
				banner.style.display = 'block';
			}
		});
	}

	// 📌 Manejo del formulario de login
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

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					banner.style.display = 'block';
					banner.style.opacity = '1';

					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 1000);
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					banner.style.display = 'block';
				}
			} catch (error) {
				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = data.img_gif;
				banner.style.display = 'block';
			}
		});
	}

	// 📌 Manejo del botón de logout
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

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					banner.style.display = 'block';
					banner.style.opacity = '1';

					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 1000);
					}, 3000);
				} else {
					alert("Error al cerrar sesión: " + data.message);
				}
			} catch (error) {
				console.error('Error en la solicitud:', error);
				alert("Error al procesar la solicitud.");
			}
		});
	}

	// 📌 Alternar entre login y signup
	let toggleLink = document.getElementById("toggle-link");
	let closeLink = document.getElementById("close-link");
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

	if (closeLink) {
        closeLink.addEventListener("click", function (e) {
            e.preventDefault();
            if (formLogin && formSignup) {
                formLogin.style.display = "block";
                formSignup.style.display = "none";
            }
        });
    }

	// 📌 Manejo del datos de usuario
    try {
        let response = await fetch('api/get_my_info.php', {
            method: 'GET',
            headers: { Accept: "application/json" }
        });

        let data = await response.json();
        console.log("Server response:", data);

		let hiUser = document.getElementById("hi-user");
		let myData = document.getElementById("my-data");
		let subsc = document.getElementById("subsc");
		let totalSpot = document.getElementById("total-spot");

		if (data.success && data.data) {
			let user = data.data;

			if (hiUser) hiUser.innerHTML = `Hi, ${user.name || 'User'}!`;

			if (myData) {
				myData.innerHTML =
					`<p><strong>ID:</strong> ${user.user_id?.trim() || "-"}</p>` +
					`<p><strong>Phone:</strong> ${user.phone?.trim() || "No Phone Number"}</p>` +
					`<p><strong>Email:</strong> ${user.email?.trim() || "No Email"}</p>`;
				;
			}

			if (subsc) {
				subsc.innerHTML = user.members && String(user.members).trim() !== "" ? `<p>${user.members}</p>` : "0";
			}
	
			if (totalSpot) {
				totalSpot.innerHTML = user.members && String(user.members).trim() !== "" ? user.members : "0";
			}

			// subsc.innerHTML = user.members && user.members.trim() !== "" ? `<p>${user.members}</p>` : "0";

			// totalSpot.innerHTML = user.members && user.members.trim() !== "" ? user.members : "0";
        } else {
            if (myData) {
				myData.innerHTML = `<p>No user data found.</p>`;
			}
        }
    } catch (error) {
    	console.error("Error fetching data:", error);
		const myData = document.getElementById("my-data");
		if (myData) {
			myData.innerHTML = `<p>Error loading user data.</p>`;
		}
    }

	// 📌 Manejo de lo datos de Empresa
    try {
        let response = await fetch('api/get_company_info.php', {
            method: 'GET',
            headers: { Accept: "application/json" }
        });

        let data = await response.json();
        console.log("Server response:", data);

		let myCompany = document.getElementById("company-data");

		if (data.success && data.data) {
			let company = data.data;

			myCompany.innerHTML =
				`<p><strong>Org No.:</strong> ` + (company.organization_no && company.organization_no.trim() !== "" ? `${company.organization_no}</p>` : "-</p>") +
				`<p><strong>Name:</strong> ` + (company.company_name && company.company_name.trim() !== "" ? `${company.company_name}</p>` : "-</p>")
        
			if (company.company_logo && company.company_logo.trim() !== "") {
				myCompany.innerHTML += `<p><img src="images/company-logos/${company.company_logo}" alt="Company Logo" style="max-width: 40px; margin: 0 auto; border-radius: 50%; border: 1px solid #000;"></p>`;
			}
		} else {
			myCompany.innerHTML = `<p>No company information available.</p>`;
		}
    } catch (error) {
        console.error("Error fetching data:", error);
        document.getElementById("company-data").innerHTML = `<tr><td colspan="6">Error al cargar los datos de empresa.</td></tr>`;
    }

	// 📌 Manejo de lista de usuarios hijos
	try {
        let response = await fetch('api/get_users.php', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        let data = await response.json();
        console.log('Server response:', data);
		
		let spot = document.getElementById("spot");
        let userContainer = document.getElementById('child-user-table');

		spot.innerHTML = data.count !== "" ? data.count : "0";

        if (data.success && data.count > 0) {
            userContainer.innerHTML = '';

            data.users.forEach(user => {
                let card = document.createElement("div");
				card.classList.add("members-card");

				let profileImage = user.image && user.image.trim() !== "" 
                ? `images/profile/${user.image}` 
                : "images/profile/NonProfilePic.png";

				card.innerHTML = `
					<div class="mini-banner">
						<div class="mini-profile">
							<img src="${profileImage}" alt="Profile Picture">
						</div>
					</div>
					<div class="card-info">
						<h3>${user.name} ${user.surname}</h3>
						<p><strong>Email:</strong> ${user.email}</p>
						<p><strong>Phone:</strong> ${user.phone ? user.phone : "No Phone Number"}</p>
					</div>
				`;

				userContainer.appendChild(card);
            });
        } else {
			userContainer.innerHTML = "<p>No members found.</p>";
        }
    } catch (error) {
        console.error('Error fetching data:', error);
		document.getElementById("child-user-table").innerHTML = `<p>Error loading user data.</p>`;
    }


	// 📌 cerrar al hacer clic fuera del formulario
	function handlePopupClose(popupId, contentSelector, otherPopups = []) {
		const popup = document.getElementById(popupId);
		const popupContent = popup.querySelector(contentSelector);
	
		if (popup) {
			popup.addEventListener("click", function (e) {
				if (!popupContent.contains(e.target)) {
					popup.style.display = "none";
	
					otherPopups.forEach(id => {
						const other = document.getElementById(id);
						if (other) other.style.display = "none";
					});
				}
			});
		}
	}
	handlePopupClose("edit-my_info-form", ".formular-frame", ["edit-my_info-form"]);
	handlePopupClose("subsc-form", ".formular-frame", ["subsc-form"]);
	handlePopupClose("edit-company-form", ".formular-frame", ["edit-company-form"]);
	handlePopupClose("add-members-form", ".formular-frame", ["add-members-form"]);

	// 📌 Boton para cerrar formulario
	let cancelButtons = document.querySelectorAll('.cancel-btn');
	cancelButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			let popup = button.closest('.bg-popup');
			if (popup) {
				popup.style.display = 'none';
			}
		});
	});

	// 📌 recoje el valor del select del formulario subscripcion
	let selectPack = document.getElementById('packs');
	let estimated = document.getElementById('estimated');
	let estimatedInput = document.getElementById('estimated_cost');
	const pricePerMember = 100;

	function updateEstimatedCost() {
		if (selectPack && estimated && estimatedInput) {
			let selectedValue = parseInt(selectPack.value);
			let totalCost = selectedValue > 0 ? selectedValue * pricePerMember : 0;
			estimated.innerHTML = `Estimated cost: <strong>$ ${totalCost}</strong>`;
			estimatedInput.value = totalCost;
			
		}
	}

	// 📌 script para recojer el paquete actual
	async function loadCurrentPackage() {
		try {
			let response = await fetch('api/get_current_package.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
	
			let data = await response.json();
			console.log("Current package data:", data);
	
			if (data.success && data.current_pack && selectPack) {
				let currentPack = parseInt(data.current_pack);
				let options = selectPack.querySelectorAll("option");
				options.forEach(option => {
					let value = parseInt(option.value);
					if (!isNaN(value)) {
						option.style.display = value < currentPack ? 'none' : 'block';
					}
				});

				selectPack.value = currentPack;
				updateEstimatedCost();
			}
		} catch (error) {
			console.error("Error loading current package:", error);
		}
	}

	function scrollToTopIfNeeded() {
		if (window.scrollY > 0) {
			window.scrollTo({
				top: 0,
				behavior: 'smooth'
			});
		}
	}

	// Drag & Drop + click
	function initDragAndDrop(dropAreaId, inputFileId, previewImgId = null) {
		const dropArea = document.getElementById(dropAreaId);
		const fileInput = document.getElementById(inputFileId);
		const previewImage = previewImgId ? document.getElementById(previewImgId) : null;
	
		if (!dropArea || !fileInput) return;
	
		// Al hacer clic en el área se dispara el input
		dropArea.addEventListener('click', () => fileInput.click());
	
		// Drag events
		dropArea.addEventListener('dragenter', (e) => {
			e.preventDefault();
			dropArea.classList.add('active');
		});
		dropArea.addEventListener('dragleave', () => dropArea.classList.remove('active'));
		dropArea.addEventListener('dragover', (e) => e.preventDefault());
	
		// Drop file
		dropArea.addEventListener('drop', (e) => {
			e.preventDefault();
			dropArea.classList.remove('active');
			const files = e.dataTransfer.files;
			fileInput.files = files;
	
			if (previewImage && files && files[0]) {
				const reader = new FileReader();
				reader.onload = function (e) {
					previewImage.src = e.target.result;
					previewImage.style.display = 'block';
				};
				reader.readAsDataURL(files[0]);
			}
		});
	
		// Input change
		fileInput.addEventListener('change', () => {
			if (previewImage && fileInput.files && fileInput.files[0]) {
				const reader = new FileReader();
				reader.onload = function (e) {
					previewImage.src = e.target.result;
					previewImage.style.display = 'block';
				};
				reader.readAsDataURL(fileInput.files[0]);
			}
		});
	}

	// 📌 script para my info popup
	let editMyDataButton = document.getElementById('edit-my-data');
	editMyDataButton.addEventListener('click', function (e) {
		e.preventDefault();

		scrollToTopIfNeeded();
		
		const editMyInfoForm = document.getElementById('edit-my_info-form');
		const popupContent = editMyInfoForm.querySelector('.formular-frame');

		if (editMyInfoForm && popupContent) {
			editMyInfoForm.style.display = 'block';
			editMyInfoForm.style.opacity = '0';
			editMyInfoForm.style.transition = 'opacity 0.5s ease';
			setTimeout(() => {
				editMyInfoForm.style.opacity = '1';
			}, 10);

			popupContent.style.transform = 'scale(0.7)';
			popupContent.style.opacity = '0';
			popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			setTimeout(() => {
				popupContent.style.transform = 'scale(1)';
				popupContent.style.opacity = '1';
				loadMyInfo();
			}, 50);
		}
	});

	// 📌 Manejo del formulario de edit my info
	let formEditMyInfo = document.getElementById('formEditMyInfo');
	if (formEditMyInfo) {
		formEditMyInfo.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			try {
				let response = await fetch('api/update_my_info.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				let data = await response.json();
				console.log('Server response:', data);

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				banner.style.display = 'block';
				banner.style.opacity = '1';

				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = "Error processing request.";
				statusImage.src = "../images/sys-img/error.gif";
				banner.style.display = 'block';
			}
		});
	}

	initDragAndDrop('profile-drop-area', 'image', 'profile-pic-preview');

	// 📌 script para subscrition popup
	let subscButton = document.getElementById('subsc-button');
	subscButton.addEventListener('click', function (e) {
		e.preventDefault();

		scrollToTopIfNeeded();

		const subscForm = document.getElementById('subsc-form');
		const popupContent = subscForm.querySelector('.formular-frame');

		if (subscForm && popupContent) {
			subscForm.style.display = 'block';
			subscForm.style.opacity = '0';
			subscForm.style.transition = 'opacity 0.5s ease';
			setTimeout(() => {
				subscForm.style.opacity = '1';
			}, 10);

			popupContent.style.transform = 'scale(0.7)';
			popupContent.style.opacity = '0';
			popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			setTimeout(() => {
				popupContent.style.transform = 'scale(1)';
				popupContent.style.opacity = '1';
				loadCurrentPackage();
			}, 50);
		}
	});

	// 📌 Manejo del formulario de subscripcion
	let formSubscription = document.getElementById('formSubscription');
	if (formSubscription) {
		formSubscription.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			try {
				let response = await fetch('api/subscribe.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();
				console.log('Server response:', data);

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					banner.style.display = 'block';
					banner.style.opacity = '1';

					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 1000);
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif;
					banner.style.display = 'block';
				}
			} catch (error) {
				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = "Error processing request.";
				statusImage.src = "../images/sys-img/error.gif";
				banner.style.display = 'block';
			}
		});
	}

	// 📌 script para recojer los datos de la compania
	async function loadMyInfo() {
		try {
			let response = await fetch('api/get_my_info.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
	
			let data = await response.json();
			console.log("User Info:", data);
	
			if (data.success && data.data) {
				let user = data.data;
	
				document.getElementById('name').value = user.name || "";
				document.getElementById('surname').value = user.surname || "";
				document.getElementById('birthday').value = user.birthday ? user.birthday.split(' ')[0] : "";
				document.getElementById('phone').value = user.phone || "";
				document.getElementById('email').value = user.email || "";
	
				const profilePicPreview = document.getElementById('profile-pic-preview');

				if (user.image && user.image.trim() !== "") {
					profilePicPreview.src = `../images/profile/${user.image}`;
					profilePicPreview.style.display = 'block';
				} else {
					profilePicPreview.src = ""; // Deja la imagen en blanco si no hay una
					profilePicPreview.style.display = 'none';
				}
			}
		} catch (error) {
			console.error("Error loading user info:", error);
		}
	}
	
	// 📌 script para recojer los datos de la compania
	async function loadCompanyData() {
		try {
			let response = await fetch('api/get_company_info.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
	
			let data = await response.json();
			if (data.success && data.data) {
				let company = data.data;

				document.getElementById('company_name').value = company.company_name || '';
				document.getElementById('organization_no').value = company.organization_no || '';
				document.getElementById('company_address').value = company.company_address || '';
				document.getElementById('company_phone').value = company.company_phone || '';

				if (company.company_logo) {
					const logoPreview = document.getElementById('logo-preview');
					logoPreview.src = `images/company-logos/${company.company_logo}`;
					logoPreview.style.display = 'block';
				}
			}
		} catch (error) {
			console.error("Error loading company data:", error);
		}
	}

	// 📌 script para update company popup
	let editCompButton = document.getElementById('edit-comp-button');
	editCompButton.addEventListener('click', function (e) {
		e.preventDefault();

		scrollToTopIfNeeded();

		const editCompanyForm = document.getElementById('edit-company-form');
		const popupContent = editCompanyForm.querySelector('.formular-frame');

		if (editCompanyForm && popupContent) {
			editCompanyForm.style.display = 'block';
			editCompanyForm.style.opacity = '0';
			editCompanyForm.style.transition = 'opacity 0.5s ease';
			setTimeout(() => {
				editCompanyForm.style.opacity = '1';
			}, 10);

			popupContent.style.transform = 'scale(0.7)';
			popupContent.style.opacity = '0';
			popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			setTimeout(() => {
				popupContent.style.transform = 'scale(1)';
				popupContent.style.opacity = '1';
				loadCompanyData();
			}, 50);
		}
	});

	// 📌 Manejo del formulario de update Company
	let formEditCompany = document.getElementById('formEditCompany');
	if (formEditCompany) {
		formEditCompany.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			try {
				let response = await fetch('api/update_company.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				let data = await response.json();
				console.log('Server response:', data);

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				banner.style.display = 'block';
				banner.style.opacity = '1';

				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = "../images/sys-img/error.gif";
				banner.style.display = 'block';
			}
		});
	}

	initDragAndDrop('drop-area', 'company_logo', 'logo-preview');

	// 📌 script para add members popup
	let addMemberButton = document.getElementById('add-members-button');
	addMemberButton.addEventListener('click', function (e) {
		e.preventDefault();

		scrollToTopIfNeeded();

		const addMembersForm = document.getElementById('add-members-form');
		const popupContent = addMembersForm.querySelector('.formular-frame');

		if (addMembersForm && popupContent) {
			addMembersForm.style.display = 'block';
			addMembersForm.style.opacity = '0';
			addMembersForm.style.transition = 'opacity 0.5s ease';
			setTimeout(() => {
				addMembersForm.style.opacity = '1';
			}, 10);

			popupContent.style.transform = 'scale(0.7)';
			popupContent.style.opacity = '0';
			popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			setTimeout(() => {
				popupContent.style.transform = 'scale(1)';
				popupContent.style.opacity = '1';
			}, 50);
		}
	});

	// 📌 Manejo del formulario de crear miembros
	let formMembers = document.getElementById('formMembers');
	if (formMembers) {
		formMembers.addEventListener('submit', async function (e) {
			e.preventDefault();

			let formData = new FormData(this);

			try {
				let response = await fetch('api/create_members.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json' },
					body: formData
				});

				let data = await response.json();
				console.log('Server response:', data);

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (data.success) {
					statusText.innerText = data.message;
					statusImage.src = data.img_gif;
					banner.style.display = 'block';
					banner.style.opacity = '1';

					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url;
						}, 1000);
					}, 3000);
				} else {
					statusText.innerText = "Error: " + data.message;
					statusImage.src = data.img_gif; 
					banner.style.display = 'block';
				}
			} catch (error) {
				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				statusText.innerText = "Error procesando la solicitud.";
				statusImage.src = data.img_gif;
				banner.style.display = 'block';
			}
		});
	}

});