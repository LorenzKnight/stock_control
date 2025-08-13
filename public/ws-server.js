// LIVE VERSION
// Descomentar este bloque para la versión en producción

// const WebSocket = require('ws');
// const http = require('http');
// const fs = require('fs');
// const express = require('express');
// const cors = require('cors');

// const PORT = 3001;
// const HTTP_BRIDGE_PORT = 3002;

// const server = http.createServer();
// // const server = https.createServer({
// // 	key: fs.readFileSync('/etc/letsencrypt/live/allstockcontrol.com/privkey.pem'),
// // 	cert: fs.readFileSync('/etc/letsencrypt/live/allstockcontrol.com/fullchain.pem')
// // });
// const wss = new WebSocket.Server({ server });

// let clients = [];

// wss.on('connection', function connection(ws) {
// 	clients.push(ws);

// 	ws.on('close', () => {
// 		clients = clients.filter(client => client !== ws);
// 	});

// 	ws.isAlive = true;
// 	ws.on('pong', () => (ws.isAlive = true));
// });

// function broadcastNotification(payload) {
// 	// Garantiza que el payload sea objeto
// 	let data = payload;
// 	if (typeof payload === 'string') {
// 		try { data = JSON.parse(payload); } catch { data = { message: String(payload) }; }
// 	}
// 	const msg = JSON.stringify(data);
// 	console.log('📤 Enviando a clientes:', msg);
// 	clients.forEach((client) => {
// 		if (client.readyState === WebSocket.OPEN) {
// 			try { client.send(msg); } catch (e) { /* ignore */ }
// 		}
// 	});
// }

// server.listen(WS_PORT, '127.0.0.1', () => {
// 	console.log(`🟢 WebSocket server listening on ws://allstockcontrol.com:${PORT}`);
// });

// // Para que PHP lo llame
// const app = express();
// app.use(cors());
// app.use(express.json());

// app.post('/notify', (req, res) => {
// 	const body = req.body || {};
// 	console.log('🔔 Notificación recibida desde PHP:', body);

// 	if (!body.message) {
// 		return res.status(400).json({ success: false, message: "Falta 'message' en el payload." });
// 	}
	
// 	broadcastNotification(body);
// 	res.json({ success: true });
// });

// app.listen(HTTP_BRIDGE_PORT, '127.0.0.1', () => {
// 	console.log(`🟢 HTTP bridge listening on http://127.0.0.1:${HTTP_BRIDGE_PORT}/notify`);
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