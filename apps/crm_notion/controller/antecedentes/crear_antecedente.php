<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/AntecedentesClienteModel.php';

$database = new Database();
$db = $database->getConnection();
$antecedentes = new AntecedentesCliente($db);

try {
    
    $idusuario = (int) $_SESSION['idusuario'];

    $input = json_decode(file_get_contents('php://input'), true);
    $idcliente = isset($input['idcliente']) ? filter_var($input['idcliente'], FILTER_VALIDATE_INT) : null;
    $status = isset($input['status']) ? htmlspecialchars(trim($input['status'])) : null;

    if (!$idcliente || !$status) {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan datos obligatorios'
        ]);
        exit;
    }

    $result = $antecedentes->crear($idcliente, $idusuario, $status);
    echo json_encode($result);
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage(), 3, __DIR__ . '/../../logs/errors.log');
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor. Contacte al administrador.'
    ]);
}
?>