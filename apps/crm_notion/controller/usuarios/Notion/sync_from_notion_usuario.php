<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 1);


$logFile = __DIR__ . '/sync_cron_usuarios.log'; // Log en la misma carpeta
function logMessage($msg) {
    global $logFile;
    file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}


require_once __DIR__ . '/../../../models/notion/UsuarioNotion.php';

// ✅ Calcular bloque actual en UTC o local (aquí local)
date_default_timezone_set('America/Lima'); // Ajusta a tu zona horaria
$now = new DateTime();
$hour = (int)$now->format('H');

// Determinar inicio del bloque
$blockStartHour = floor($hour / 6) * 6; // Bloques de 6h: 0,6,12,18
$now->setTime($blockStartHour, 0, 0);
$lastSyncTime = $now->format('Y-m-d\TH:i:s');

try {
    $db = new Database();
    $data = UsuarioNotion::syncFromNotion($db->getConnection(), $lastSyncTime);
    // 🔹 Log principal
    logMessage("Resultado sync: " . json_encode([
        'success'  => $data['success'],
        'message'  => $data['message'],
        'cantidad' => $data['cantidad']
    ]));

    // 🔹 Si hay errores, registrarlos también
    if (!empty($data['errores'])) {
        foreach ($data['errores'] as $err) {
            logMessage("ERROR sync usuario: " . $err);
        }
    }
    echo json_encode($data);
    
} catch (PDOException $e) {
    $errorMsg = 'Excepción: ' . $e->getMessage();
    logMessage("ERROR: $errorMsg");
    echo json_encode(['success' => false, 'message' => $errorMsg]);
}
