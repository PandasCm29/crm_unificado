<?php
require_once __DIR__ . '/../../../models/notion/ClienteNotion.php';

date_default_timezone_set('America/Lima'); // Para last_sync_time

$db = new Database();
$success = ClienteNotion::syncFromNotion($db->getConnection());


// echo json_encode([
//     'success' => $success,
//     'message' => $success ? 'Sincronización desde Notion completada.' : 'Error en la sincronización desde Notion.'
// ]);

error_log('Sync from Notion at ' . date('Y-m-d H:i:s') . ': ' . ($success ? 'Success' : 'Failed'));

// /usr/bin/php -f /home/tu_usuario/public_html/controller/cliente/sync_from_notion.php >> /home/tu_usuario/sync.log 2>&1


// Ejemplo en cPanel:En la interfaz de Cron Jobs:Configuración común: Selecciona "Cada 5 minutos" (o la frecuencia deseada).
// Comando: Ingresa el comando anterior.
// Guarda la configuración.

// Verificar la ejecución:Revisa el archivo de log (/home/tu_usuario/sync.log) para confirmar que el cron job se ejecuta:bash

// cat /home/tu_usuario/sync.log