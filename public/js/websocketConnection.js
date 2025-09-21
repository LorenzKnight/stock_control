document.addEventListener("DOMContentLoaded", async function () {
	const headerMenu = document.getElementById("header-menu");
	if (headerMenu) {
		let currentUserId = 0;

		let response = await fetch('api/get_my_info.php', {
			method: 'GET',
			headers: { Accept: "application/json" }
		});

		let data = await response.json();
		if (data.success && data.data) {
			let user = data.data;
			currentUserId = parseInt(user.user_id) || 0;
		}

		// PRODUCCIÓN (Nginx proxya a ws://
		// const wsProtocol = location.protocol === 'https:' ? 'wss' : 'ws';
		// const wsUrl = `${wsProtocol}://${location.host}/ws`; // sin puertos; Nginx proxya a 3001
		// const socket = new WebSocket(wsUrl);

		// LOCAL (para desarrollo, sin Nginx)
		const socket = new WebSocket(`ws://${location.hostname}:3001`);

		socket.addEventListener('open', () => {
			console.log('✅ WS conected');
		});

		socket.addEventListener('message', async event => {
			let data;
			try {
				data = JSON.parse(event.data);
			} catch (e) {
				console.error('[WS] JSON inválido:', event.data);
				return;
			}

			if (data.type !== 'notification') return;

			const me = Number(currentUserId);

			let matchesMe = Number.isFinite(Number(data.to_user_id)) && Number(data.to_user_id) === me;

			if (!matchesMe && Array.isArray(data.to_user_id)) {
				matchesMe = data.to_user_id.map(Number).includes(me);
			}
			if (!matchesMe && (data.to_user_id === null || data.to_user_id === 'all')) {
				matchesMe = true;
			}

			// console.log('[WS] incoming notification', data, { me, matchesMe });
			if (!matchesMe) return;

			const message = data.message;
			const notifType = data.notification_type || 'General';
			const link = data.link;

			const container = document.getElementById('notification-container');
			if (!container) {
				console.warn('[WS] #notification-container no encontrado');
				return;
			}

			const box = document.createElement('div');
			box.classList.add('notification-box');

			box.innerHTML = `
				<div class="notification-title">${notifType}</div>
				<div class="notification-message">${message}</div>
			`;

			if (link) {
				box.style.cursor = 'pointer';
				box.addEventListener('click', () => {
					window.location.href = link;
				});
			}

			container.appendChild(box);

			// Forzar reflow para que la transición funcione
			void box.offsetWidth;
			box.classList.add('show');

			await checkNotifications();

			// Eliminar después de 5 segundos
			setTimeout(() => {
				box.classList.remove('show');
				setTimeout(() => container.removeChild(box), 300); // coincide con el transition
			}, 10000);
		});

		socket.addEventListener('close', () => {
			console.warn('⚠️ WS disconnected');
		});

		socket.addEventListener('error', error => {
			console.error('❌ Error in WebSocket:', error);
		});
	}

	async function checkNotifications() {
		const notifCount = document.getElementById('notif-count');
		if (!notifCount) return;

		try {
			const response = await fetch('api/get_notifications.php', {
				method: 'POST',
				headers: { 'Accept': 'application/json' },
			});

			const data = await response.json();
			const count = (data.success && typeof data.count !== 'undefined') ? parseInt(data.count, 10) : 0;

			notifCount.textContent = count > 0 ? count : '';
			notifCount.style.display = count > 0 ? 'inline-block' : 'none';

		} catch (error) {
			console.error("❌ Error getting notifications:", error);
			notifCount.textContent = '';
			notifCount.style.display = 'none';
		}
	} checkNotifications();
});