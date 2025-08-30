const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { exec } = require("child_process");
const fs = require('fs');
const path = require("path");

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  path: "/ws/socket.io", // Igual al cliente
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
  //     cors: {
  //       origin: "https://compihost.net",
  //       methods: ["GET", "POST"]
  //   }
});

// Ruta absoluta del script PHP
const syncScriptPath = path.resolve(
  __dirname,
  "../controller/cliente/notion/sync_from_notion.php"
);

// Servidor en el puerto 3000
const PORT = process.env.PORT || 8080; //8455

io.on("connection", (socket) => {
  console.log('🔌 Cliente conectado');
  socket.on('identificar', (data) => {
    const area = data.tipo || 'General';
    const nombre = data.usuario || 'Invitado';

    console.log(`🧑 Usuario conectado: ${nombre} (área: ${area})`);

    // Unir a la sala del área
    socket.join(area);
    socket.data.area = area; // guardar en socket para referencia futura
  });
  socket.on('disconnect', () => {
    console.log('🚫 Cliente desconectado');
  });
});

server.listen(PORT, () => {
  console.log(`Servidor Node.js corriendo en http://localhost:${PORT}`);
});

const syncTimeFilePath = path.resolve(__dirname, 'last_sync_time.json');

// Leer el lastSyncTime del archivo o generar uno inicial
let lastSyncTime = getStartOfCurrentBlock();
console.log('🕒 Ventana de sincronización actual:', lastSyncTime);
try {
  const fileContent = fs.readFileSync(syncTimeFilePath, 'utf-8');
  const data = JSON.parse(fileContent);
  if (data.lastSyncTime) {
    lastSyncTime = data.lastSyncTime;
    console.log('🕒 Última sincronización registrada:', lastSyncTime);
  }
} catch (err) {
  console.log('📄 No se encontró archivo de sincronización, usando hora de bloque actual');
}

// Ejecutar el script PHP cada 10 segundos
setInterval(() => {
  const syncTimeToSend = getStartOfCurrentBlock();
  const phpCommand = `php ${syncScriptPath} "${syncTimeToSend}"`;
  exec(phpCommand, (error, stdout, stderr) => {
  const timestamp = new Date().toLocaleTimeString();

  if (stderr && stderr.trim()) {
    console.warn(`[${timestamp}] ⚠️ STDERR (advertencia): ${stderr.trim()}`);
  }  
  if (error) {
    console.error(`[${timestamp}] ❌ Error ejecutando PHP: ${error.message}`);
    io.emit('notion-sync', {
      success: false,
      message: 'Error al ejecutar PHP',
      error: error.message,
      timestamp,
    });
    return;
  }

  let payload;
  try {
    payload = JSON.parse(stdout);
  } catch (e) {
    console.error(`[${timestamp}] ❌ JSON inválido desde PHP: ${stdout}`);
    io.emit('notion-sync', {
      success: false,
      message: 'JSON inválido desde PHP',
      raw: stdout,
      timestamp,
    });
    return;
  }

  if (payload.success && (payload.clientes_nuevos.length > 0 || payload.clientes_editados.length > 0)) {
    // console.log(`[${timestamp}] ✅ Sync: ${payload.message}`);

    const cambios = {
      nuevos: payload.clientes_nuevos,
      editados: payload.clientes_editados
    };

    io.emit('clientes-actualizados', cambios);
  }
  if (!payload.success || (payload.cantidad && payload.cantidad > 0)) {
    console.log(`[${timestamp}] ✅ Sync: ${payload.message}`);
  }
  io.emit('notion-sync', payload);

  // Actualizar la variable de sincronización
  if (payload.success) {
    lastSyncTime = syncTimeToSend;
    fs.writeFileSync(syncTimeFilePath, JSON.stringify({ lastSyncTime }), 'utf-8');
  }
});
}, 3000); // 3 segundos

function getStartOfCurrentBlock() {
  const now = new Date();
  now.setMinutes(0, 0, 0); // limpiar minutos, segundos y milisegundos

  const hour = now.getHours();
  const startHour = Math.floor(hour / 6) * 6; // bloques de 6h: 0,6,12,18
  now.setHours(startHour);

  return now.toISOString();
}