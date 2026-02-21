const http = require('http');
const WebSocket = require('ws');
const express = require('express');
const cors = require('cors');

const app = express();

/**
 * Config por entorno:
 * - LOCAL: defaults a localhost / 127.0.0.1
 * - PROD: puedes setear WS_HOST/BRIDGE_HOST a 127.0.0.1 (si va detrás de Nginx),
 *         o 0.0.0.0 si necesitas escuchar en todas las interfaces.
 */
const WS_HOST     = process.env.WS_HOST     || '127.0.0.1';
const WS_PORT     = Number(process.env.WS_PORT || 3001);
const BRIDGE_HOST = process.env.BRIDGE_HOST || '127.0.0.1';
const BRIDGE_PORT = Number(process.env.BRIDGE_PORT || 3002);

// Si lo seteas (prod), lo valida. Si está vacío, no valida.
const NOTIFY_TOKEN = process.env.NOTIFY_TOKEN || '';

// Detecta “producción” por NODE_ENV (recomendado)
const isProd = (process.env.NODE_ENV || '').toLowerCase() === 'production';

// --- WebSocket server ---
const server = http.createServer();
const wss = new WebSocket.Server({ server });

let clients = [];

wss.on('connection', function connection(ws, req) {
	ws.isAlive = true;
	ws.on('pong', () => (ws.isAlive = true));

	clients.push(ws);

	ws.on('close', () => {
		clients = clients.filter(c => c !== ws);
	});

	// ESCUCHAR MENSAJES ENTRANTES
	ws.on('message', raw => {
		let data;
		try {
			data = JSON.parse(raw.toString());
		} catch (e) {
			console.warn('❌ WS payload inválido');
			return;
		}

		// 🔐 IDENTIFICAR USUARIO
		if (data.type === 'identify' && data.user_id) {
			ws.userId = Number(data.user_id);
			return;
		}

		// 💬 DIRECT MESSAGE
		if (data.type === 'direct_message') {
			const toUserId = Number(data.to_user_id);
			const fromUserId = ws.userId || null;

			if (!toUserId || !fromUserId) return;

			clients.forEach(client => {
				if (client.readyState === WebSocket.OPEN && client.userId === toUserId) {
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
	clients.forEach(client => {
		if (client.readyState === WebSocket.OPEN) {
			client.send(JSON.stringify(payload));
		}
	});
}

// heartbeat
const interval = setInterval(() => {
	clients.forEach(ws => {
		if (!ws.isAlive) return ws.terminate();
		ws.isAlive = false;
		ws.ping();
	});
}, 30000);

// --- HTTP bridge (PARA PHP) ---
if (!isProd) {
	// En local es cómodo permitir CORS
	app.use(cors());
}

app.use(express.json());

app.post('/notify', (req, res) => {
	// Seguridad: token simple (si existe NOTIFY_TOKEN)
	if (NOTIFY_TOKEN && req.get('X-Notify-Token') !== NOTIFY_TOKEN) {
		return res.status(401).json({ success: false, message: 'Unauthorized' });
	}

	if (!req.body || !req.body.message) {
		return res.status(400).json({ success: false, message: "Missing 'message'." });
	}

	console.log('🔔 Notificación recibida desde PHP:', req.body);
	broadcastNotification(req.body);
	res.json({ success: true });
});

app.get('/health', (req, res) => res.json({ ok: true }));

server.listen(WS_PORT, WS_HOST, () => {
	console.log(`🟢 WS listening on ws://${WS_HOST}:${WS_PORT} (${isProd ? 'PROD' : 'LOCAL'})`);
});

app.listen(BRIDGE_PORT, BRIDGE_HOST, () => {
	console.log(`🟢 Bridge listening on http://${BRIDGE_HOST}:${BRIDGE_PORT}/notify (${isProd ? 'PROD' : 'LOCAL'})`);
});