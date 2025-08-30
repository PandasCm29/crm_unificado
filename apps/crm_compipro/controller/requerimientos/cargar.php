<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception("ID de consulta no proporcionado", 400);
    }

    $idConsulta = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$idConsulta) {
        throw new Exception("ID de consulta inválido", 400);
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT h.*, 
                 u.nombres, 
                 u.apellidos, 
                 u.usuario
          FROM historial_status h
          LEFT JOIN usuarios u ON h.idusuario = u.idusuario
          WHERE h.tabla = 'consultas' AND h.idtabla = :idconsulta
          ORDER BY h.fechaingreso DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':idconsulta', $idConsulta, PDO::PARAM_INT);
    $stmt->execute();

    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $historial
    ]);
    
} catch(Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>