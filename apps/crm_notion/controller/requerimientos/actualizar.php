<?php
require_once __DIR__ . '/../cliente_conexion.php';

header('Content-Type: application/json');
try {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input || !isset($input['action'])) {
        throw new Exception("accion no especificada");
    }
    $action = $input['action'];
    switch ($action) {
        case 'update_status':
            updateStatus($input);
            break;
        case 'delete':
            deleteRequerimiento($input);
            break;
        default:
            throw new Exception("accion no valida");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
function updateStatus($data) {
    if (!isset($data['id']) || !isset($data['status'])) {
        throw new Exception("ID o status no proporcionados");
    }
    $id = intval($data['id']);
    $status = $data['status'];
    $validStatuses = ['', '1', '2', '3'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception("Status no valido");
    }
    try {
        $database = new Database();
        $db       = $database->getConnection();
        $stmt = $db->prepare("UPDATE consultas SET status = ? WHERE idconsulta = ?");
        $result = $stmt->execute([$status, $id]);
        if (!$result) {
            throw new Exception("Error al actualizar el status en la base de datos");
        }
        echo json_encode([
            'success' => true,
            'message' => 'Status actualizado correctamente'
        ]);
    } catch (Exception $e) {
        throw new Exception("Error de base de datos: " . $e->getMessage());
    }
}
function deleteRequerimiento($data) {
    if (!isset($data['id'])) {
        throw new Exception("ID no proporcionado");
    }   
    $id = intval($data['id']);
    try {  
        $database = new Database();
        $db       = $database->getConnection();
        $stmt = $db->prepare("UPDATE consultas SET status = 4 WHERE idconsulta = ?");
        $result = $stmt->execute([$id]);
        if (!$result) {
            throw new Exception("Error al eliminar el requerimiento");
        }
        if ($stmt->rowCount() === 0) {
            throw new Exception("No se encontro el requerimiento a eliminar");
        }
        echo json_encode([
            'success' => true,
            'message' => 'Requerimiento eliminado correctamente'
        ]);
    } catch (Exception $e) {
        throw new Exception("Error de base de datos: " . $e->getMessage());
    }
}
?>