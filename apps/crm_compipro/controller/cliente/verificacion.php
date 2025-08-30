<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../cliente_conexion.php';

try {
    // Leer JSON del cuerpo
    $data = json_decode(file_get_contents("php://input"), true);

    // Función para obtener valores con fallback a "----"
    function getValueOrPlaceholder($data, $key) {
        return isset($data[$key]) && trim($data[$key]) !== '' ? trim($data[$key]) : '----';
    }

    // Obtener datos sanitizados o "----"
    $email    = getValueOrPlaceholder($data, 'email');
    $telefono = getValueOrPlaceholder($data, 'telefono');
    $celular  = getValueOrPlaceholder($data, 'celular');

    // Ejecutar búsqueda
    $idCliente = $cliente->getIdByInfo($email, $telefono, $celular);

    // Enviar respuesta JSON
    echo json_encode([
        'existe' => $idCliente !== null,
        'id' => $idCliente
    ]);

} catch (Exception $e) {
    echo json_encode([
        'existe' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
