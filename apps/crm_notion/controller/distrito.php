<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DistritoModel.php';

try {
    $db = (new Database())->getConnection();
    $distritoModel = new Distrito($db);

    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        $todos = $distritoModel->obtenerTodos();
        $nombres = array_column($todos, 'distrito');
        echo json_encode(['success' => true, 'distritos' => $nombres]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $nombre = $data['nombre'] ?? '';
        $cp     = $data['codigo_postal'] ?? '';

        $res = $distritoModel->crear($nombre, $cp);
        if ($res === true) {
            echo json_encode([
                'success' => true,
                'message' => "Distrito \"{$nombre}\" agregado"
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $res
            ]);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de servidor: ' . $e->getMessage()
    ]);
}
