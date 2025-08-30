<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../cliente_conexion.php';

try {
    $id = $_GET['id'];
    // Supongamos que tienes una función para obtener el cliente
    $clienteObtenido = $cliente->getById($id);
    echo json_encode($clienteObtenido);
}catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Excepción en la base de datos: " . $e->getMessage()
    ]);
}
