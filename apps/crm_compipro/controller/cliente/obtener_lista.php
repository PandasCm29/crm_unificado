<?php
// Activar CORS si accedes desde otro dominio o localhost
header("Content-Type: application/json");

// Incluir clase o conexión
require_once __DIR__ . '/../cliente_conexion.php';

// Recoger parámetros GET
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$valoresPermitidos = [10, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500];
if (!in_array($limit, $valoresPermitidos)) {
    $limit = 10;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;


$ordenarPorFecha = isset($_GET['fecha_aviso']);
$filtrarPorFecha = (!empty($_GET['desde']) || !empty($_GET['hasta']));
$fechaAviso = isset($_GET['fecha_aviso']) && $_GET['fecha_aviso'] === '1';
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

// Obtener datos
$totalClientes = $cliente->countFiltered($filtrarPorFecha, $fechaAviso, $desde, $hasta);
$clientes = $cliente->readPage($page, $limit, $ordenarPorFecha, $filtrarPorFecha, $fechaAviso, $desde, $hasta);
$totalPaginas = ceil($totalClientes / $limit);
$clientesGlobal = $cliente ->countWithOutFilters(false);

// Rango de páginas
$rangoInicio = max(1, $page - 2);
$rangoFin = min($totalPaginas, $page + 2);
if ($rangoFin - $rangoInicio < 4 && $totalPaginas > 5) {
    if ($rangoInicio == 1) {
        $rangoFin = min($rangoInicio + 4, $totalPaginas);
    } elseif ($rangoFin == $totalPaginas) {
        $rangoInicio = max(1, $rangoFin - 4);
    }
}

// Registros visibles
$registroInicio = ($totalClientes > 0) ? ($page - 1) * $limit + 1 : 0;
$registroFin = min($page * $limit, $totalClientes);

// Respuesta JSON
echo json_encode([
    'clientes' => $clientes,
    'totalClientes' => $totalClientes,
    'paginaActual' => $page,
    'totalPaginas' => $totalPaginas,
    'rangoInicio' => $rangoInicio,
    'rangoFin' => $rangoFin,
    'registroInicio' => $registroInicio,
    'registroFin' => $registroFin,
    'clientesGlobal' => $clientesGlobal,
]);
exit;
