<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido", 405);
    }

    $database = new Database();
    $db = $database->getConnection();

    $data = json_decode(file_get_contents("php://input"));
    
    if(!$data || !isset($data->idconsulta)) {
        throw new Exception("Datos incompletos", 400);
    }

    // Validar datos
    $idconsulta = filter_var($data->idconsulta, FILTER_VALIDATE_INT);
    $comentario = htmlspecialchars($data->comentario ?? '');
    $idusuario = (int) $_SESSION['idusuario'];

    if(!$idconsulta) {
        throw new Exception("ID de consulta inválido", 400);
    }

    $query = "INSERT INTO historial_status 
              (tabla, idtabla, idusuario, fechaingreso, status) 
              VALUES 
              ('consultas', :idconsulta, :idusuario, NOW(), :comentario)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':idconsulta', $idconsulta, PDO::PARAM_INT);
    $stmt->bindParam(':idusuario', $idusuario, PDO::PARAM_INT);
    $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
    
    if(!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta", 500);
    }

    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId(),
        'message' => 'Historial guardado correctamente'
    ]);
    
} catch(Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>