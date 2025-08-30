<?php
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';
ob_start(); // Start output buffering to prevent stray output

try {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $areasParam = isset($_GET['areas']) ? $_GET['areas'] : '';
    if($id){
        $userModel->idusuario = $id;
        // Supongamos que tienes una función para obtener el userModel
        $userModelObtenido = $userModel->readOne();
        echo json_encode($userModelObtenido);
    }else if($areasParam){
        $resultado = $areas->readAll();
        ob_clean();
        echo json_encode($resultado);
    }else {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Parámetro "areas" no proporcionado'
        ]);
    }
}catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Excepción en la base de datos: " . $e->getMessage()
    ]);
}

ob_end_flush(); // End output buffering