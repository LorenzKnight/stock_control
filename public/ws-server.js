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