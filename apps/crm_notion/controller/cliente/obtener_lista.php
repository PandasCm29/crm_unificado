<?php
// Activar CORS si accedes desde otro dominio o localhost
header("Content-Type: application/json");

// Incluir clase o conexión
require_once __DIR__ . '/../cliente_conexion.php';

// 1. Capturar page y limit desde GET
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$valoresPermitidos = [10, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500];
if (!in_array($limit, $valoresPermitidos)) {
    $limit = 10;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// 2. Capturar filtros desde JSON en el body
$input = json_decode(file_get_contents("php://input"), true);
// Validar si el JSON es válido
if (!is_array($input)) {
    echo json_encode([
        'error' => true,
        'message' => 'Formato de entrada inválido. Se esperaba un JSON.'
    ]);
    exit;
}

// 3. Construir el array de filtros
$filters = [
    'filtrarPorFecha' => isset($input['filtrarPorFecha']) ? (bool)$input['filtrarPorFecha'] : false,
    'fechaAviso' => isset($input['fechaAviso']) ? (bool)$input['fechaAviso'] : false,
    'desde' => $input['desde'] ?? null,
    'hasta' => $input['hasta'] ?? null,
    'tipoCliente' => $input['tipoCliente'] ?? null,
    'statusAtencion' => $input['statusAtencion'] ?? null,
    'origen' => $input['origen'] ?? null,
    'texto' => $input['texto'] ?? null,
    'campoTexto' => $input['campoTexto'] ?? null
];
echo $cliente->readPage($filters, (int)$page, (int)$limit);
exit;
