<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');

require_once __DIR__ . '/cliente_conexion.php';
require_once __DIR__ . '/requerimientos/conexion.php';
require_once __DIR__ . '/../models/RequerimientoModel.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'OPTIONS') {
        // CORS preflight
        http_response_code(200);
        exit;
    }

    if ($method === 'GET') {
        // Obtener un requerimiento por id
        if (empty($_GET['id'])) {
            throw new Exception('Falta parámetro id');
        }
        $id       = (int) $_GET['id'];
        $consulta = $requerimiento->getByIdConsulta($id);
        if (!$consulta) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => "No se encontró la consulta con id $id"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode([
            'success' => true,
            'data'    => $consulta
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Leemos el body como JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new Exception('JSON inválido');
    }

    // — PUT: 
    if ($method === 'PUT') {
        if (empty($input['idconsulta'])) {
            throw new Exception('Falta idconsulta para actualizar');
        }
        foreach ($input as $k => $v) {
            if (property_exists($requerimiento, $k)) {
                $requerimiento->$k = $v;
            }
        }
        $res = $requerimiento->updateConsulta((int)$input['idconsulta']);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // — POST: clonación + actualización 
    if ($method === 'POST') {
        if (!empty($input['idconsulta']) && !empty($input['clonar'])) {
            $res = $requerimiento->cloneAndUpdate((int)$input['idconsulta'], $input);

            if ($res['success']) {
            // 2) Clonar el historial de status
            require_once __DIR__ . '/../models/HistorialUsuarioClienteModel.php';
            $histModel = new HistorialUsuarioCliente($db, 'consultas');
            $histRes   = $histModel->clonarHistorial((int)$input['idconsulta'], (int)$res['newId']);

            // 3) Adjuntar mensaje de historial al resultado
            $res['message'] .= ' | '.$histRes['message'];
        }
            echo json_encode($res, JSON_UNESCAPED_UNICODE);
            exit;
        }
        throw new Exception('Creación de requerimiento no implementada aún');
    }

    throw new Exception('Método no soportado');

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
