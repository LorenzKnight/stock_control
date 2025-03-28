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
					: "../images/profile/NonProfilePic.png";
			
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

	// Roles disponibles
	const ranks = {
		1: "Administrator (Full Access)",
		2: "Manager (Manage teams/data)",
		3: "Supervisor (Oversee operations)",
		4: "Operator (Limited access)",
		5: "Viewer (Read-only access)"
	};

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
					: "images/profile/NonProfilePic.png";

					let borderColor = Number(user.status) === 1 ? "#8cda8a" : "#fbadad";

					card.innerHTML = `
						<div class="mini-banner">
							<div class="mini-profile" style="border: 2px solid ${borderColor};">
								<img src="${profileImage}" alt="Profile Picture">
							</div>
							<div class="co-worker-position">${ranks[user.rank] || 'Unknown role'}</div>
						</div>
						<div class="card-info">
							<h3>${user.name} ${user.surname}</h3>
							<p><strong>Email:</strong> ${user.email}</p>
							<p><strong>Phone:</strong> ${user.phone ? user.phone : "No Phone Number"}</p>
						</div>
						<div class="card-menu">
							<img src="images/sys-img/edit-icon.png" alt="eidt-card">
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
				document.getElementById('edit_name').value = user.name || '';
				document.getElementById('edit_surname').value = user.surname || '';
				document.getElementById('edit_birthday').value = user.birthday ? user.birthday.split(" ")[0] : '';
				document.getElementById('edit_phone').value = user.phone || '';
				document.getElementById('edit_email').value = user.email || '';

				populateRankSelect('edit_rank', user.rank);
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

	// 📌 Boton para cerrar formulario
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

	initDragAndDrop('drop-area', 'company_logo', 'logo-preview');


	// Función para llenar el <select>
	function populateRankSelect(selectId, selectedValue = '') {
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

				populateRankSelect('rank');
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

				// populateRankSelect('rank');
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

	initDragAndDrop('drop-product-area', 'Product_image', 'product-image-preview');


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

				// populateRankSelect('rank');
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

});