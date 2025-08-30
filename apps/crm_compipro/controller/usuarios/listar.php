<?php
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

try {
    $page  = isset($_GET['page'])  ? (int) $_GET['page']  : 1;
    $filtro  = isset($_GET['filtro'])  ? (string) $_GET['filtro'] : '';
    $resultList = $userModel->readPageFilter($filtro, $page);
    $total = $userModel->countAll($filtro);    
    echo json_encode([
        'success' => true,
        'data'    => $resultList['data'],
        'total'   => $total,
        'filtering'=>$resultList['filtering']
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Excepción en la base de datos: " . $e->getMessage()
    ]);
}
