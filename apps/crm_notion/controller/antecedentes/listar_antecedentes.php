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
    $idcliente = isset($_GET['idcliente']) ? filter_var($_GET['idcliente'], FILTER_VALIDATE_INT) : null;
    if (!$idcliente) {
        echo json_encode([
            'success' => false,
            'message' => 'Falta el ID del cliente'
        ]);
        exit;
    }

    $antecedentesList = $antecedentes->getById($idcliente, $idusuario);
    echo json_encode([
        'success' => true,
        'data' => $antecedentesList
    ]);
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage(), 3, __DIR__ . '/../../logs/errors.log');
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor. Contacte al administrador.'
    ]);
}
?>