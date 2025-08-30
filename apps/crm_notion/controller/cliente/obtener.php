<?php
require_once __DIR__ . '/../cliente_conexion.php';

header('Content-Type: application/json');

try {
    // Si viene por POST, usa json_decode
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? null;
    $id = $_GET['id'] ?? null;
    $sincro = $_GET['sincro'] ?? null;

    if ($ids && is_array($ids)) {
        // Obtener múltiples clientes
        $clientes = $cliente->getByIdOrIds($ids);
        echo json_encode([
            'success' => true,
            'clientes' => $clientes
        ]);
    } elseif ($id) {
        // Obtener un solo cliente
        $clienteObtenido = $cliente->getByIdOrIds($id);
        echo json_encode([
            'success' => true,
            'cliente' => $clienteObtenido
        ]);
    } elseif($sincro){
        // Obtener clientes desincronizados
        $clientes = $cliente->obtenerClientesParaNotion();
        echo json_encode($clientes);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se proporcionó ID ni lista de IDs. Tampoco un valor para traer datos desincronizados.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Excepción en la base de datos: ' . $e->getMessage()
    ]);
}
