<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Lima');

require_once __DIR__ . '/cliente_conexion.php';
require_once __DIR__ . '/../view/cliente/components/utilidades-tabla.php';
$cliente->reset();

try {
    $action = $_REQUEST['action'] ?? 'create';


// ======================
// FILTRAR POR NÚMERO DE CLIENTE
// ======================
if ($action === 'filter_num_cliente') {
    $num    = $_GET['num']   ?? '';
    $page   = max(1, (int)($_GET['page']  ?? 1));
    $limit  = max(1, (int)($_GET['limit'] ?? 25));
    $offset = ($page - 1) * $limit;

    $total = $cliente->countByNumero($num);
    $rows = $cliente->getByNumero($num, $limit, $offset);
        ob_start();
        foreach ($rows as $c) {
            $idcliente = $c['idcliente'];
            $propio = ( $c['propio'] ?? "0" ) == "1";
            $data_propio = $propio ? "si":"no";
            // Reconstruir lista de teléfonos
            $telefonos = trim(
                ($c['telefono'] ?? '') . "\n" .
                ($c['celular']  ?? ''). "\n" .
                ($c['celular2']  ?? ''). "\n" .
                ($c['celular3']  ?? ''). "\n" .
                ($c['celular4']  ?? '')
            );
            $emails = str_replace(', ', "\n", $c['email']);

                    $estado_cliente_con_status = htmlspecialchars($c['estado_cliente'] ?: '–')
            . '<br><span '
            . 'style="color: blue; cursor: pointer;" '
            . 'onclick="abrirModalEditar('.$c['idcliente'].', 3)">ver status</span>';

            // Columna de acciones
            $acciones = '<div data-id="'.htmlspecialchars($idcliente).'" data-propio="'.htmlspecialchars($data_propio).'"class="flex flex-row space-x-2">
                    <button class="p-0 m-0 bg-transparent border-none outline-none hover:bg-gray-100" title="Editar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 hover:text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>'.
                    ($propio ?
                    '<button class="p-0 m-0 bg-transparent border-none outline-none shadow-none hover:bg-gray-100" title="Eliminar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 m-1 text-red-600 hover:text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>' :
                    '<button class="p-0 m-0 bg-transparent border-none outline-none hover:bg-gray-100" title="Protegido">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 hover:text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 8V6a4 4 0 00-8 0v2m-2 0h12a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8a2 2 0 012-2z" />
                        </svg>
                    </button>'
                    ).'</div>';

                $tds = [
                $acciones,
                $c['idcliente'],
                $c['usuario'],
                $c['fatencion'],
                $c['empresa'],
                $c['razon'],
                $c['rubro'],
                $c['tipo_cliente'],
                $c['origen_nombre'],
                $estado_cliente_con_status,
                $c['estado_atencion'],
                $c['nombres'],
                $c['apellidos'],
                $c['cargo'],
                $telefonos,
                $c['ruc'],
                $emails,
                $c['web'],
                $c['direccion'],
                $c['direccion2'],
                $c['obsdireccion'],
                $c['referencia'],
                $c['distrito'],
                $c['ciudad'],
                $c['cumpleanios'],
                $c['aniversario'],
            ];

            echo '<tr class="hover:bg-gray-50">';
            mostrarTx($tds, 1);
            echo '</tr>';
        }

        $body = ob_get_clean();
        echo json_encode([
            'success'        => true,
            'body'           => $body,
            'total'          => $total,
            'paginationHtml' => ''
        ]);
        exit;
}
    // ======================
    // CONTAR CLIENTES
    // ======================
    if ($action === 'count') {
        $total = $cliente->countAll();
        echo json_encode([
            'success'      => true,
            'totalClients' => (int) $total
        ]);
        exit;
    }
    // ======================
    // AGREGAR - EDITAR
    // ======================
    $seccion = $_POST['seccion_activa'] ?? 'personal';

    $cliente->fecha         = date('Y-m-d');
    $cliente->estadousuario = 1;

    // ======================
    // SECCIÓN PERSONAL Y TRABAJO
    // ======================
    if (in_array($seccion, ['personal', 'trabajo'])) {
        // PERSONAL
        $cliente->nombres     = trim($_POST['nombres'] ?? '');
        $cliente->apellidos   = trim($_POST['apellidos'] ?? '');
        $emails = [];
        // Recorre todos los valores del formulario
        foreach ($_POST as $key => $value) {
            if (preg_match('/^email-\d+$/', $key) && trim($value) !== '') {
                $emails[] = trim($value);
            }
        }
        $cliente->email = implode(',', $emails);
        $cliente->telefono    = trim($_POST['telefono'] ?? '');
        $cliente->celular     = trim($_POST['celular'] ?? '');
        $cliente->celular2    = trim($_POST['celular2'] ?? '');
        $cliente->celular3    = trim($_POST['celular3'] ?? '');
        $cliente->celular4    = trim($_POST['celular4'] ?? '');
        $cliente->direccion   = trim($_POST['direccion'] ?? '');
        $cliente->referencia  = trim($_POST['referencia'] ?? '');
        $cliente->distrito    = trim($_POST['distrito'] ?? '');
        $cliente->distrito2    = trim($_POST['distrito2'] ?? '');
        $cliente->ciudad      = trim($_POST['ciudad'] ?? '');
        $cliente->provincia   = trim($_POST['provincia'] ?? '');
        $cliente->pais        = trim($_POST['pais'] ?? '');
        $cliente->postal      = trim($_POST['postal'] ?? '');
        $cliente->cumpleanios = !empty($_POST['cumpleanios'])
                               ? date('Y-m-d', strtotime($_POST['cumpleanios']))
                               : null;
        $cliente->skype       = trim($_POST['skype'] ?? '');
        $cliente->fatencion   = date('Y-m-d H:i:s');
        $cliente->fechaaviso  = date('Y-m-d H:i:s');
        // TRABAJO
        $cliente->empresa         = trim($_POST['empresa'] ?? '');
        $cliente->razon           = trim($_POST['razon'] ?? '');
        $cliente->ruc             = trim($_POST['ruc'] ?? '');
        $cliente->direccion2      = trim($_POST['direccion2'] ?? '');
        $cliente->obsdireccion    = trim($_POST['obsdireccion'] ?? '');
        $cliente->cargo           = trim($_POST['cargo'] ?? '');
        $cliente->aniversario     = !empty($_POST['aniversario'])
                                   ? date('Y-m-d', strtotime($_POST['aniversario']))
                                   : null;
        $cliente->rubro           = trim($_POST['rubro'] ?? '');
        $cliente->num_empleados   = (int)($_POST['num_empleados'] ?? 0);
        $cliente->web             = trim($_POST['web'] ?? '');
        $cliente->detalle_origen          = trim($_POST['detalle_origen'] ?? '');
        $cliente->origen          = trim($_POST['origen'] ?? '');
        $cliente->prioridad          = trim($_POST['prioridad'] ?? ''); // status_atencion
        $cliente->carta_presentacion = trim($_POST['carta_presentacion'] ?? '0');
        $cliente->catalogo = trim($_POST['catalogo'] ?? '0');
        $cliente->correo = trim($_POST['correo'] ?? '0');
        $cliente->account = '';
        if (!empty($_POST['cuentas'])) {
            $c = is_array($_POST['cuentas']) ? $_POST['cuentas'] : (json_decode($_POST['cuentas'], true) ?? []);
            // Si es lista (["compina", ...]) úsala tal cual; si es mapa (["compina"=>1,...]) filtra claves truthy
            $sel = (array_values($c) === $c) ? $c : array_keys(array_filter($c));

            $cliente->account = $sel ? json_encode($sel) : '';
        }
    }
    $perfilData = [
        'TIPO DE CLIENTE' => $_POST['tipo_cliente'] ?? '',
        'POLÍTICA DE PAGO' => $_POST['politica_pago'] ?? '',
        'TRABAJA CON PROVEEDORES' => $_POST['trabaja_proveedores'] ?? '',
        'PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO' => $_POST['procedimiento_facturacion'] ?? '',
        'FRECUENCIA DE COMPRA' => $_POST['frecuencia_compra'] ?? '',
        'ADICIONALES' => $_POST['adicionales'] ?? ''
    ];
    $cliente->perfil = json_encode($perfilData, JSON_UNESCAPED_UNICODE);
    $cliente->idcliente = trim($_POST['idcliente'] ?? 0);
    // ======================
    // CREAR CLIENTE
    // ======================
    if ($action === 'create') {
        $response = $cliente->create();
        if ($response['success']) {
            echo json_encode([
                'success' => true,
                'id'      => $response['idcliente'],
                'message' => "Cliente guardado con éxito (ID #".$response['idcliente'].")."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Fallo al insertar: ". ($response['message'] ?? 'Error desconocido')
            ]);
        }
    // ======================
    // EDITAR CLIENTE
    // ======================
    }else if($action === 'edit' || $action === 'edit-perfil'){
        $resultado = $cliente->update();
        if ($resultado['success']) {
            echo json_encode([
                'success' => true,
                'message' => $resultado['message']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Fallo al actualizar: " . ($resultado['message'] ?? 'Error desconocido')
            ]);
        }
    }

    // ======================
    // ELIMINAR CLIENTE
    // ======================
        else if ($action === 'delete') {
        $data = json_decode(file_get_contents('php://input'), true);
        $cliente->idcliente = (int)($data['idcliente'] ?? 0);
        if ($cliente->delete()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cliente eliminado correctamente.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el cliente.'
            ]);
        }
        exit;
    }
    // ====================== 
    // LEER CLIENTES ELIMINADOS
    // ======================
    if ($action === 'get_eliminados') {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, (int)($_GET['limit'] ?? 25));
        $ordenarPorFecha = isset($_GET['ordenar_fecha']) ? (bool)$_GET['ordenar_fecha'] : true;
        $filtrarPorFecha = isset($_GET['filtrar_fecha']) ? (bool)$_GET['filtrar_fecha'] : false;
        $fechaAviso = isset($_GET['fecha_aviso']) ? (bool)$_GET['fecha_aviso'] : false;
        $desde = $_GET['desde'] ?? null;
        $hasta = $_GET['hasta'] ?? null;
        $tipo_cliente = $_GET['tipo_cliente'] ?? '';
        $status_atencion = $_GET['status_atencion'] ?? '';
        $origen = $_GET['origen'] ?? '';
        $clientesEliminados = $cliente->readEliminados(
            $page, 
            $limit,
        );
        $totalEliminados = $cliente->countWithOutFilters();
        echo json_encode([
            'success' => true,
            'clientes' => $clientesEliminados,
            'total' => $totalEliminados,
            'page' => $page,
            'limit' => $limit
        ]);
        exit;
    }
if ($action === 'validar_multiples_emails') {
    $correos = explode(',', strtolower(trim($_POST['correos'] ?? '')));
    $idcliente_actual = intval($_POST['idcliente'] ?? 0);
    $duplicados = [];

    foreach ($correos as $correo) {
        $email = trim($correo);
        if (!$email) continue;
        $existe = $cliente->buscarPorEmail($email);
        if ($existe && $existe['idcliente'] != $idcliente_actual) {
            $duplicados[] = array_merge($existe, ['email' => $email]);
        }
    }

    echo json_encode(['duplicados' => $duplicados]);
    exit;
}

    // ======================
// VALIDAR EMAIL DUPLICADO
   // ======================
if ($action === 'validar_email') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $idcliente_actual = intval($_POST['idcliente'] ?? 0);

    $clienteExistente = $cliente->buscarPorEmail($email);

    if ($clienteExistente && $clienteExistente['idcliente'] != $idcliente_actual) {
        echo json_encode([
            'exists' => true,
            'cliente' => $clienteExistente
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit;
}


} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Excepción en la base de datos: " . $e->getMessage()
    ]);
}
?>
