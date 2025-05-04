document.addEventListener("DOMContentLoaded", async function () {
	// 📌 Redireccionar al hacer clic en Menu
	document.querySelectorAll('.menu li').forEach(item => {
		if (!item.classList.contains('no-redirect')) {
			item.addEventListener('click', function () {
				const section = this.textContent.trim().toLowerCase();
				window.location.href = section + ".php";
			});
		}

		const currentPage = window.location.pathname.split("/").pop(); // ej: "products.php"
		const sectionName = item.textContent.trim().toLowerCase() + ".php";

		if (sectionName === currentPage) {
			item.classList.add('active');
		}
	});

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
        // console.log("Server response:", data);

		let myName = document.getElementById("my-name");
		let hiUser = document.getElementById("hi-user");
		let myData = document.getElementById("my-data");
		let subsc = document.getElementById("subsc");
		let totalSpot = document.getElementById("total-spot");
		const headerProfilePic = document.getElementById("header-profile-pic");

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

			if (myName) {
				myName.innerHTML = (String(user.name).trim() || "") + " " + (String(user.surname).trim() || "");
			}

			if (headerProfilePic) {
				const hasCustomImage = user.image && user.image.trim() !== "";
			
				headerProfilePic.src = hasCustomImage
					? `../images/profile/${user.image}`
					: "../images/sys-img/NonProfilePic.png";
			
				headerProfilePic.alt = hasCustomImage
					? "User profile picture"
					: "Default profile picture";
			
				headerProfilePic.classList.remove("default-profile-pic", "custom-profile-pic");
				headerProfilePic.classList.add(hasCustomImage ? "custom-profile-pic" : "default-profile-pic");
			}
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
	const myCompany = document.getElementById("company-data");
	if (myCompany) {
		try {
			let response = await fetch('api/get_company_info.php', {
				method: 'GET',
				headers: { Accept: "application/json" }
			});

			let data = await response.json();
			// console.log("Server response:", data);

			if (data.success && data.data) {
				let company = data.data;

				let logoHTML = "";
				if (company.company_logo && company.company_logo.trim() !== "") {
					logoHTML = `<p><img src="images/company-logos/${company.company_logo}" alt="Company Logo" style="max-width: 40px; margin: 0 auto; border-radius: 50%; border: 1px solid #000;"></p>`;
				}

				myCompany.innerHTML = 
					logoHTML +
					`<p><strong>Org No.:</strong> ` + (company.organization_no && company.organization_no.trim() !== "" ? `${company.organization_no}</p>` : "-</p>") +
					`<p><strong>Name:</strong> ` + (company.company_name && company.company_name.trim() !== "" ? `${company.company_name}</p>` : "-</p>");
			} else {
				myCompany.innerHTML = `<p>No company information available.</p>`;
			}
		} catch (error) {
			console.error("Error fetching data:", error);
			document.getElementById("company-data").innerHTML = `<tr><td colspan="6">Error al cargar los datos de empresa.</td></tr>`;
		}
	}

	// 📌 Manejo de lista de usuarios hijos
	const spot = document.getElementById("spot");
	const userContainer = document.getElementById('child-user-table');
	if (spot && userContainer) {
		try {
			let response = await fetch('api/get_users.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});

			let data = await response.json();
			// console.log('Server response:', data);

			spot.innerHTML = data.count !== "" ? data.count : "0";

			if (data.success && data.count > 0) {
				userContainer.innerHTML = '';

				data.users.forEach(user => {
					let card = document.createElement("div");
					card.classList.add("members-card");

					let profileImage = user.image && user.image.trim() !== "" 
					? `images/profile/${user.image}` 
					: "images/sys-img/NonProfilePic.png";

					let borderColor = Number(user.status) === 1 ? "#8cda8a" : "#fbadad";

					card.innerHTML = `
						<div class="mini-banner">
							<div class="mini-profile" style="border: 2px solid ${borderColor};">
								<img src="${profileImage}" alt="Profile Picture">
							</div>
							<div class="co-worker-position">${user.rank_text || 'Unknown role'}</div>
						</div>
						<div class="card-info">
							<h3>${user.name} ${user.surname}</h3>
							<p><strong>Email:</strong> ${user.email}</p>
							<p><strong>Phone:</strong> ${user.phone ? user.phone : "No Phone Number"}</p>
						</div>
						<div class="card-menu">
							<img src="images/sys-img/edit-icon.png" alt="edit-card">
						</div>
					`;

					userContainer.appendChild(card);

					const cardMenuBtn = card.querySelector('.card-menu');
					cardMenuBtn.addEventListener('click', () => {
						openMemberForm(user.user_id);
					});
				});
			} else {
				userContainer.innerHTML = "<p>No members found.</p>";
			}
		} catch (error) {
			console.error('Error fetching data:', error);
			document.getElementById("child-user-table").innerHTML = `<p>Error loading user data.</p>`;
		}
	}

	async function openMemberForm(userId) {
		scrollToTopIfNeeded();
	
		const addMembersForm = document.getElementById('edit-members-form');
		const popupContent = addMembersForm.querySelector('.formular-frame');
		const formEditMembers = document.getElementById('formEditMembers');
	
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

			formEditMembers.setAttribute('data-user-id', userId);
		}

		try {
			let response = await fetch(`api/get_user_by_id.php?user_id=${userId}`);
			let data = await response.json();
	
			if (data.success && data.data) {
				const user = data.data;
				const ranks = data.ranks;

				document.getElementById('edit_name').value = user.name || '';
				document.getElementById('edit_surname').value = user.surname || '';
				document.getElementById('edit_birthday').value = user.birthday ? user.birthday.split(" ")[0] : '';
				document.getElementById('edit_phone').value = user.phone || '';
				document.getElementById('edit_email').value = user.email || '';

				populateRankSelect('edit_rank', user.rank, ranks);
				// Opcional: Puedes ocultar el campo de contraseña si estás editando
				// document.getElementById('edit_password').value = '';
				document.getElementById("edit_status").checked = user.status === "1" || user.status === 1;
			}
		} catch (error) {
			console.error("Error loading user data:", error);
		}
	}

	// 📌 Manejo del formulario de edit usuarios hijos
	const formEditMembers = document.getElementById('formEditMembers');
	if (formEditMembers) {
		formEditMembers.addEventListener('submit', async function (e) {
			e.preventDefault();
	
			const formData = new FormData(this);
			formData.append('edit_user_id', formEditMembers.getAttribute('data-user-id')); // ID del usuario a editar
	
			try {
				const response = await fetch('api/update_member.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});
	
				const data = await response.json();
				console.log("Update response:", data);
	
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
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				console.error("Error updating member:", error);
			}
		});
	}

	// 📌 Script para eliminar co-worker
	const deleteBtn = document.getElementById('deleteAccountBtn');
	const formEditMembersToDelete = document.getElementById('formEditMembers');
	if (deleteBtn && formEditMembersToDelete) {
		deleteBtn.addEventListener('click', async () => {
			const userId = formEditMembersToDelete.getAttribute('data-user-id');

			if (!userId) {
				alert("User ID not found.");
				return;
			}

			showConfirmModal("Delete User", "Are you sure you want to delete this user?", async () => {
				const frame = document.querySelector('.formular-frame');
				if (frame) frame.style.display = 'none';

				const formData = new FormData();
				formData.append("user_id", userId);

				try {
					const response = await fetch('api/delete_user.php', {
						method: 'POST',
						body: formData
					});

					const data = await response.json();
					console.log('Delete response:', data);

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
								window.location.href = data.redirect_url || window.location.href;
							}, 1000);
						}, 3000);
					}
				} catch (error) {
					console.error("Error deleting user:", error);
					alert("Error deleting user. Check console.");
				}
			});
		});
	}

	// 📌 Boton para cerrar formularios
	let cancelButtons = document.querySelectorAll('.neutral-btn');
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

	if (selectPack) {
		selectPack.addEventListener('change', updateEstimatedCost);
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
					previewImage.style.opacity = 1;
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
					previewImage.style.opacity = 1;
				};
				reader.readAsDataURL(fileInput.files[0]);
			}
		});
	}

	// 📌 script para my info popup
	let editMyDataButton = document.getElementById('edit-my-data');
	if (editMyDataButton) {
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
	}

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
	if (subscButton) {
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
	}

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
	
				document.getElementById('user_name').value = user.name || "";
				document.getElementById('user_surname').value = user.surname || "";
				document.getElementById('user_birthday').value = user.birthday ? user.birthday.split(' ')[0] : "";
				document.getElementById('user_phone').value = user.phone || "";
				document.getElementById('user_email').value = user.email || "";
	
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

				const logoPreview = document.getElementById('logo-preview');
				if (logoPreview) {
					if (company.company_logo && company.company_logo.trim() !== "") {
						logoPreview.src = `images/company-logos/${company.company_logo}`;
						logoPreview.style.display = 'block';
						logoPreview.style.visibility = 'visible';
						logoPreview.style.opacity = '1';
					} else {
						logoPreview.src = '';
						logoPreview.style.display = 'none';
						logoPreview.style.visibility = 'hidden';
						logoPreview.style.opacity = '0';
					}
				}
			}
		} catch (error) {
			console.error("Error loading company data:", error);
		}
	}

	// 📌 script para update company popup
	let editCompButton = document.getElementById('edit-comp-button');
	if (editCompButton) {
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

				initDragAndDrop('company-logo-drop-area', 'company_logo', 'logo-preview');
			}
		});
	}

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


	// Función para llenar el <select>
	function populateRankSelect(selectId, selectedValue = '', ranks = {}) {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select user role';
		select.appendChild(defaultOption);
	
		for (const [value, label] of Object.entries(ranks)) {
			const option = document.createElement('option');
			option.value = value;
			option.textContent = label;
			if (String(value) === String(selectedValue)) {
				option.selected = true;
			}
			select.appendChild(option);
		}
	}

	// 📌 script para add members popup
	let addMemberButton = document.getElementById('add-members-button');
	if (addMemberButton) {
		addMemberButton.addEventListener('click', async function (e) {
			e.preventDefault();

			try {
				const usersRes = await fetch('api/get_users.php');
				const usersData = await usersRes.json();
				const currentMemberCount = usersData.success ? usersData.count : 0;
	
				const userInfoRes = await fetch('api/get_my_info.php');
				const userInfo = await userInfoRes.json();
				const allowedMembers = userInfo.success ? parseInt(userInfo.data.members) : 0;
	
				if (currentMemberCount >= allowedMembers) {
					showAlertModal("Maximum allowed members reached", "If you want to have the ability to add more members, upgrade your membership.");
					return;
				}

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

					try {
						const res = await fetch('api/get_global_array.php?key=ranks');
						const data = await res.json();
		
						if (data.success && data.data) {
							populateRankSelect('rank', '', data.data);
						} else {
							console.error("Failed to load ranks:", data.message);
						}
					} catch (err) {
						console.error("Error fetching ranks:", err);
					}
				}
			} catch (err) {
				console.error("Error validating member limit:", err);
				alert("An error occurred while validating your permission to add members.");
			}
		});
	}

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

//################################################################ PRODUCTS #####################################################################

	// 📌 script para add product popup
	let addProductButton = document.getElementById('add-product-btn');
	if (addProductButton) {
		addProductButton.addEventListener('click', function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const addProductForm = document.getElementById('add-product-form');
			const popupContent = addProductForm.querySelector('.formular-frame');

			if (addProductForm && popupContent) {
				addProductForm.style.display = 'block';
				addProductForm.style.opacity = '0';
				addProductForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					addProductForm.style.opacity = '1';
				}, 10);

				popupContent.style.transform = 'scale(0.7)';
				popupContent.style.opacity = '0';
				popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);

				initDragAndDrop('drop-product-area', 'product_image', 'product-image-preview');

				populateVehicleTypes('product_type');

				initCategorySelectors('product_mark', 'product_model', 'product_sub_model');
			}
		});
	}

	
	// 📌 Manejo del formulario para crear Producto
	const formAddProduct = document.getElementById('formAddProduct');
	if (formAddProduct) {
		formAddProduct.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);

			try {
				const response = await fetch('api/create_product.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				const data = await response.json();
				console.log('Server response:', data);

				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				banner.style.display = 'block';
				banner.style.opacity = '1';

				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');

				statusText.innerText = "Error processing the request.";
				statusImage.src = "../images/sys-img/error.gif";
				banner.style.display = 'block';
			}
		});
	}

	// 📌 script para add category popup
	let addCategoryButton = document.getElementById('add-category-btn');
	if (addCategoryButton) {
		addCategoryButton.addEventListener('click', function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const addProductForm = document.getElementById('add-category-form');
			const popupContent = addProductForm.querySelector('.formular-big-frame');

			if (addProductForm && popupContent) {
				addProductForm.style.display = 'block';
				addProductForm.style.opacity = '0';
				addProductForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					addProductForm.style.opacity = '1';
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
	}

	// 📌 script para crear Marca / categoria
	let addMarkBtn = document.getElementById('add-mark-btn');
	if (addMarkBtn) {
		addMarkBtn.addEventListener('click', function(){
			let clicCreateMark = document.getElementById('clic-create-mark');
			let inputMark = document.getElementById('input-mark');

			clicCreateMark.style.display = 'none';
			inputMark.style.display = 'block';
		});
	}

	const inputProductMark = document.getElementById('input-product-mark');
	const markList = document.getElementById('mark-list');
	const btnCreateMark = document.getElementById('btn-create-mark');

	if (inputProductMark) {
		inputProductMark.addEventListener('input', () => {
			let currentValue = inputProductMark.value;
			
			if (currentValue === currentValue.toUpperCase()) return;

			let words = inputProductMark.value.split(" ");
			words = words.map(word => {
				return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
			});
			inputProductMark.value = words.join(" ");
		});
	}

	if (btnCreateMark) {
		btnCreateMark.addEventListener('click', function (e) {
			e.preventDefault();
			const value = inputProductMark.value.trim();

			if (value !== '') {
				const existingNames = markList.querySelectorAll('tr td:nth-child(2)');
				let exists = false;

				existingNames.forEach(cell => {
					if (cell.textContent.trim().toLowerCase() === value.toLowerCase()) {
						exists = true;
					}
				});

				if (exists) {
					showConfirmModal(
						"Mark Already Exists",
						`The mark "${value}" already exists. Please choose a different name.`,
						() => {
							inputProductMark.focus();
						}
					);
					return;
				}

				const existingRadios = document.querySelectorAll('input[name="product_mark"]');
				existingRadios.forEach(r => r.checked = false);

				const uniqueId = `mark-${Date.now()}`;
				const row = document.createElement('tr');
				row.className = "categoryContainer";
				row.innerHTML = `
					<td width="10%" align="center" valign="middle">
						<div class="list-icon">
							<img src="images/sys-img/element-list.png" alt="">
						</div>
					</td>
					<td width="80%" valign="middle" style="padding-left:10px;">${value}</td>
					<td width="10%" align="center" valign="middle">
						<div class="opcion-radio">
							<input type="radio" id="${uniqueId}" name="product_mark" class="category-radio" data-mark="${value}" checked />
							<label for="${uniqueId}"></label>
						</div>
					</td>
				`;
				markList.appendChild(row);
				inputProductMark.value = '';
			}
		});

		try {
			let response = await fetch('api/get_categories.php', {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});

			let data = await response.json();

			if (data.success && data.data.length > 0) {
				data.data.forEach(category => {
					const uniqueId = `mark-db-${category.category_id}`;
					const row = document.createElement('tr');
					row.className = "categoryContainer";
					row.innerHTML = `
						<td width="10%" align="center" valign="middle">
							<div class="list-icon">
								<img src="images/sys-img/element-list.png" alt="">
							</div>
						</td>
						<td width="80%" valign="middle" style="padding-left:10px;">${category.category_name}</td>
						<td width="10%" align="center" valign="middle">
							<div class="opcion-radio">
								<input type="radio" id="${uniqueId}" name="product_mark" class="category-radio" data-mark="${category.category_id}" />
								<label for="${uniqueId}"></label>
							</div>
						</td>
					`;
					markList.appendChild(row);
				});
			}
		} catch (error) {
			console.error("Error loading categories:", error);
		}
	}


	// 📌 script para crear sub-categoria / modelo
	let addModelBtn = document.getElementById('add-model-btn');
	if (addModelBtn) {
		addModelBtn.addEventListener('click', function(){
			let clicCreateMark = document.getElementById('clic-create-model');
			let inputMark = document.getElementById('input-model');

			clicCreateMark.style.display = 'none';
			inputMark.style.display = 'block';
		});
	}

	const inputProductModel = document.getElementById('input-product-model');
	const modelList = document.getElementById('model-list');
	const btnCreateModel = document.getElementById('btn-create-model');
	
	if (inputProductModel) {
		inputProductModel.addEventListener('input', () => {
			let currentValue = inputProductModel.value;
	
			if (currentValue === currentValue.toUpperCase()) return;

			let words = inputProductModel.value.split(" ");
			words = words.map(word => {
				return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
			});
			inputProductModel.value = words.join(" ");
		});
	}

	if (btnCreateModel) {
		btnCreateModel.addEventListener('click', function (e) {
			e.preventDefault();
			const value = inputProductModel.value.trim();

			if (value !== '') {
				const emptyRow = modelList.querySelector('tr[data-empty-message]');
				if (emptyRow) {
					emptyRow.remove();
				}

				const existingRadios = document.querySelectorAll('input[name="product_model"]');
				existingRadios.forEach(r => r.checked = false);

				const uniqueId = `model-${Date.now()}`;
				const row = document.createElement('tr');
				row.className = "categoryContainer";
				row.innerHTML = `
					<td width="10%" align="center" valign="middle">
						<div class="list-icon">
							<img src="images/sys-img/element-list.png" alt="">
						</div>
					</td>
					<td width="80%" valign="middle" style="padding-left:10px;">${value}</td>
					<td width="10%" align="center" valign="middle">
						<div class="opcion-radio">
							<input type="radio" id="${uniqueId}" name="product_model" class="category-radio" data-model="${value}" checked />
							<label for="${uniqueId}"></label>
						</div>
					</td>
				`;
				modelList.appendChild(row);
				inputProductModel.value = '';
			}
		});
	}

	// CARGAR MODELOS (SUB-CATEGORÍAS) DINÁMICAMENTE CUANDO SE SELECCIONA UNA MARCA
	document.addEventListener('change', function (e) {
		if (e.target.matches('input[name="product_mark"]')) {
			if (e.target.checked) {
				addModelBtn.disabled = false;
				addModelBtn.classList.remove('disabled');
			}

			const selectedMarkId = e.target.dataset.mark;

			if (!isNaN(selectedMarkId)) {
				modelList.innerHTML = '';

				fetch(`api/get_sub_categories.php?mark_id=${selectedMarkId}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				})
				.then(res => res.json())
				.then(data => {
					if (data.success && data.data.length > 0) {
						data.data.forEach(model => {
							const uniqueId = `model-db-${model.category_id}`;
							const row = document.createElement('tr');
							row.className = "categoryContainer";
							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="list-icon">
										<img src="images/sys-img/element-list.png" alt="">
									</div>
								</td>
								<td width="80%" valign="middle" style="padding-left:10px;">${model.category_name}</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-radio">
										<input type="radio" id="${uniqueId}" name="product_model" class="category-radio" data-model="${model.category_id}" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;
							modelList.appendChild(row);
						});
					} else {
						modelList.innerHTML = `<tr data-empty-message><td colspan="3" style="text-align: center; padding:15px 0;">No models found for this brand.</td></tr>`;
					}
				})
				.catch(error => {
					console.error("Error loading subcategories:", error);
				});
			}
		}
	});


	// 📌 script para crear sub-modelo
	let addSubmodelBtn = document.getElementById('add-submodel-btn');
	if (addSubmodelBtn) {
		addSubmodelBtn.addEventListener('click', function(){
			let clicCreateMark = document.getElementById('clic-create-submodel');
			let inputMark = document.getElementById('input-submodel');

			clicCreateMark.style.display = 'none';
			inputMark.style.display = 'block';
		});
	}

	const inputSubmodel = document.getElementById('input-product-submodel');
	const submodelList = document.getElementById('submodel-list');
	const btnCreateSubmodel = document.getElementById('btn-create-submodel');

	if (inputSubmodel) {
		inputSubmodel.addEventListener('input', () => {
			let currentValue = inputSubmodel.value;
	
			if (currentValue === currentValue.toUpperCase()) return;

			let words = inputSubmodel.value.split(" ");
			words = words.map(word => {
				return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
			});
			inputSubmodel.value = words.join(" ");
		});
	}

	if (inputSubmodel) {
		btnCreateSubmodel.addEventListener('click', function (e) {
			e.preventDefault();
			const value = inputSubmodel.value.trim();

			if (value !== '') {
				const emptyRow = submodelList.querySelector('tr[data-empty-message]');
				if (emptyRow) {
					emptyRow.remove();
				}

				const existingRadios = document.querySelectorAll('input[name="product_sub_model"]');
				existingRadios.forEach(r => r.checked = false);

				const uniqueId = `submodel-${Date.now()}`;
				const row = document.createElement('tr');
				row.className = "categoryContainer";
				row.innerHTML = `
					<td width="10%" align="center" valign="middle">
						<div class="list-icon">
							<img src="images/sys-img/element-list.png" alt="">
						</div>
					</td>
					<td width="80%" valign="middle" style="padding-left:10px;">${value}</td>
					<td width="10%" align="center" valign="middle">
						<div class="opcion-radio">
							<input type="radio" id="${uniqueId}" name="product_sub_model" class="category-radio" data-submodel="${value}" checked />
							<label for="${uniqueId}"></label>
						</div>
					</td>
				`;
				submodelList.appendChild(row);
				inputSubmodel.value = '';
			}
		});
	}

	// DETECTAR CAMBIO EN MODELO Y CARGAR SUB-MODELOS
	document.addEventListener('change', function (e) {
		if (e.target.matches('input.category-radio[name="product_model"]')) {
			if (e.target.checked) {
				addSubmodelBtn.disabled = false;
				addSubmodelBtn.classList.remove('disabled');
			}

			const selectedModelId = e.target.dataset.model;

			if (!isNaN(selectedModelId)) {
				submodelList.innerHTML = '';

				fetch(`api/get_sub_models.php?model_id=${selectedModelId}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				})
				.then(res => res.json())
				.then(data => {
					if (data.success && data.data.length > 0) {
						data.data.forEach(submodel => {
							const uniqueId = `submodel-db-${submodel.category_id}`;
							const row = document.createElement('tr');
							row.className = "categoryContainer";
							row.innerHTML = `
								<td width="10%" align="center" valign="middle">
									<div class="list-icon">
										<img src="images/sys-img/element-list.png" alt="">
									</div>
								</td>
								<td width="80%" valign="middle" style="padding-left:10px;">${submodel.category_name}</td>
								<td width="10%" align="center" valign="middle">
									<div class="opcion-radio" style="display: none;"> <!-- oculto con display none -->
										<input type="radio" id="${uniqueId}" name="product_sub_model" class="category-radio" data-submodel="${submodel.category_id}" />
										<label for="${uniqueId}"></label>
									</div>
								</td>
							`;
							submodelList.appendChild(row);
						});
					} else {
						submodelList.innerHTML = `<tr data-empty-message><td colspan="3" style="text-align: center; padding:15px 0;">No submodels found.</td></tr>`;
					}
				})
				.catch(error => {
					console.error("Error loading submodels:", error);
				});
			}
		}
	});

	// 📌 JavaScript para enviar datos de creación de marca, modelo o submodelo
	const formAddCategory = document.getElementById("formAddCategory");
	if (formAddCategory) {
		formAddCategory.addEventListener("submit", async function (e) {
			e.preventDefault();

			const selectedMark = document.querySelector('input[name="product_mark"]:checked');
			const selectedModel = document.querySelector('input[name="product_model"]:checked');
			const selectedSubmodel = document.querySelector('input[name="product_sub_model"]:checked');

			let name = "";
			let cat_parent_sub = null;
			let sub_parent = null;

			if (selectedSubmodel) {
				// ✅ Ingresar submodelo
				name = selectedSubmodel.dataset.submodel;
				cat_parent_sub = selectedMark ? parseInt(selectedMark.dataset.mark) : null;
				sub_parent = selectedModel ? parseInt(selectedModel.dataset.model) : null;

			} else if (selectedModel) {
				// ✅ Ingresar modelo
				name = selectedModel.dataset.model;
				cat_parent_sub = selectedMark ? parseInt(selectedMark.dataset.mark) : null;

			} else if (selectedMark) {
				// ✅ Ingresar marca
				name = selectedMark.dataset.mark;

			} else {
				alert("You must select a Mark, Model, or Submodel.");
				return;
			}

			const formData = new FormData();
			formData.append("category_name", name);
			formData.append("cat_parent_sub", cat_parent_sub ?? "");
			formData.append("sub_parent", sub_parent ?? "");

			try {
				const response = await fetch("api/create_category.php", {
					method: "POST",
					body: formData,
					headers: { Accept: "application/json" },
				});

				const data = await response.json();
				console.log("Server response:", data);

				let banner = document.getElementById("status-message");
				let statusText = document.getElementById("status-text");
				let statusImage = document.getElementById("status-image");

				statusText.innerText = data.message;
				statusImage.src = data.img_gif;
				banner.style.display = "block";
				banner.style.opacity = "1";

				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = "0";
						setTimeout(() => {
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				console.error("Error submitting category:", error);
			}
		});
	}

	const container = document.getElementById('product-list');
	const searchField = document.getElementById('searchField');
	let markSelect = document.getElementById('search_product_mark');
	let modelSelect = document.getElementById('search_product_model');
	let submodelSelect = document.getElementById('search_product_sub_model');

	async function fetchAndRenderProducts() {
		if (!container) return;

		markSelect = document.getElementById('search_product_mark');
		modelSelect = document.getElementById('search_product_model');
		submodelSelect = document.getElementById('search_product_sub_model');

		const searchText = searchField?.value.trim() || "";
		let selectedMark = markSelect?.value || "";
		let selectedModel = modelSelect?.value || "";
		let selectedSubmodel = submodelSelect?.value || "";

		const params = new URLSearchParams();
		if (searchText) params.append('search', searchText);
		if (selectedMark) params.append('mark', selectedMark);
		if (selectedModel) params.append('model', selectedModel);
		if (selectedSubmodel) params.append('submodel', selectedSubmodel);

		// Log params para depuración
		// console.log("🔍 Enviando filtros a get_products.php:", params.toString());

		try {
			const res = await fetch(`api/get_products.php?${params.toString()}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();
			container.innerHTML = "";

			if (data.success && data.data.length > 0) {
				data.data.forEach(product => {
					const card = document.createElement('div');
					card.className = 'product-card';

					let isDefaultImage = !product.product_image || product.product_image.trim() === "";
					let productImage = isDefaultImage 
					? "images/sys-img/wooden-box.png"
					: `images/products/${product.product_image}`;

					let imageClass = isDefaultImage ? "grayscale-img" : "";

					card.innerHTML = `
					<div class="product-pic">
						<img src="${productImage}" alt="${product.product_name}" class="${imageClass}" />
					</div>
					<div class="product-desc">
						<table width="90%" align="center" cellspacing="0">
							<tr valign="baseline">
								<td colspan="2">
									<p>${product.product_name}</p>
									<h3><strong>${product.mark_name + ' - ' + product.model_name}</strong></h3>
									<p>${product.submodel_name || ''}</p>
								</td>
							</tr>
							<tr valign="baseline">
								<td style="width: 50%; border-top: 1px solid #CCC;">
									<p>Year<br><strong>${product.product_year || ''}</strong></p>
								</td>
								<td style="width: 50%; border-top: 1px solid #CCC;">
									<p>Prise<br><strong>${product.prise ? '$' + product.prise : ''}</strong></p>
								</td>
							</tr>
						</table>
						<div class="product-menu">
							<img src="images/sys-img/menu-icon.png" alt="product-menu">
						</div>
					</div>
					`;
					container.appendChild(card);

					const cardMenuBtn = card.querySelector('.product-menu');
					cardMenuBtn.addEventListener('click', () => {
						openProductForm(product.product_id);
					});
				});
			} else {
				container.innerHTML = `<p style="text-align:center;">No products found</p>`;
			}
		} catch (error) {
			console.error("Error loading products:", error);
			container.innerHTML = `<p style="text-align:center;">Error loading products</p>`;
		}
	}

	searchField?.addEventListener('keyup', fetchAndRenderProducts);
	document.addEventListener('change', (e) => {
		const id = e.target?.id;
		if (["search_product_mark", "search_product_model", "search_product_sub_model"].includes(id)) {
			fetchAndRenderProducts();
		}
	});

	// 📌 JavaScript para recoger datos de los select del formulario de busqueda(search)
	initCategorySelectors('search_product_mark', 'search_product_model', 'search_product_sub_model');

	fetchAndRenderProducts();


	function animateHeightChange(container, sectionToShow, callback) {
		const startHeight = container.offsetHeight + 'px';
		container.style.height = startHeight;
	
		// Ocultar sección destino antes de mostrarla
		sectionToShow.style.display = 'block';
		sectionToShow.style.opacity = '0';
		sectionToShow.style.visibility = 'hidden';
	
		// Realizar cambios (esconder lo anterior, mostrar lo nuevo)
		if (callback) callback();
	
		requestAnimationFrame(() => {
			const desiredHeight = container.scrollHeight;
			const maxHeight = window.innerHeight * 0.9;
			const endHeight = Math.min(desiredHeight, maxHeight) + 'px';
			container.style.height = endHeight;
	
			container.addEventListener('transitionend', function handler() {
				container.style.height = 'auto';
				// Mostrar suavemente la sección nueva después del estiramiento
				sectionToShow.style.visibility = 'visible';
				sectionToShow.style.transition = 'opacity 0.2s ease';
				sectionToShow.style.opacity = '1';
	
				container.removeEventListener('transitionend', handler);
			});
		});
	}

	async function openProductForm(productId) {
		scrollToTopIfNeeded();
	
		const productOptions = document.getElementById('product-options');
		const popupContent = productOptions.querySelector('.formular-frame');
		const productName = document.getElementById('product-name');
	
		if (!productId) return;

		try {
			const res = await fetch(`api/get_products.php?product_id=${productId}`);
			const data = await res.json();

			if (data.success && data.data.length > 0) {
				const product = data.data.find(p => p.product_id == productId);
				if (product && productName) {
					productName.textContent = product.mark_name + ' - ' + product.model_name;
				}
			}

			if (productOptions && popupContent) {
				resetProductPopupView();

				productOptions.style.display = 'block';
				productOptions.style.opacity = '0';
				productOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					productOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
		
				// Botón: Assign to sale
				const assignBtn = document.getElementById('assignSaleBtn');
				if (assignBtn) {
					assignBtn.onclick = () => {
						const menuDiv = document.getElementById('product-menu-buttons');
						const assignDiv = document.getElementById('assign-sale-section');
				
						animateHeightChange(popupContent, assignDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(assignDiv);
							});
						});
					};
				}

				// Botón: Receive as initial
				const receiveBtn = document.getElementById('receiveInitialBtn');
				if (receiveBtn) {
					receiveBtn.onclick = () => {
						const menuDiv = document.getElementById('product-menu-buttons');
						const receiveDiv = document.getElementById('receive-as-initial'); 
				
						animateHeightChange(popupContent, receiveDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(receiveDiv);
							});
						});
					};
				}
				
				// Botón: Edit product
				const editBtn = document.getElementById('editProductBtn');
				if (editBtn) {

					editBtn.setAttribute('data-product-id', productId);

					editBtn.onclick = () => {
						const menuDiv = document.getElementById('product-menu-buttons');
						const editDiv = document.getElementById('edit-product-modal');

						const productId = editBtn.getAttribute('data-product-id');
						if (!productId) return;

						openEditProductForm(productId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					}
				}

				// Botón: Delete product
				const deleteBtn = document.getElementById('deleteProductBtn');
				if (deleteBtn) {
					deleteBtn.onclick = () => {

						deleteBtn.setAttribute('data-product-id', productId);
						
						if (!productId) {
							alert("Product ID not found.");
							return;
						}

						showConfirmModal("Delete Product", "Are you sure you want to delete this product?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("product_id", productId);
				
							try {
								const response = await fetch('api/delete_product.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
								console.log('Delete response:', data);
				
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
											window.location.href = data.redirect_url || window.location.href;
										}, 1000);
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting product:", error);
								alert("Error deleting product. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading product info:", error);
		}
	}

	async function openEditProductForm(productId) {
		const formEditProduct = document.getElementById('formEditProduct');
		if (!formEditProduct) return;
	
		formEditProduct.setAttribute('data-product-id', productId);
	
		try {
			const response = await fetch(`api/get_products.php?product_id=${productId}`);
			const data = await response.json();
	
			if (data.success && data.data.length > 0) {
				const product = data.data.find(p => p.product_id == productId);
				if (!product) return;
				
				// Llenar campos del formulario
				document.getElementById('edit_product_name').value = product.product_name || '';
				document.getElementById('edit_product_year').value = product.product_year || '';
				document.getElementById('edit_prise').value = product.prise || '';
				document.getElementById('edit_description').value = product.description || '';
	
				const preview = document.getElementById('edit-product-image-preview');
				if (preview) {
					if (product.product_image && product.product_image.trim() !== "") {
						preview.src = `images/products/${product.product_image}`;
						preview.style.display = 'block';
						preview.style.visibility = 'visible';
						preview.style.opacity = '1';
					} else {
						preview.src = '';
						preview.style.display = 'none';
					}
				}
					
				await populateVehicleTypes('edit_product_type', product.product_type);
	
				await initCategorySelectors('edit_product_mark', 'edit_product_model', 'edit_product_sub_model');

				initDragAndDrop('edit-drop-product-area', 'edit_Product_image', 'edit-product-image-preview');
				
				document.getElementById('edit_product_mark').value = product.product_mark;
				await loadModels(product.product_mark, 'edit_product_model', product.product_model);
				await loadSubModels(product.product_model, 'edit_product_sub_model', product.product_sub_model);
			}
		} catch (error) {
			console.error("Error loading product data:", error);
		}
	}

	async function loadModels(markId, modelSelectId, selectedModel = '') {
		const modelSelect = document.getElementById(modelSelectId);
		if (!modelSelect || !markId) return;
	
		modelSelect.innerHTML = `<option value="">Select Model</option>`;
		modelSelect.disabled = true;
	
		try {
			const res = await fetch(`api/get_sub_categories.php?mark_id=${markId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();
			
			if (data.success && data.data.length > 0) {
				data.data.forEach(model => {
					const option = document.createElement('option');
					option.value = model.category_id;
					option.textContent = model.category_name;
					if (String(option.value) === String(selectedModel)) {
						option.selected = true;
					}
					modelSelect.appendChild(option);
				});
				modelSelect.disabled = false;
			} else {
				modelSelect.innerHTML += `<option value="">No models found</option>`;
			}
		} catch (error) {
			console.error("Error loading models:", error);
		}
	}

	async function loadSubModels(modelId, submodelSelectId, selectedSubmodel = '') {
		const submodelSelect = document.getElementById(submodelSelectId);
		if (!submodelSelect || !modelId) return;
	
		submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;
		submodelSelect.disabled = true;
	
		try {
			const res = await fetch(`api/get_sub_models.php?model_id=${modelId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();
	
			if (data.success && data.data.length > 0) {
				data.data.forEach(submodel => {
					const option = document.createElement('option');
					option.value = submodel.category_id;
					option.textContent = submodel.category_name;
					if (String(option.value) === String(selectedSubmodel)) {
						option.selected = true;
					}
					submodelSelect.appendChild(option);
				});
				submodelSelect.disabled = false;
			} else {
				submodelSelect.innerHTML += `<option value="">No submodels found</option>`;
			}
		} catch (error) {
			console.error("Error loading submodels:", error);
		}
	}

	const formEditProduct = document.getElementById('formEditProduct');
	if (formEditProduct) {
		formEditProduct.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);
			formData.append('edit_product_id', formEditProduct.getAttribute('data-product-id'));

			try {
				const response = await fetch('api/update_product.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				const data = await response.json();

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (banner && statusText && statusImage) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "images/sys-img/loading.gif";
					banner.style.display = 'block';
					banner.style.opacity = '1';
				}

				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				console.error("Error updating product:", error);
			}
		});
	}

	setupBackToMenuButton(
		'.back-to-menu-btn', 
		['assign-sale-section', 'receive-as-initial', 'edit-product-modal'], 
		'product-menu-buttons', 
		'product-options'
	);

	function resetProductPopupView() {
		const menuDiv = document.getElementById('product-menu-buttons');
		const sectionsToHide = [
			document.getElementById('edit-product-modal'),
			document.getElementById('assign-sale-section'),
			document.getElementById('assign-sale-section'),
			// puedes agregar más secciones aquí
		];

		menuDiv.style.display = 'block';
		menuDiv.style.opacity = '1';
		menuDiv.style.transform = 'scale(1)';

		sectionsToHide.forEach(section => {
			if (section) {
				section.style.display = 'none';
				section.style.opacity = '0';
				section.style.transform = 'scale(0.8)';
			}
		});
	}

//################################################################ END PRODUCTS ##################################################################

//################################################################ CUSTOMERS #####################################################################
	const customerContainer = document.getElementById('customers-list');
	const searchCustomerField = document.getElementById('customersSearchField');

	if (customerContainer || searchCustomerField) {
		async function fetchAndRenderCustomers() {
			try {
				const searchTerm = searchCustomerField?.value.trim() || "";

				const params = new URLSearchParams();
				if (searchTerm) params.append('search', searchTerm);

				const res = await fetch(`api/get_customers.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await res.json();

				customerContainer.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(customer => {
						const row = document.createElement('div');
						row.className = 'customer-row';

						const profileImg = customer.image && customer.image.trim() !== ""
							? `images/customers/${customer.image}`
							: `images/sys-img/NonProfilePic.png`;

						row.innerHTML = `
							<table width="100%" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td width="5%" align="center" valign="middle">
										<div class="customers-profile">
											<img src="${profileImg}" alt="profile picture">
										</div>
									</td>
									<td width="25%" align="left" valign="middle">
										${customer.full_name}
									</td>
									<td width="15%" align="left" valign="middle">
										<p class="mini-title">${customer.document_type}:</p>
										${customer.document_no}
									</td>
									<td width="40%" align="left" valign="middle">
										<p class="mini-title">Address:</p>
										${customer.address}
									</td>
									<td width="10%" align="center" valign="middle">
										${customer.status}
									</td>
									<td width="5%" align="center" valign="middle">
										<div class="customers-menu">
											<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
										</div>
									</td>
								</tr>
							</table>
						`;

						customerContainer.appendChild(row);

						const customersMenuBtn = row.querySelector('.customers-menu');
						customersMenuBtn.addEventListener('click', () => {
							openCusomersForm(customer.customer_id);
						});
					});
				} else {
					customerContainer.innerHTML = `<p style="text-align:center;">No customers found.</p>`;
				}
			} catch (err) {
				console.error("Error loading customers:", err);
				customerContainer.innerHTML = `<p style="text-align:center;">Error loading customers</p>`;
			}
		}

		searchCustomerField?.addEventListener('keyup', fetchAndRenderCustomers);

		fetchAndRenderCustomers();
	}


	// 📌 script para add customers popup
	let addCustomerButton = document.getElementById('add-customers-button');
	if (addCustomerButton) {
		addCustomerButton.addEventListener('click', async function (e) {
			scrollToTopIfNeeded();
			
			const addCustomersForm = document.getElementById('add-customers-form');
			const popupContent = addCustomersForm.querySelector('.formular-frame');

			if (addCustomersForm && popupContent) {
			    addCustomersForm.style.display = 'block';
			    addCustomersForm.style.opacity = '0';
			    addCustomersForm.style.transition = 'opacity 0.5s ease';
			    setTimeout(() => {
			        addCustomersForm.style.opacity = '1';
			    }, 10);

			    popupContent.style.transform = 'scale(0.7)';
			    popupContent.style.opacity = '0';
			    popupContent.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
			    setTimeout(() => {
			        popupContent.style.transform = 'scale(1)';
			        popupContent.style.opacity = '1';
			    }, 50);
			}

			initDragAndDrop('customer-drop-area', 'customer_image', 'customer-pic-preview');

			populateDocumentTypes('customer_document_type');

			populateCustomerTypes('customer_type', 1);

			populateCustomerStatus('customer_status', 1);
		});
	}

	// 📌 script para customers form menu
	const dataTab = document.getElementById('tab-customer-data');
	const referenceTab = document.getElementById('tab-customer-reference');

	const dataSection = document.getElementById('customer-data');
	const referenceSection = document.getElementById('customer-reference');

	// Mostrar por defecto la sección de "data"
	if (dataTab && referenceTab && dataSection && referenceSection) {
		activateTab(dataTab, referenceTab, dataSection, referenceSection);

		dataTab.addEventListener('click', () => {
			activateTab(dataTab, referenceTab, dataSection, referenceSection);
		});

		referenceTab.addEventListener('click', () => {
			activateTab(referenceTab, dataTab, referenceSection, dataSection);
		});
	}


	async function populateDocumentTypes(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Document Type';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch('api/get_global_array.php?key=documentTypes');
			const data = await res.json();
	
			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No document types found</option>`;
			}
		} catch (error) {
			console.error("Error loading document types:", error);
			select.innerHTML += `<option value="">Error loading document types</option>`;
		}
	}

	async function populateCustomerTypes(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select Customer Type';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch('api/get_global_array.php?key=customerTypes');
			const data = await res.json();
	
			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No customer types found</option>`;
			}
		} catch (error) {
			console.error("Error loading customer types:", error);
			select.innerHTML += `<option value="">Error loading customer types</option>`;
		}
	}

	async function populateCustomerStatus(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		select.innerHTML = '';
	
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select Status';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch('api/get_global_array.php?key=customerStatus');
			const data = await res.json();
	
			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No status types found</option>`;
			}
		} catch (error) {
			console.error("Error loading customer status:", error);
			select.innerHTML += `<option value="">Error loading customer status</option>`;
		}
	}

	// 📌 Manejo del formulario para crear Customers
	const formAddCustomer = document.getElementById('formCustomers');
	if (formAddCustomer) {
		formAddCustomer.addEventListener('submit', async function (e) {
			e.preventDefault();
	
			const formData = new FormData(this);

			try {
				const response = await fetch('api/create_customer.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});
	
				const data = await response.json();
				console.log('Server response:', data);
	
				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');
	
				statusText.innerText = data.message || 'No message';
				statusImage.src = data.img_gif || '../images/sys-img/info.gif';
				banner.style.display = 'block';
				banner.style.opacity = '1';
	
				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				console.error("Error submitting customer form:", error);
	
				const banner = document.getElementById('status-message');
				const statusText = document.getElementById('status-text');
				const statusImage = document.getElementById('status-image');
	
				statusText.innerText = "Error processing the request.";
				statusImage.src = "../images/sys-img/error.gif";
				banner.style.display = 'block';
				banner.style.opacity = '1';
			}
		});
	}

	async function openCusomersForm(customerId) {
		scrollToTopIfNeeded();
	
		const customersOptions = document.getElementById('customers-options');
		const popupContent = customersOptions.querySelector('.formular-frame');
		const customerName = document.getElementById('customers-name');
	
		if (!customerId) return;

		try {
			const res = await fetch(`api/get_customers.php?customer_id=${customerId}`);
			const data = await res.json();

			if (data.success && data.data.length > 0) {
				const customers = data.data.find(p => p.customer_id == customerId);
				if (customers && customerName) {
					customerName.textContent = customers.customer_name + ' ' + customers.customer_surname;
				}
			}

			if (customersOptions && popupContent) {
				resetCustomerPopupView();

				customersOptions.style.display = 'block';
				customersOptions.style.opacity = '0';
				customersOptions.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					customersOptions.style.opacity = '1';
				}, 10);

				popupContent.style.opacity = '0';
				popupContent.style.transform = 'scale(0.7)';
				popupContent.classList.remove('animate-elastic');
				setTimeout(() => {
					popupContent.style.transform = 'scale(1)';
					popupContent.style.opacity = '1';
				}, 50);
		
				// Botón: Assign to sale
				const assignBtn = document.getElementById('assignCustomerSaleBtn');
				if (assignBtn) {
					assignBtn.onclick = () => {
						const menuDiv = document.getElementById('product-menu-buttons');
						const assignDiv = document.getElementById('assign-sale-section');
				
						animateHeightChange(popupContent, assignDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(assignDiv);
							});
						});
					};
				}
				
				// Botón: Edit Customer
				const editBtn = document.getElementById('editCustomerBtn');
				if (editBtn) {

					editBtn.setAttribute('data-customer-id', customerId);

					editBtn.onclick = () => {
						const menuDiv = document.getElementById('customers-menu-buttons');
						const editDiv = document.getElementById('edit-customers-modal');

						const customerId = editBtn.getAttribute('data-customer-id');
						if (!customerId) return;

						openEditCustomerForm(customerId);
			
						animateHeightChange(popupContent, editDiv, () => {
							fadeOutAndHide(menuDiv, () => {
								showWithFadeIn(editDiv);
							});
						});
					}
				}

				// Botón: Delete Customer
				const deleteBtn = document.getElementById('deleteCustomerBtn');
				if (deleteBtn) {
					deleteBtn.onclick = () => {

						deleteBtn.setAttribute('data-customer-id', customerId);
						
						if (!customerId) {
							alert("Customer ID not found.");
							return;
						}

						showConfirmModal("Delete Customer", "Are you sure you want to delete this cusomer?", async () => {
							const frame = document.querySelector('.formular-frame');
							if (frame) frame.style.display = 'none';

							const formData = new FormData();
							formData.append("customer_id", customerId);
				
							try {
								const response = await fetch('api/delete_customer.php', {
									method: 'POST',
									body: formData
								});
				
								const data = await response.json();
								console.log('Delete response:', data);
				
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
											window.location.href = data.redirect_url || window.location.href;
										}, 1000);
									}, 3000);
								}
							} catch (error) {
								console.error("Error deleting product:", error);
								alert("Error deleting product. Check console.");
							}
						});
					};
				}
			}
		} catch (error) {
			console.error("Error loading product info:", error);
		}
	}

	async function openEditCustomerForm(customerId) {
		const formEditCustomer = document.getElementById('formEditCustomer');
		if (!formEditCustomer) return;
	
		formEditCustomer.setAttribute('data-customer-id', customerId);
	
		try {
			const response = await fetch(`api/get_customers.php?customer_id=${customerId}`);
			const data = await response.json();
	
			if (data.success && data.data.length > 0) {
				const customer = data.data.find(c => c.customer_id == customerId);
				if (!customer) return;
	
				// Llenar campos del formulario
				document.getElementById('edit_customer_name').value = customer.customer_name || '';
				document.getElementById('edit_customer_surname').value = customer.customer_surname || '';
				document.getElementById('edit_customer_email').value = customer.customer_email || '';
				document.getElementById('edit_customer_address').value = customer.customer_address || '';
				document.getElementById('edit_customer_phone').value = customer.customer_phone || '';
				document.getElementById('edit_customer_birthday').value = customer.customer_birthday ? customer.customer_birthday.split(" ")[0] : '';
				document.getElementById('edit_customer_document_no').value = customer.customer_document_no || '';
				document.getElementById('edit_references_1').value = customer.references_1 || '';
				document.getElementById('edit_references_1_phone').value = customer.references_1_phone || '';
				document.getElementById('edit_references_2').value = customer.references_2 || '';
				document.getElementById('edit_references_2_phone').value = customer.references_2_phone || '';
	
				// Imagen de perfil
				const preview = document.getElementById('edit-customer-pic-preview');
				if (preview) {
					if (customer.customer_image && customer.customer_image.trim() !== '') {
						preview.src = `images/customers/${customer.customer_image}`;
						preview.style.display = 'block';
						preview.style.visibility = 'visible';
						preview.style.opacity = '1';
					} else {
						preview.src = '';
						preview.style.display = 'none';
					}
				}
	
				// Cargar select de tipo de documento, tipo de cliente y estatus
				await populateDocumentTypes('edit_customer_document_type', customer.customer_document_type);
				await populateCustomerTypes('edit_customer_type', customer.customer_type);
				await populateCustomerStatus('edit_customer_status', customer.customer_status);
	
				// Inicializar drag and drop
				initDragAndDrop('edit-customer-drop-area', 'edit_customer_image', 'edit-customer-pic-preview');
			}
		} catch (error) {
			console.error("Error loading customer data:", error);
		}
	}

	const formEditCustomer = document.getElementById('formEditCustomer');
	if (formEditCustomer) {
		formEditCustomer.addEventListener('submit', async function (e) {
			e.preventDefault();

			const formData = new FormData(this);
			const customerId = formEditCustomer.getAttribute('data-customer-id');
			formData.append('edit_customer_id', customerId);

			try {
				const response = await fetch('api/update_customer.php', {
					method: 'POST',
					headers: { Accept: 'application/json' },
					body: formData
				});

				const data = await response.json();

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (banner && statusText && statusImage) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "images/sys-img/loading.gif";
					banner.style.display = 'block';
					banner.style.opacity = '1';
				}

				if (data.success) {
					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				}
			} catch (error) {
				console.error("Error updating customer:", error);
			}
		});
	}

	function resetCustomerPopupView() {
		const menuDiv = document.getElementById('customers-menu-buttons');
		const sectionsToHide = [
			document.getElementById('edit-customers-modal'),
			document.getElementById('assign-sale-section'),
			// puedes agregar más secciones aquí
		];

		menuDiv.style.display = 'block';
		menuDiv.style.opacity = '1';
		menuDiv.style.transform = 'scale(1)';

		sectionsToHide.forEach(section => {
			if (section) {
				section.style.display = 'none';
				section.style.opacity = '0';
				section.style.transform = 'scale(0.8)';
			}
		});
	}

	setupBackToMenuButton(
		'.edit-back-to-menu-btn',
		['assign-customers-sale-section', 'edit-customers-modal'],
		'customers-menu-buttons',
		'customers-options'
	);

	// 📌 script para edit customers form menu
	const editDataTab = document.getElementById('tab-edit-customer-data');
	const editReferenceTab = document.getElementById('tab-edit-customer-reference');

	const editDataSection = document.getElementById('edit-customer-data');
	const editReferenceSection = document.getElementById('edit-customer-reference');

	// Mostrar por defecto la sección de "edit data"
	if (editDataTab && editReferenceTab && editDataSection && editReferenceSection) {
		activateTab(editDataTab, editReferenceTab, editDataSection, editReferenceSection);

		editDataTab.addEventListener('click', () => {
			activateTab(editDataTab, editReferenceTab, editDataSection, editReferenceSection);
		});

		editReferenceTab.addEventListener('click', () => {
			activateTab(referenceTab, editDataTab, editReferenceSection, editDataSection);
		});
	}

	//############################################################# END CUSTOMERS ##################################################################

	//############################################################# SALES ##################################################################

	const salesContainer = document.getElementById('sales-list');
	const searchSalesField = document.getElementById('salesSearchField');

	if (salesContainer || searchSalesField) {
		async function fetchAndRenderSales() {
			try {
				const searchTerm = searchSalesField?.value.trim() || "";

				const params = new URLSearchParams();
				if (searchTerm) params.append('search', searchTerm);

				const res = await fetch(`api/get_sales.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});

				const data = await res.json();
				salesContainer.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(sale => {
						const row = document.createElement('div');
						row.className = 'sale-row';

						const customerImg = sale.customer.image?.trim() !== ''
							? `images/customers/${sale.customer.image}`
							: 'images/sys-img/NonProfilePic.png';

						const paymentDateFormatted = sale.payment_date
							? `Every ${new Date(sale.payment_date).getDate()}th`
							: '-';

						let productsHtml = '';
						if (!sale.products || sale.products.length === 0) {
							productsHtml = `
								<tr valign="baseline" class="form_height">
									<td width="100%" align="center" valign="middle">
										<p>No products in this sale...</p>
									</td>
								</tr>
							`;
						} else if (sale.products.length === 1) {
							const product = sale.products[0];
							const productImg = product.image?.trim() !== ''
								? `images/products/${product.image}`
								: 'images/sys-img/wooden-box.png';
						
							productsHtml = `
								<tr valign="baseline" class="form_height">
									<td width="30%" align="left" valign="middle">
										<div class="sale-product-pic">
											<img src="${productImg}" alt="product picture">
										</div>
									</td>
									<td width="80%" align="left" valign="middle">
										<p class="mini-title">Product No:</p>
										${product.name}
										<h3><strong>${product.mark_name} - ${product.model_name}</strong></h3>
										<p>${product.submodel_name}</p>
										<p class="mini-title">Year</p>
										<strong>${product.year}</strong>
									</td>
								</tr>
							`;
						} else {
							sale.products.forEach(product => {
								const productImg = product.image?.trim() !== ''
									? `images/products/${product.image}`
									: 'images/sys-img/wooden-box.png';
						
								productsHtml += `
									<tr valign="baseline" class="form_height">
										<td width="18%" align="left" valign="middle">
											<div class="sale-list-product-pic">
												<img src="${productImg}" alt="product picture">
											</div>
										</td>
										<td width="35%" align="left" valign="middle">
											<h3 style="margin: 0; padding: 0;"><strong>${product.mark_name} - ${product.model_name}</strong></h3>
											<p style="margin: 0; padding: 0;">${product.submodel_name}</p>
										</td>
										<td width="15%" align="left" valign="middle">
											<p class="mini-title">Year</p>
											<strong>${product.year}</strong>
										</td>
										<td width="12%" align="left" valign="middle">
											<p class="mini-title">Price</p>
											<strong>${product.prise}</strong>
										</td>
										<td width="20%" align="left" valign="middle">
											<p class="mini-title">Product No:</p>
											${product.name}
										</td>
									</tr>
								`;
							});
						}

						row.innerHTML = `
						<table width="100%" style="border-bottom: 1px solid #999;" align="center" cellspacing="0">
							<tr valign="baseline" class="form_height">
								<td width="10%" align="left" valign="middle">
									<p class="mini-title">Ord. No:</p>
									${sale.ord_no}
								</td>
								<td width="87%" align="center" valign="middle"></td>
								<td width="3%" align="center" valign="middle">
									<div class="sale-menu">
										<img src="images/sys-img/hamburger-menu-icon.png" alt="menu">
									</div>
								</td>
							</tr>
						</table>
						<div class="flex" style="width: 100%; margin-top: 5px;">
							<table width="30%" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td width="30%" align="left" valign="middle">
										<div class="sale-profile">
											<img src="${customerImg}" alt="profile picture">
										</div>
									</td>
									<td width="70%" align="left" valign="middle">
										<h3><strong>${sale.customer.full_name}</strong></h3>
										<p class="mini-title">${sale.customer.document_type}:</p>
										${sale.customer.document_no}<br><br>
										<p class="mini-title">Phone:</p>
										${sale.customer.phone}
									</td>
								</tr>
							</table>
							<table width="40%" style="border-left: 1px solid #999;" align="center" cellspacing="0">
								${productsHtml}
							</table>
							<table width="30%" style="border-left: 1px solid #999;" align="center" cellspacing="0">
								<tr valign="baseline" class="form_height">
									<td colspan="2" style="padding-left: 7px;" align="left" valign="middle"><strong>Method of Payment</strong></td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td width="35%" align="right">Price :</td><td width="65%" style="padding-left: 5px;">${sale.price_sum}</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td align="right">Initial :</td><td style="padding-left: 5px;">${sale.initial}</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td align="right">Delivery date :</td><td style="padding-left: 5px;">${sale.delivery_date}</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td align="right">Remaining :</td><td style="padding-left: 5px;">${sale.remaining}</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td align="right">Interest :</td><td style="padding-left: 5px;">${sale.interest}</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td align="right">Installments / month :</td><td style="padding-left: 5px;">${sale.no_installments} / ${sale.installments_month}</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td align="right">Payment date :</td><td style="padding-left: 5px;">${paymentDateFormatted}</td>
								</tr>
								<tr valign="baseline" class="form_height">
									<td align="right">Due :</td><td style="padding-left: 5px;">${sale.due}</td>
								</tr>
							</table>
						</div>`;

						salesContainer.appendChild(row);
					});
				} else {
					salesContainer.innerHTML = `<p style="text-align:center;">No sales found.</p>`;
				}
			} catch (err) {
				console.error("Error loading sales:", err);
				salesContainer.innerHTML = `<p style="text-align:center;">Error loading sales</p>`;
			}
		}

		searchSalesField?.addEventListener('keyup', fetchAndRenderSales);
		fetchAndRenderSales();
	}

	// 📌 script para add category popup
	let addSaleBtn = document.getElementById('add-sale-btn');
	if (addSaleBtn) {
		addSaleBtn.addEventListener('click', function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const addSaleForm = document.getElementById('add-sale-form');
			const popupContent = addSaleForm.querySelector('.formular-big-frame');

			if (addSaleForm && popupContent) {
				addSaleForm.style.display = 'block';
				addSaleForm.style.opacity = '0';
				addSaleForm.style.transition = 'opacity 0.5s ease';
				setTimeout(() => {
					addSaleForm.style.opacity = '1';
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
	}

	const searchCustomerInput = document.getElementById('search-customer');
	const customerListTable = document.getElementById('select-customers-list');

	if (searchCustomerInput && customerListTable) {
		async function fetchAndRenderCustomers(search = "") {
			try {
				const params = new URLSearchParams();
				if (search.trim() !== "") {
					params.append('search', search.trim());
				}

				const response = await fetch(`api/get_customers.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await response.json();
				customerListTable.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(customer => {
						const uniqueId = `customer-${customer.customer_id}`;
						const row = document.createElement('tr');
						row.className = "categoryContainer";

						const profileImg = customer.image && customer.image.trim() !== ""
							? `images/customers/${customer.image}`
							: `images/sys-img/NonProfilePic.png`;

						row.innerHTML = `
							<td width="10%" align="center" valign="middle">
								<div class="customers-profile">
									<img src="${profileImg}" alt="">
								</div>
							</td>
							<td width="80%" valign="middle" style="padding-left:10px;">
								<strong>${customer.full_name}</strong>
								<p class="mini-title" style="color: #000;">${customer.document_type}: <strong>${customer.document_no}</strong></p>
							</td>
							<td width="10%" align="center" valign="middle">
								<div class="opcion-radio">
									<input type="radio" id="${uniqueId}" name="customer_select" class="category-radio" data-id="${customer.customer_id}" />
									<label for="${uniqueId}"></label>
								</div>
							</td>
						`;
						customerListTable.appendChild(row);
					});
				} else {
					customerListTable.innerHTML = `
						<tr><td colspan="3" style="text-align:center; padding: 10px;">No customers found.</td></tr>
					`;
				}
			} catch (error) {
				console.error("Error loading customers:", error);
				customerListTable.innerHTML = `
					<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading customers</td></tr>
				`;
			}
		}

		searchCustomerInput.addEventListener('input', () => {
			fetchAndRenderCustomers(searchCustomerInput.value);
		});

		fetchAndRenderCustomers();
	}

	const searchProductInput = document.getElementById('search-product-purchase');
	const saleMarkSelect = document.getElementById('search-product-mark');
	const productListTable = document.getElementById('select-product-list');

	if ((searchProductInput || saleMarkSelect) && productListTable) {
		async function fetchAndRenderProducts(search = "", mark = "") {
			try {
				const params = new URLSearchParams();
				if (search.trim() !== "") {
					params.append('search', search.trim());
				}
				if (mark && mark !== "") {
					params.append('mark', mark);
				}

				const response = await fetch(`api/get_products.php?${params.toString()}`, {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				const data = await response.json();
				productListTable.innerHTML = "";

				if (data.success && data.data.length > 0) {
					data.data.forEach(product => {
						const uniqueId = `product-${product.product_id}`;
						const productImg = product.product_image && product.product_image.trim() !== ''
							? `images/products/${product.product_image}`
							: `images/sys-img/wooden-box.png`;

						const row = document.createElement('tr');
						row.className = "productContainer";
						row.innerHTML = `
							<td width="10%" align="center" valign="middle">
								<div class="list-icon">
									<img src="${productImg}" alt="product image" width="32" height="32">
								</div>
							</td>
							<td width="75%" valign="middle" style="padding-left:10px;">
								${product.product_name}<br>
								<small>${product.mark_name || ''} - ${product.model_name || ''} ${product.submodel_name || ''}</small>
							</td>
							<td width="15%" align="center" valign="middle">
								<div class="opcion-checkbox">
									<input type="checkbox" id="${uniqueId}" name="product_selection[]" value="${product.product_id}" data-price="${product.prise}" class="product-checkbox" />
									<label for="${uniqueId}"></label>
								</div>
							</td>
						`;
						productListTable.appendChild(row);
						document.getElementById(uniqueId).addEventListener('change', calculatePriceSum);
					});
				} else {
					productListTable.innerHTML = `
						<tr><td colspan="3" style="text-align:center; padding: 10px;">No products found.</td></tr>
					`;
				}
			} catch (error) {
				console.error("Error loading products:", error);
				productListTable.innerHTML = `
					<tr><td colspan="3" style="text-align:center; padding: 10px;">Error loading products</td></tr>
				`;
			}
		}

		// 🔹 Cargar marcas
		async function loadMarks() {
			try {
				const response = await fetch("api/get_categories.php", {
					method: "GET",
					headers: { "Accept": "application/json" }
				});
				const data = await response.json();

				saleMarkSelect.innerHTML = `<option value="">All Marks</option>`;

				if (data.success && data.data.length > 0) {
					data.data.forEach(category => {
						const option = document.createElement("option");
						option.value = category.category_id;
						option.textContent = category.category_name;
						saleMarkSelect.appendChild(option);
					});
				} else {
					saleMarkSelect.innerHTML += `<option value="">No marks found</option>`;
				}
			} catch (error) {
				console.error("Error loading marks:", error);
				saleMarkSelect.innerHTML = `<option value="">Error loading marks</option>`;
			}
		}

		searchProductInput.addEventListener('input', () => {
			fetchAndRenderProducts(searchProductInput.value, saleMarkSelect.value);
		});
		saleMarkSelect.addEventListener('change', () => {
			fetchAndRenderProducts(searchProductInput.value, saleMarkSelect.value);
		});

		await loadMarks();
		fetchAndRenderProducts();
	}

	function calculatePriceSum() {
		const checkboxes = document.querySelectorAll('.product-checkbox:checked');
		let total = 0;
	
		checkboxes.forEach(cb => {
			const price = parseFloat(cb.getAttribute('data-price')) || 0;
			total += price;
		});
	
		document.getElementById('price_sum').value = total.toFixed(2);

		calculateRemaining();
		calculateInterest();
	}

	function calculateRemaining() {
		const priceSum = parseFloat(document.getElementById('price_sum').value.replace(/,/g, '')) || 0;
		const initial = parseFloat(document.getElementById('initial').value.replace(/,/g, '')) || 0;
		const remaining = priceSum - initial;
	
		document.getElementById('remaining').value = remaining.toFixed(2);
		calculateDue();
	}

	function calculateInterest() {
		const priceSum = parseFloat(document.getElementById('remaining').value.replace(/,/g, '')) || 0;
		const interestPercent = parseFloat(document.getElementById('interest').value) || 0;
	
		const totalInterest = (priceSum * interestPercent) / 100;
		document.getElementById('total_interest').value = totalInterest.toFixed(2);
		calculateDue();
	}

	function calculateDue() {
		const remaining = parseFloat(document.getElementById('remaining').value.replace(/,/g, '')) || 0;
		const totalInterest = parseFloat(document.getElementById('total_interest').value.replace(/,/g, '')) || 0;
		const due = remaining + totalInterest;
	
		document.getElementById('due').value = due.toFixed(2);
	}

	document.getElementById('initial').addEventListener('input', calculateRemaining);
	document.getElementById('interest').addEventListener('input', calculateInterest);

	document.querySelector('#formAddSale').addEventListener('submit', function (e) {
		e.preventDefault();
		(async () => {
			try {
				const formatDecimal = val => parseFloat((val || '').toString().replace(',', '').trim()) || 0;

				const customerId = document.querySelector('input[name="customer_select"]:checked')?.dataset.id;
				const priceSum = formatDecimal(document.getElementById('price_sum').value);
				const initial = formatDecimal(document.getElementById('initial').value);
				const deliveryDate = document.getElementById('delivery_date').value;
				const remaining = formatDecimal(document.getElementById('remaining').value);
				const interest = parseInt(document.getElementById('interest').value) || 0;
				const installmentsMonth = parseInt(document.getElementById('installments_month').value) || 0;
				const noInstallments = installmentsMonth;
				const paymentDate = document.getElementById('payment_date').value;
				const due = formatDecimal(document.getElementById('due').value);
		
				// Validación mínima
				if (!customerId) throw new Error("Select a customer");
		
				const products = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => {
					const productId = cb.value;
					const price = parseFloat(cb.dataset.price) || 0;
		
					return {
						product_id: productId,
						price: price,
						quantity: 1,
						total: price
					};
				});
		
				if (products.length === 0) throw new Error("Select at least one product");
		
				const payload = {
					customer_id: parseInt(customerId),
					price_sum: priceSum,
					initial: initial,
					delivery_date: deliveryDate,
					remaining: remaining,
					interest: interest,
					installments_month: installmentsMonth,
					no_installments: noInstallments,
					payment_date: paymentDate,
					due: due,
					products: products
				};
		
				const res = await fetch('api/create_sale.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
		
				const data = await res.json();

				let banner = document.getElementById('status-message');
				let statusText = document.getElementById('status-text');
				let statusImage = document.getElementById('status-image');

				if (banner && statusText && statusImage) {
					statusText.innerText = data.message || "Unknown response";
					statusImage.src = data.img_gif || "images/sys-img/success.gif";
					banner.style.display = 'block';
					banner.style.opacity = '1';
				}
		
				if (data.success) {
					document.getElementById('formAddSale').reset();
					document.getElementById('price_sum').value = '';
					document.getElementById('remaining').value = '';
					document.getElementById('total_interest').value = '';
					document.getElementById('due').value = '';
					document.querySelectorAll('.product-checkbox:checked').forEach(cb => cb.checked = false);
					const selectedCustomer = document.querySelector('input[name="customer_select"]:checked');
					if (selectedCustomer) selectedCustomer.checked = false;

					setTimeout(() => {
						banner.style.opacity = '0';
						setTimeout(() => {
							window.location.href = data.redirect_url || window.location.href;
						}, 1000);
					}, 3000);
				} else {
					alert("Failed: " + result.message);
				}
			} catch (error) {
				alert("Error: " + error.message);
			}
		})();
	});

	//############################################################# END SALES ##################################################################

	//############################################################# FUNCTIONES ##################################################################

	// 📌 cerrar al hacer clic fuera del formulario
	function handlePopupClose(popupId, contentSelector, otherPopups = []) {
		const popup = document.getElementById(popupId);
	
		if (popup) {
			const popupContent = popup.querySelector(contentSelector);
			if (!popupContent) return;

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
	handlePopupClose("edit-members-form", ".formular-frame", ["edit-members-form"]);
	handlePopupClose("add-product-form", ".formular-frame", ["add-product-form"]);
	handlePopupClose("add-category-form", ".formular-big-frame", ["add-category-form"]);
	handlePopupClose("product-options", ".formular-frame", ["product-options"]);
	handlePopupClose("add-customers-form", ".formular-frame", ["add-customers-form"]);
	handlePopupClose("customers-options", ".formular-frame", ["customers-options"]);
	handlePopupClose("add-sale-form", ".formular-big-frame", ["add-sale-form"]);

	// 📌 script para cargar marcas, modelos y submodelos
	async function initCategorySelectors(markId, modelId, submodelId) {
		const markSelect = document.getElementById(markId);
		let modelSelect = document.getElementById(modelId);
		let submodelSelect = document.getElementById(submodelId);
	
		if (!markSelect || !modelSelect || !submodelSelect) return;
	
		// 🔹 Cargar marcas
		try {
			const response = await fetch("api/get_categories.php", {
				method: "GET",
				headers: { "Accept": "application/json" }
			});
			const data = await response.json();
	
			markSelect.innerHTML = `<option value="">All Marks</option>`;
	
			if (data.success && data.data.length > 0) {
				data.data.forEach(category => {
					const option = document.createElement("option");
					option.value = category.category_id;
					option.textContent = category.category_name;
					markSelect.appendChild(option);
				});
			} else {
				markSelect.innerHTML += `<option value="">No marks found</option>`;
			}
		} catch (error) {
			console.error("Error loading marks:", error);
			markSelect.innerHTML = `<option value="">Error loading marks</option>`;
		}
	
		// 🔹 Reemplazar modelo (por si venía como input) y configurar
		const modelSelectCloned = document.createElement('select');
		modelSelectCloned.name = modelId;
		modelSelectCloned.id = modelId;
		modelSelectCloned.className = 'form-medium-input-style';
		modelSelectCloned.disabled = true;
		modelSelectCloned.innerHTML = `<option value="">Select Model</option>`;
		modelSelect.parentNode.replaceChild(modelSelectCloned, modelSelect);
		modelSelect = modelSelectCloned;
	
		// 🔹 Reemplazar submodelo y configurar
		const submodelSelectCloned = document.createElement('select');
		submodelSelectCloned.name = submodelId;
		submodelSelectCloned.id = submodelId;
		submodelSelectCloned.className = 'form-medium-input-style';
		submodelSelectCloned.disabled = true;
		submodelSelectCloned.innerHTML = `<option value="">Select Submodel</option>`;
		submodelSelect.parentNode.replaceChild(submodelSelectCloned, submodelSelect);
		submodelSelect = submodelSelectCloned;
	
		// 🔹 Evento: Al cambiar Marca → cargar Modelos
		markSelect.addEventListener('change', () => {
			const markId = markSelect.value;
			modelSelect.innerHTML = `<option value="">Select Model</option>`;
			modelSelect.disabled = !markId;
	
			// Reset submodel también
			submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;
			submodelSelect.disabled = true;
	
			if (!markId) return;
	
			fetch(`api/get_sub_categories.php?mark_id=${markId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			})
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data.length > 0) {
					data.data.forEach(category => {
						const option = document.createElement("option");
						option.value = category.category_id;
						option.textContent = category.category_name;
						modelSelect.appendChild(option);
					});
				} else {
					modelSelect.innerHTML += `<option value="">No models found</option>`;
				}
			})
			.catch(error => {
				console.error("Error loading models:", error);
			});
		});
	
		// 🔹 Evento: Al cambiar Modelo → cargar Submodelos
		modelSelect.addEventListener('change', () => {
			const modelId = modelSelect.value;
			submodelSelect.innerHTML = `<option value="">Select Submodel</option>`;
			submodelSelect.disabled = !modelId;
	
			if (!modelId) return;
	
			fetch(`api/get_sub_models.php?model_id=${modelId}`, {
				method: 'GET',
				headers: { 'Accept': 'application/json' }
			})
			.then(res => res.json())
			.then(data => {
				if (data.success && data.data.length > 0) {
					data.data.forEach(submodel => {
						const option = document.createElement('option');
						option.value = submodel.category_id;
						option.textContent = submodel.category_name;
						submodelSelect.appendChild(option);
					});
				} else {
					submodelSelect.innerHTML += `<option value="">No submodels found</option>`;
				}
			})
			.catch(error => {
				console.error("Error loading submodels:", error);
			});
		});
	}

	async function populateVehicleTypes(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;
	
		// 🔹 Limpiar contenido actual del <select>
		select.innerHTML = '';
	
		// 🔹 Agregar opción por defecto
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Type';
		select.appendChild(defaultOption);
	
		try {
			const res = await fetch('api/get_global_array.php?key=vehicleTypes');
			const data = await res.json();
	
			if (data.success && data.data) {
				for (const [value, label] of Object.entries(data.data)) {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = label;
					if (String(value) === String(selectedValue)) {
						option.selected = true;
					}
					select.appendChild(option);
				}
			} else {
				select.innerHTML += `<option value="">No vehicle types found</option>`;
			}
		} catch (error) {
			console.error("Error loading vehicle types:", error);
			select.innerHTML += `<option value="">Error loading vehicle types</option>`;
		}
	}


	function showConfirmModal(title, message, onConfirm) {
		const modal = document.getElementById('globalConfirmModal');
		const modalTitle = document.getElementById('confirm-modal-title');
		const modalMessage = document.getElementById('confirm-modal-message');
		const cancelBtn = document.getElementById('modalCancelBtn');
		const confirmBtn = document.getElementById('modalConfirmBtn');

		if (!modal || !modalTitle || !modalMessage || !cancelBtn || !confirmBtn) return;

		modalTitle.textContent = title || "Confirm Action";
		modalMessage.textContent = message || "Are you sure you want to proceed?";
		modal.style.display = 'flex';

		// Reset listeners
		const newConfirmBtn = confirmBtn.cloneNode(true);
		confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

		// Confirmación
		newConfirmBtn.addEventListener('click', () => {
			modal.style.display = 'none';
			if (typeof onConfirm === 'function') onConfirm();
		});

		// Cancelar
		cancelBtn.onclick = () => {
			modal.style.display = 'none';
		};
	}

	function showAlertModal(title, message) {
		const modal = document.getElementById('globalOkModal');
		const modalTitle = document.getElementById('alert-modal-title');
		const modalMessage = document.getElementById('alert-modal-message');
		const confirmBtn = document.getElementById('modalOkBtn');
	
		if (!modal || !modalTitle || !modalMessage || !confirmBtn) return;
	
		modalTitle.textContent = title || "Notice";
		modalMessage.textContent = message || "";
	
		modal.style.display = 'flex';
	
		// Clonar y reemplazar para quitar listeners previos
		const newConfirmBtn = confirmBtn.cloneNode(true);
		confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
	
		newConfirmBtn.textContent = 'OK';
		newConfirmBtn.onclick = () => {
			modal.style.display = 'none';
		};
	}

	function setupBackToMenuButton(buttonSelector, divsToHide = [], menuDivId = '', optionsDivId = '') {
		const buttons = document.querySelectorAll(buttonSelector);
		if (!buttons.length) return;
	
		buttons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
	
				const menuDiv = document.getElementById(menuDivId);
				const optionsDiv = optionsDivId ? document.getElementById(optionsDivId) : null;
	
				let anyDivShown = false;
	
				divsToHide.forEach(divId => {
					const div = document.getElementById(divId);
					if (div && div.style.display === 'block') {
						anyDivShown = true;
						fadeOutAndHide(div, () => {
							if (menuDiv) showWithFadeIn(menuDiv);
						});
					}
				});
	
				if (!anyDivShown && menuDiv) {
					showWithFadeIn(menuDiv);
				}
	
				if (optionsDiv) {
					optionsDiv.style.display = 'block';
				}
			});
		});
	}

	function fadeOutAndHide(element, callback) {
		element.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
		element.style.opacity = '1';
		element.style.transform = 'scale(1)';
	
		setTimeout(() => {
			element.style.opacity = '0';
			element.style.transform = 'scale(0.8)';
			setTimeout(() => {
				element.style.display = 'none';
				if (callback) callback();
			}, 400);
		}, 10);
	}
	
	function showWithFadeIn(element) {
		element.style.display = 'block';
		element.style.opacity = '0';
		element.style.transform = 'scale(0.8)';
		element.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
	
		setTimeout(() => {
			element.style.opacity = '1';
			element.style.transform = 'scale(1)';
		}, 50);
	}

	function activateTab(activeTab, inactiveTab, showSection, hideSection) {
		activeTab.classList.add('tab-active');
		inactiveTab.classList.remove('tab-active');

		showSection.style.display = 'block';
		hideSection.style.display = 'none';
	}

	document.querySelectorAll('.input-year-only').forEach(input => {
		input.addEventListener('input', () => {
			let year = input.value.replace(/\D/g, ''); // eliminar todo lo que no sea número
			if (year.length > 4) year = year.slice(0, 4); // limitar a 4 dígitos
			input.value = year;
		});
	});

//############################################################# END FUNCTIONES ##################################################################

});