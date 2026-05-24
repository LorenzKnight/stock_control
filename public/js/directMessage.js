document.addEventListener("DOMContentLoaded", async function () {
//############################################################# DIRECT MESSAGE ##################################################################
	// 📌 script para direct message popup
	let startDirectMessageBtn = document.getElementById('startDirectMessage');
	if (startDirectMessageBtn) {
		startDirectMessageBtn.addEventListener('click', async function (e) {
			e.preventDefault();

			scrollToTopIfNeeded();

			const editCompanyForm = document.getElementById('add-direct-message-form');
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
				}, 50);
			
				initDMUsersList();

				handlePopupClose("add-direct-message-form", ".formular-frame", []);
			}
		});
	}

	window.selectedDMUserId = null;
	function initDMUsersList() {
		const searchUserInput = document.getElementById('search-dm-users');
		const userListTable = document.getElementById('select-dm-users-list');
		const CURRENT_USER_ID = window.currentUserId || null;

		if (!searchUserInput || !userListTable) return;

		async function fetchAndRenderUsers(search = "") {
			try {
				const params = new URLSearchParams();
				params.append('include_parent', '1');

				if (search.trim() !== "") {
					params.append('search', search.trim());
				}

				const response = await fetch(`api/get_users.php?${params.toString()}`, {
					headers: { 'Accept': 'application/json' }
				});

				const data = await response.json();
				userListTable.innerHTML = "";

				if (data.success && Array.isArray(data.users) && data.users.length > 0) {
					data.users.forEach(user => {

						if (CURRENT_USER_ID && Number(user.user_id) === Number(CURRENT_USER_ID)) return;

						const row = document.createElement('tr');
						row.className = "dm-user-row";

						const profileImg = user.image
							? `images/profile/${user.image}`
							: `images/sys-img/NonProfilePic.png`;

						row.innerHTML = `
							<td width="10%" align="center">
								<div class="message-user-profile-pic">
									<img src="${profileImg}">
								</div>
							</td>
							<td width="80%" valign="middle" style="padding-left:10px;">
								<P><strong>${user.full_name || user.username}</strong></P>
								<p>${user.email || ''}</p>
							</td>
							<td width="10%" align="center">
								<input type="radio" name="user_select" data-id="${user.user_id}">
							</td>
						`;

						row.addEventListener('click', () => {
							document.querySelectorAll('.dm-user-row')
								.forEach(r => r.classList.remove('selected-dm-user'));
							row.classList.add('selected-dm-user');

							window.selectedDMUserId = Number(user.user_id);
							// console.log("Selected DM User ID:", window.selectedDMUserId);
						});

						userListTable.appendChild(row);
					});
				} else {
					userListTable.innerHTML = `
						<tr><td colspan="3" align="center">No users found</td></tr>
					`;
				}
			} catch (e) {
				console.error("Error loading DM users:", e);
			}
		}

		searchUserInput.addEventListener('input', () => {
			fetchAndRenderUsers(searchUserInput.value);
		});

		fetchAndRenderUsers();
	}

	const dmForm = document.getElementById('formDirectMessage');
	if (dmForm) {
		dmForm.addEventListener('submit', function (e) {
			e.preventDefault();

			if (!window.selectedDMUserId) {
				alert("Please select a user first.");
				return;
			}

			const targetUserId = window.selectedDMUserId;

			// Cerrar popup
			const popup = document.getElementById('add-direct-message-form');
			if (popup) popup.style.display = 'none';

			// 👉 Inicializar vista de chat
			(async () => {
				try {
					const res = await fetch('api/get_notifications.php', {
						method: 'GET',
						headers: { 'Accept': 'application/json' }
					});

					const data = await res.json();
					if (!data.success) throw new Error('Cannot load notifications');

					// 🔎 Buscar chat existente
					const existingChat = data.data.find(n =>
						n.notification_type === 'Direct Message' &&
						(
							Number(n.from_user_id) === targetUserId ||
							Number(n.to_user_id) === targetUserId
						)
					);

					if (existingChat) {
						// ✅ Chat ya existe → reutilizar
						localStorage.setItem('activeDMUserId', targetUserId);
						localStorage.setItem('activeDMNotificationId', existingChat.notification_id);
						localStorage.setItem('dmAutoSelect', '1');
					} else {
						// 🆕 Chat nuevo → sin notification_id aún
						localStorage.setItem('activeDMUserId', targetUserId);
						localStorage.removeItem('activeDMNotificationId');
						localStorage.setItem('dmAutoSelect', '1');
					}

					if (typeof window.fetchAndRenderNotifications === 'function') {
						await window.fetchAndRenderNotifications();
					}

					openDirectMessageChat();

				} catch (e) {
					console.error('Error resolving DM chat:', e);
				} finally {
					window.selectedDMUserId = null;
				}
			})();
		});
	}

	async function openDirectMessageChat() {
		const toUserId = Number(localStorage.getItem('activeDMUserId'));
		const notifId  = Number(localStorage.getItem('activeDMNotificationId')) || null;

		if (!toUserId) return;

		const prevChatUser = Number(localStorage.getItem('activeDMChatUserId'));

		if (prevChatUser !== toUserId) {
			localStorage.removeItem('activeChatId');
			localStorage.setItem('activeDMChatUserId', toUserId);
		}

		const detailsDiv = document.getElementById('notifications-details');
		if (!detailsDiv) return;

		// Vista base (sin nombre aún)
		detailsDiv.innerHTML = `
		<div>
			<table class="message-details" width="90%" align="center" cellspacing="0" style="margin-top:15px;">
				<tr valign="baseline" id="dm-header">
					<td colspan="3" style="border-bottom: 1px solid #ccc; padding:10px;">
						<strong>Loading...</strong>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="3" align="center" valign="middle">
						<div class="message-content-box" id="dm-messages-box">
							<p style="opacity:.6; text-align:center;">Start the conversation</p>
						</div>
					</td>
				</tr>
				<tr valign="baseline">
					<td colspan="3" align="center" valign="middle">
						<div class="message-reply-box">
							<input 
								type="text"
								id="replyMessageField"
								class="form-input-style"
								placeholder="${window.i18n?.message || 'Message'}..."
							/>
							<button id="sendMessageBtn" class="button-style-agree" style="margin-left:10px; width: 80px;">${window.i18n?.send || 'Send'}</button>
						</div>
					</td>
				</tr>
			</table>
		</div>
		`;

		attachDMInputHandler();

		try {
			const res = await fetch(`api/get_notifications.php`, {
				headers: { 'Accept': 'application/json' }
			});

			const data = await res.json();
			if (!data.success) return;

			let resolvedNotifId = notifId;
	
			const header = document.getElementById('dm-header');
			if (!header) return;

			// 🆕 CHAT NUEVO (sin notificación aún)
			if (!resolvedNotifId) {
				const userRes = await fetch(`api/get_users.php?id=${toUserId}`, {
					headers: { 'Accept': 'application/json' }
				});
				const userData = await userRes.json();

				if (userData.success && userData.user) {
					const u = userData.user;

					const img = u.image
						? `images/profile/${u.image}`
						: `images/sys-img/NonProfilePic.png`;

					header.innerHTML = `
						<td width="5%" align="center" valign="middle" style="border-bottom: 1px solid #ccc;">
							<div class="message-user-profile-pic">
								<img src="${img}" alt="Profile Pic">
							</div>
						</td>
						<td width="45%" align="left" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0; padding-left: 10px;">
							<strong>${u.full_name || u.username}</strong>
						</td>
						<td width="50%" align="right" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0;"></td>
					`;
				} else {
					// fallback mínimo si no hay preview
					header.innerHTML = `
						<td colspan="3" style="border-bottom:1px solid #ccc; padding:10px;">
							<strong>Direct Message</strong>
						</td>
					`;
				}
			}

			const notif = Array.isArray(data.data)
				? data.data.find(n => Number(n.notification_id) === Number(resolvedNotifId))
				: null;

			if (!notif) {
				console.warn(
					// '[DM] Notification not found, fallback to chat_id from storage',
					{ notifId, activeChatId: localStorage.getItem('activeChatId') }
				);
			}

			if (notif) {
				header.innerHTML = `
					<td width="5%" align="center" valign="middle" style="border-bottom: 1px solid #ccc;">
						<div class="message-user-profile-pic">
							<img src="${notif.from_user_image}" alt="Profile Pic">
						</div>
					</td>
					<td width="45%" align="left" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0; padding-left: 10px;">
						<strong>${notif.from_user_name}</strong>
					</td>
					<td width="50%" align="right" valign="middle" style="border-bottom: 1px solid #ccc; padding: 10px 0;"></td>
				`;
			}

			// 🔹 Cargar historial del chat (si existe chat_id)
			let chatId = null;

			// 1️⃣ Prioridad: notification_link
			if (notif && notif.notification_link) {
				chatId = Number(notif.notification_link);
				localStorage.setItem('activeChatId', chatId);
			}

			// 2️⃣ Fallback: localStorage
			if (!chatId) {
				chatId = Number(localStorage.getItem('activeChatId')) || null;
			}

			try {
				const historyRes = await fetch(
					`api/get_chat_messages.php?chat_id=${chatId}`,
					{ headers: { 'Accept': 'application/json' } }
				);

				const history = await historyRes.json();
				// console.log('DM History:', history);
				if (history.success && Array.isArray(history.messages)) {
					renderDMHistory(history.messages);

					updateReadStatus(chatId);

					if (!window.dmReadInterval) {
						window.dmReadInterval = setInterval(() => {
							const activeChatId = Number(localStorage.getItem('activeChatId'));
							if (activeChatId) {
								updateReadStatus(activeChatId);
							}
						}, 5000);
					}
				}
			} catch (e) {
				console.error('Error loading chat history:', e);
			}

		} catch (e) {
			console.error('Error loading DM chat:', e);
		}
	}
	window.openDirectMessageChat = openDirectMessageChat;

	async function sendDMMessage() {
		const input = document.getElementById('replyMessageField');
		const messagesBox = document.getElementById('dm-messages-box');

		if (!input || !messagesBox) return;

		const message = input.value.trim();
		if (!message) return;

		const chatId   = Number(localStorage.getItem('activeChatId')) || null;
		const toUserId = Number(localStorage.getItem('activeDMUserId'));
		if (!toUserId) return;

		// UI optimista
		const tempMsg = document.createElement('div');
		tempMsg.className = 'dm-message dm-me pending';
		tempMsg.dataset.createdAt = new Date().toISOString();
		tempMsg.innerHTML = `
			<div class="dm-text">${message}</div>
			<div class="dm-meta">
				<span class="dm-time">${formatTime(new Date())}</span>
				<span class="dm-status">✔</span>
			</div>
		`;
		messagesBox.appendChild(tempMsg);
		messagesBox.scrollTop = messagesBox.scrollHeight;

		input.value = '';

		try {
			const formData = new FormData();
			if (chatId > 0) formData.append('chat_id', chatId);
			formData.append('to_user_id', toUserId);
			formData.append('message', message);

			const res = await fetch('api/create_chat_message.php', {
				method: 'POST',
				headers: { 'Accept': 'application/json' },
				body: formData
			});

			const data = await res.json();

			if (!data.success) {
				tempMsg.classList.remove('pending');
				tempMsg.classList.add('error');
				tempMsg.querySelector('.dm-status').innerText = '❌';
				return;
			}

			tempMsg.classList.remove('pending');
			tempMsg.classList.add('confirmed');
			tempMsg.dataset.messageId = data.message_id;

			const status = tempMsg.querySelector('.dm-status');
			if (status) status.innerText = '✔✔';

			if (!chatId && data.chat_id) {
				localStorage.setItem('activeChatId', data.chat_id);
			}

			if (typeof window.fetchAndRenderNotifications === 'function') {
				await window.fetchAndRenderNotifications();
			}
		} catch (err) {
			tempMsg.classList.remove('pending');
			tempMsg.classList.add('error');
			tempMsg.innerText = '❌ Error sending message';
			console.error(err);
		}
	}

	function attachDMInputHandler() {
		const input = document.getElementById('replyMessageField');
		const sendBtn = document.getElementById('sendMessageBtn');

		if (!input) return;

		if (input.dataset.bound === '1') return;
		input.dataset.bound = '1';

		// ⌨️ Enter
		input.addEventListener('keydown', (e) => {
			if (e.key === 'Enter') {
				e.preventDefault();
				sendDMMessage();
			}
		});

		// 🖱️ Botón Send
		if (sendBtn) {
			sendBtn.addEventListener('click', (e) => {
				e.preventDefault();
				sendDMMessage();
			});
		}
	}

	function renderDMHistory(messages) {
		const box = document.getElementById('dm-messages-box');
		if (!box) return;

		box.innerHTML = "";

		const me = Number(window.currentUserId);
		let lastDay = null;

		messages.forEach(m => {
			const msgDate = new Date(m.created_at);
			const dayKey = msgDate.toDateString();

			// 🗓️ Separador de fecha
			if (dayKey !== lastDay) {
				const dayDivider = document.createElement('div');
				dayDivider.className = 'dm-day-divider';
				dayDivider.innerText = formatDayLabel(m.created_at);
				box.appendChild(dayDivider);
				lastDay = dayKey;
			}

			// 💬 Mensaje
			const msg = document.createElement('div');
			const isMine = Number(m.from_user_id) === me;

			msg.className = `dm-message ${isMine ? 'dm-me' : 'dm-other'}`;

			msg.dataset.createdAt = m.created_at;
			msg.innerHTML = `
				<div class="dm-text">${m.message}</div>
				<div class="dm-meta">
					<span class="dm-time">${formatTime(m.created_at)}</span>
					${isMine ? `
						<span class="dm-status ${m.is_read ? 'read' : ''}">
							${m.is_read ? '✔✔' : '✔'}
						</span>` : ``}
				</div>
			`;

			box.appendChild(msg);
		});

		box.scrollTop = box.scrollHeight;
	}


	window.DM = window.DM || {};
	
	window.DM.refreshChat = async function (chatId) {
		if (!chatId) return;

		try {
			const res = await fetch(`api/get_chat_messages.php?chat_id=${chatId}`, {
				headers: { 'Accept': 'application/json' }
			});
			const data = await res.json();

			if (!data.success || !Array.isArray(data.messages)) return;

			renderDMHistory(data.messages);
			updateReadStatus(chatId);

		} catch (e) {
			console.error('[DM] refreshChat error:', e);
		}
	};

	function formatTime(dateStr) {
		const d = new Date(dateStr);
		return d.toLocaleTimeString([], {
			hour: '2-digit',
			minute: '2-digit'
		});
	}

	function formatDayLabel(dateStr) {
		const d = new Date(dateStr);
		const today = new Date();
		const yesterday = new Date();
		yesterday.setDate(today.getDate() - 1);

		const sameDay = (a, b) =>
			a.getFullYear() === b.getFullYear() &&
			a.getMonth() === b.getMonth() &&
			a.getDate() === b.getDate();

		if (sameDay(d, today)) return 'Today';
		if (sameDay(d, yesterday)) return 'Yesterday';

		return d.toLocaleDateString();
	}

	function updateReadStatus(chatId) {
		fetch(`api/get_chat_read_status.php?chat_id=${chatId}`)
			.then(res => res.json())
			.then(data => {
				if (!data.success || !data.last_read_at) return;

				const readAt = new Date(data.last_read_at);

				document.querySelectorAll('.dm-message.dm-me').forEach(msg => {
					const msgTime = new Date(msg.dataset.createdAt);
					const status = msg.querySelector('.dm-status');

					if (!status) return;

					if (msgTime <= readAt) {
						status.innerText = '✔✔';
						status.classList.add('read'); // azul
					}
				});
			});
	}
//############################################################# END DIRECT MESSAGE ##################################################################
});