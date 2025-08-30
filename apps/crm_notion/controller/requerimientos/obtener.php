<?php

require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {

    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $consulta = $requerimiento ->getByIdConsulta($id);

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
    $page  = isset($_GET['page'])  ? (int) $_GET['page']  : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

    $consultas = $requerimiento ->readPageConsultas($page, $limit);
    $queryTotal = "SELECT COUNT(*) as total FROM consultas WHERE status != 4";
    $stmtTotal = $db->prepare($queryTotal);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $totalRecords = (int) $resultTotal['total'];

    echo json_encode([
        'success' => true,
        'data'    => $consultas,
        'total'   => $totalRecords,
        'page'    => $page,
        'limit'   => $limit
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}