<?php
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true);
$tabla   = $input['tabla']   ?? 'clientes';
$idtabla = (int)($input['idtabla'] ?? 0);
$status  = trim($input['status'] ?? '');
$idstatus= (int)($input['idstatus'] ?? 0);

$historialModel = new HistorialUsuarioCliente($db, $tabla);
$idusuario = (int) $_SESSION['idusuario'];
try {
    switch ($method) {
        case 'POST':
            // CREAR
            if ($idtabla <= 0 || $status === '') {
                throw new Exception('Faltan datos para crear.');
            }
            $resultado = $historialModel->crear($idtabla,  $idusuario, $status);
            break;

        case 'PUT':
            // EDITAR
            if ($idstatus <= 0 || $status === '') {
                throw new Exception('Faltan datos para actualizar.');
            }
            $resultado = $historialModel->actualizar($idstatus, $status);
            break;

        case 'DELETE':
            // ELIMINAR
            if ($idstatus <= 0) {
                throw new Exception('Falta el idstatus para eliminar.');
            }
            $resultado = $historialModel->eliminar($idstatus);
            break;

        default:
            throw new Exception('Método no soportado');
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
