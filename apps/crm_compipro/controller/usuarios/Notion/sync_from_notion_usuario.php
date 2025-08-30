<?php

require_once __DIR__ . '/../../../models/notion/UsuarioNotion.php';


try {
    $db = new Database();
    $success = UsuarioNotion::syncFromNotion($db->getConnection());
    error_log('Sync from Notion at ' . date('Y-m-d H:i:s') . ': ' . ($success ? 'Success' : 'Failed'));
    // echo json_encode([
        // 'success' => $success,
    // 'message' => $success ? 'Usuarios sincronizados desde Notion correctamente.' : 'Error al sincronizar usuarios desde Notion.'
    // ]);
} catch (PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    // echo json_encode([
    //    'success' => false,
    //    'message' => "Error de base de datos: " . $e->getMessage()
    // ]);
}
