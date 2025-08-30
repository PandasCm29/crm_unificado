<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../cliente_conexion.php';

try {
    $mes = $_GET['mes'];
    // Supongamos que tienes una función para obtener el cliente
    $meses = $cliente->getCumpleaniosAniversarios($mes);
    echo json_encode($meses);
}catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Excepción en la base de datos: " . $e->getMessage()
    ]);
}
