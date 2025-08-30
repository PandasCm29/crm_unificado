<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../cliente_conexion.php';

try {
    $cliente->reset();
    $idcliente = $_GET['id'] ?? null;
    if (empty($idcliente)) {
        echo json_encode([
            'success' => false,
            'message' => "ID de cliente no proporcionado."
        ]);
        exit;
    }
    $cliente->idcliente = trim($idcliente);
    $cliente->cumpleanios = !empty($_POST['cumpleanios'])
                            ? date('Y-m-d', strtotime($_POST['cumpleanios']))
                            : null;
    $cliente->accionescliente = trim($_POST['accionescliente'] ?? '');
    if ($cliente->actualizarCumpleYAcciones()) {
        echo json_encode([
            'success' => true,
            'message' => "Cumpleaños y acciones actualizados correctamente.",
        ]);
    } else {
        $err = $db->errorInfo();
        echo json_encode([
            'success' => false,
            'message' => "Fallo al insertar: " . ($err[2] ?? 'Error desconocido')
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Excepción en la base de datos: " . $e->getMessage()
    ]);
}
