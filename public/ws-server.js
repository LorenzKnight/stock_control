// LIVE VERSION
// Versión para producción

// // ws-server.js
// const http = require('http');
// const WebSocket = require('ws');
// const express = require('express');
// const app = express();

// const WS_HOST = '127.0.0.1';
// const WS_PORT = 3001;
// const BRIDGE_HOST = '127.0.0.1';
// const BRIDGE_PORT = 3002;
// const NOTIFY_TOKEN = process.env.NOTIFY_TOKEN || ''; // setéalo en PM2/systemd

// // --- WebSocket server ---
// const server = http.createServer();
// const wss = new WebSocket.Server({ server }); // ws en 3001

// let clients = [];

// wss.on('connection', function connection(ws, req) {
// 	// (opcional) valida origen
// 	// const origin = req.headers.origin || '';
// 	// if (!origin.includes('tu-dominio.com')) { ws.close(); return; }

// 	ws.isAlive = true;
// 	ws.on('pong', () => (ws.isAlive = true));

// 	clients.push(ws);
// 	ws.on('close', () => {
// 		clients = clients.filter(c => c !== ws);
// 	});

// 	// ESCUCHAR MENSAJES ENTRANTES
// 	ws.on('message', raw => {
// 		let data;
// 		try {
// 			data = JSON.parse(raw.toString());
// 		} catch (e) {
// 			console.warn('❌ WS payload inválido');
// 			return;
// 		}

// 		// 🔐 IDENTIFICAR USUARIO
// 		if (data.type === 'identify' && data.user_id) {
// 			ws.userId = Number(data.user_id);
// 			return;
// 		}

// 		// 💬 DIRECT MESSAGE
// 		if (data.type === 'direct_message') {
// 			const toUserId = Number(data.to_user_id);
// 			const fromUserId = ws.userId || null;

// 			if (!toUserId || !fromUserId) return;

// 			clients.forEach(client => {
// 				if (
// 					client.readyState === WebSocket.OPEN &&
// 					client.userId === toUserId
// 				) {
// 					client.send(JSON.stringify({
// 						type: 'direct_message',
// 						from_user_id: fromUserId,
// 						message: data.message,
// 						created_at: new Date().toISOString()
// 					}));
// 				}
// 			});
// 		}
// 	});
// });

// function broadcastNotification(payload) {
// 	clients.forEach(client => {
// 		if (client.readyState === WebSocket.OPEN) {
// 		client.send(JSON.stringify(payload));
// 		}
// 	});
// }

// // heartbeat
// const interval = setInterval(() => {
// 	clients.forEach(ws => {
// 		if (!ws.isAlive) return ws.terminate();
// 		ws.isAlive = false;
// 		ws.ping();
// 	});
// }, 30000);

// server.listen(WS_PORT, WS_HOST, () => {
// 	console.log(`🟢 WS listening on ws://${WS_HOST}:${WS_PORT}`);
// });

// // --- HTTP bridge (PARA PHP) ---
// app.use(express.json());

// app.post('/notify', (req, res) => {
// 	// Seguridad: token simple
// 	if (NOTIFY_TOKEN && req.get('X-Notify-Token') !== NOTIFY_TOKEN) {
// 		return res.status(401).json({ success: false, message: 'Unauthorized' });
// 	}

// 	if (!req.body || !req.body.message) {
// 		return res.status(400).json({ success: false, message: "Missing 'message'." });
// 	}

// 	console.log('🔔 Notificación recibida desde PHP:', req.body);
// 	broadcastNotification(req.body);
// 	res.json({ success: true });
// });

// // (opcional) health
// app.get('/health', (req, res) => res.json({ ok: true }));

// app.listen(BRIDGE_PORT, BRIDGE_HOST, () => {
// 	console.log(`🟢 Bridge listening on http://${BRIDGE_HOST}:${BRIDGE_PORT}/notify`);
// });

	









// LOCALHOST VERSION
// Para pruebas locales, puedes comentar el bloque anterior y descomentar este bloque

const WebSocket = require('ws');
const http = require('http');

const PORT = 3001;

const server = http.createServer();
const wss = new WebSocket.Server({ server });

let clients = [];

wss.on('connection', function connection(ws) {
	clients.push(ws);

	ws.on('close', () => {
		clients = clients.filter(client => client !== ws);
	});

	// ESCUCHAR MENSAJES ENTRANTES
	ws.on('message', raw => {
		let data;
		try {
			data = JSON.parse(raw.toString());
		} catch {
			console.warn('❌ WS LOCAL payload inválido');
			return;
		}

		// 🔐 Identificar usuario
		if (data.type === 'identify' && data.user_id) {
			ws.userId = Number(data.user_id);
			// console.log('👤 [LOCAL WS] Usuario identificado:', ws.userId);
			return;
		}

		// 💬 Direct Message
		if (data.type === 'direct_message') {
			const toUserId = Number(data.to_user_id);
			const fromUserId = ws.userId;

			if (!toUserId || !fromUserId) return;

			// console.log(`💬 [LOCAL DM] ${fromUserId} → ${toUserId}`);

			clients.forEach(client => {
				if (
					client.readyState === WebSocket.OPEN &&
					client.userId === toUserId
				) {
					client.send(JSON.stringify({
						type: 'direct_message',
						from_user_id: fromUserId,
						message: data.message,
						created_at: new Date().toISOString()
					}));
				}
			});
		}
	});
});

function broadcastNotification(payload) {
	console.log("📤 Enviando a clientes:", JSON.stringify(payload, null, 2));
	clients.forEach(client => {
		if (client.readyState === WebSocket.OPEN) {
			client.send(JSON.stringify(payload));
		}
	});
}

server.listen(PORT, () => {
	console.log(`🟢 WebSocket server listening on ws://localhost:${PORT}`);
});

// Para que PHP lo llame
const express = require('express');
const cors = require('cors');
const app = express();

app.use(cors());
app.use(express.json());

app.post('/notify', (req, res) => {
	console.log('🔔 Notificación recibida desde PHP:', req.body);
	if (!req.body.message) {
		return res.status(400).json({ success: false, message: "Falta 'message' en el payload." });
	}
	
	broadcastNotification(req.body);
	res.json({ success: true });
});

app.listen(3002, () => {
	console.log('🟢 HTTP bridge listening on http://localhost:3002/notify');
});