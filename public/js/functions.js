document.addEventListener("DOMContentLoaded", async function () {
    async function showBanner(bannerEl) {
		if (!bannerEl) return;
		bannerEl.classList.remove('hide');
		bannerEl.style.display = 'block';
		void bannerEl.offsetWidth;
		requestAnimationFrame(() => bannerEl.classList.add('show'));
	}
    window.showBanner = showBanner;

	async function hideBanner(bannerEl, onDone) {
		if (!bannerEl) return;

		bannerEl.classList.remove('show');
		void bannerEl.offsetWidth;
		bannerEl.classList.add('hide');

		const onEnd = (e) => {
			if (e.animationName !== 'slideUpOver') return;
			bannerEl.style.display = 'none';
			bannerEl.classList.remove('hide');
			bannerEl.removeEventListener('animationend', onEnd);
			if (typeof onDone === 'function') onDone();
		};

		bannerEl.addEventListener('animationend', onEnd);
	}
    window.hideBanner = hideBanner;

	function getUserBorderColor(user) {
		if (Number(user.status_by_admin) !== 1) {
			return "#9a9999"; // Gris: bloqueado por admin
		}

		if (Number(user.status) !== 1) {
			return "#fe7070"; // Rojo: inactivo
		}

		if (Number(user.verified) !== 1) {
			return "#fad186"; // Amarillo: no verificado
		}

		if (Number(user.package_id) === 1 && user.signup_date) {
			const signupDate = new Date(user.signup_date);
			const now = new Date();
			const diffMs = now - signupDate;
			const diffDays = diffMs / (1000 * 60 * 60 * 24);

			if (diffDays >= 15) {
				return "#4c0bbd"; // Morado: 15+ días con package_id 1
			}
		}

		return "#8cda8a"; // Verde: activo y verificado
	}
	window.getUserBorderColor = getUserBorderColor;

	// 📌 formatear fecha y hora completa
	function formatFullDateTime(dateString) {
		const monthsAbbr = [
			"Jan", "Feb", "Mar", "Apr", "May", "Jun",
			"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
		];

		if (!dateString || String(dateString).trim() === "") {
			return "No date available";
		}

		const date = new Date(dateString);

		if (isNaN(date.getTime())) {
			return "Invalid date";
		}

		const year = date.getFullYear();
		const month = monthsAbbr[date.getMonth()];
		const day = String(date.getDate()).padStart(2, '0');
		const hours = String(date.getHours()).padStart(2, '0');
		const minutes = String(date.getMinutes()).padStart(2, '0');

		return `${year} ${month} ${day} ${hours}:${minutes}`;
	}
  	window.formatFullDateTime = formatFullDateTime;

	async function populateCompanies(selectId, selectedValue = '') {
		const select = document.getElementById(selectId);
		if (!select) return;

		// 🔹 Limpiar el contenido actual del <select>
		select.innerHTML = '';

		// 🔹 Agregar opción por defecto
		const defaultOption = document.createElement('option');
		defaultOption.value = '';
		defaultOption.textContent = 'Select a Company';
		select.appendChild(defaultOption);

		try {
			const res = await fetch('api/get_company_info.php');
			const data = await res.json();

			if (data.success && Array.isArray(data.data) && data.data.length > 0) {
				data.data.forEach((company, index) => {
					const option = document.createElement('option');
					option.value = company.company_id;
					option.textContent = company.company_name;

					if (selectedValue) {
						if (String(company.company_id) === String(selectedValue)) {
							option.selected = true;
						}
					} else if (index === 0) {
						option.selected = true;
					}

					select.appendChild(option);
				});

				select.dispatchEvent(new Event('change'));
			} else {
				select.innerHTML += `<option value="">No companies found</option>`;
			}
		} catch (error) {
			console.error("Error loading companies:", error);
			select.innerHTML += `<option value="">Error loading companies</option>`;
		}
	}
	window.populateCompanies = populateCompanies;

});