<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 1);


$logFile = __DIR__ . '/sync_cron_clientes.log'; // Log en la misma carpeta
function logMessage($msg) {
    global $logFile;
    file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}

require_once __DIR__ . '/../../../models/notion/ClienteNotion.php';

// ✅ Calcular bloque actual en UTC o local (aquí local)
date_default_timezone_set('America/Lima'); // Ajusta a tu zona horaria
$now = new DateTime();
$hour = (int)$now->format('H');

// Determinar inicio del bloque
$blockStartHour = floor($hour / 6) * 6; // Bloques de 6h: 0,6,12,18
$now->setTime($blockStartHour, 0, 0);
$lastSyncTime = $now->format('Y-m-d\TH:i:s');

// Log del bloque calculado
// logMessage("Calculado lastSyncTime para bloque: $lastSyncTime");

try {
    $db = new Database();
    $data = ClienteNotion::syncFromNotion($db->getConnection(), $lastSyncTime);

    logMessage("Éxito: " . json_encode($data));
    echo json_encode($data);

} catch (Exception $e) {
    $errorMsg = 'Excepción: ' . $e->getMessage();
    logMessage("ERROR: $errorMsg");
    echo json_encode(['success' => false, 'message' => $errorMsg]);
}
