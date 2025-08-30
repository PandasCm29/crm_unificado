<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-Type: application/json");

// Captura errores y excepciones
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "type" => "PHP_ERROR",
        "message" => "Error interno en el código",
        "details" => [
            "error" => $errstr,
            "file" => $errfile,
            "line" => $errline
        ]
    ]);
    exit;
});

set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "type" => "EXCEPTION",
        "message" => "Error interno en el código",
        "details" => [
            "error" => $exception->getMessage()
        ]
    ]);
    exit;
});
// require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/notion/ClienteNotion.php';


try {
    $db = new Database();
    $cliente = nuevoCliente();
    $cliente->reset();
    $clienteNotion = new ClienteNotion();

    $request = $_SERVER['REQUEST_METHOD'];
    if ($request === 'POST') {
        $seccion = $_POST['seccion_activa'] ?? 'personal';
        $cliente->estadousuario = 1;
        // PERSONAL
        if (in_array($seccion, ['personal', 'trabajo'])) {
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
            $cliente->distrito2   = trim($_POST['distrito2'] ?? '');
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
        }
        // TRABAJO
        if ($seccion === 'trabajo') {
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
            $cliente->detalle_origen  = trim($_POST['detalle_origen'] ?? '');
            $cliente->origen          = trim($_POST['origen'] ?? '');
            $cliente->prioridad          = trim($_POST['prioridad'] ?? ''); // status_atencion
            $cliente->carta_presentacion = trim($_POST['carta_presentacion'] ?? '0');
            $cliente->catalogo = trim($_POST['catalogo'] ?? '0');
            $cliente->correo = trim($_POST['correo'] ?? '0');
           
            $cliente->idcliente    = trim($_POST['idcliente'] ?? null);
           

            

        }
        // IMPORTANTE

        $action = $_REQUEST['action'] ?? 'create';
        // Si idcliente existe, buscar notion_page_id
        if ($action === "edit") {
            $cliente->idcliente = trim($_POST['idcliente'] ?? 0);
            $query = "SELECT notion_page_id FROM clientes WHERE idcliente = :idcliente";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->execute(['idcliente' => $cliente->idcliente]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $cliente->notion_page_id = $result['notion_page_id'] ?? null;
            $clienteNotion->notion_page_id = $cliente->notion_page_id;
        }

        // Copiar datos a ClienteNotion
        $clienteNotion->cliente_desde = $cliente->fatencion;
        $clienteNotion->ultimo_contacto = $cliente->fechaaviso;
        $clienteNotion->empresa = $cliente->empresa;
        $clienteNotion->razon = $cliente->razon;
        $clienteNotion->tipo_cliente = $cliente->origen;
        $clienteNotion->origen = $cliente->detalle_origen;
        $clienteNotion->nombres = $cliente->nombres;
        $clienteNotion->apellidos = $cliente->apellidos;
        $clienteNotion->cargo = $cliente->cargo;
        $clienteNotion->celular = $cliente->celular;
        $clienteNotion->ruc = $cliente->ruc;
        $clienteNotion->correo = $emails[0];
        $clienteNotion->web = $cliente->web;
        $clienteNotion->direccion2 = $cliente->direccion2;
        $clienteNotion->distrito = $cliente->distrito;
        $clienteNotion->ciudad = $cliente->ciudad;
        $clienteNotion->sync_source = $cliente->sync_source;
        $clienteNotion->cuenta     = trim($_POST['Cuenta'] ?? ''); 

        // Guardar en la base de datos
        $saved = $action === 'create' ? $cliente->create() : $cliente->update();
        if ($saved['success']) {
            if(isset($saved['idcliente'])){
                $cliente->idcliente = $saved['idcliente'];
            }
            $idusuario = $cliente->idusuario;
            if($idusuario){
                $query = "SELECT notion_page_id FROM usuarios WHERE idusuario = :idusuario";
                $stmt = $db->getConnection()->prepare($query);
                $stmt->execute(['idusuario' => $idusuario]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $clienteNotion->ejecutiva = $result['notion_page_id'] ?? null;
            }

            // Sincronizar con Notion
            $notion_page_id = $clienteNotion->syncToNotion();
            if ($notion_page_id) {
                // echo json_encode(['success' => true, 'message' => 'Cliente sincronizado con Notion']);
                $cliente->notion_page_id = $notion_page_id;
                $resultUpdate = $cliente->update(); // Actualizar notion_page_id
                if($resultUpdate['success']){
                    echo json_encode(['success' => $resultUpdate['success'], 'message' => 'Cliente sincronizado con Notion']);
                }else{
                    echo json_encode(['success' => false, 'message' => $resultUpdate['message']]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Cliente guardado localmente, pero error al sincronizar con Notion']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => $saved['message']]);
        }
    } else if($request === 'DELETE'){
        $data = json_decode(file_get_contents('php://input'), true);
        $cliente->idcliente = (int)($data['idcliente'] ?? 0);
        if(!$cliente->idcliente) throw new Exception("ID inválido");
        if ($cliente->delete()) {
            $query = "SELECT notion_page_id FROM clientes WHERE idcliente = :idcliente";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->execute(['idcliente' => $cliente->idcliente]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $clienteNotion->notion_page_id = $result['notion_page_id'] ?? null;
            if(!$clienteNotion) throw new Exception("No se encontró el id de la página de Notion para este cliente");
            $result = $clienteNotion->syncToNotion(true);
            if($result){
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente eliminado correctamente.'
                ]);
            }else {
                echo json_encode(['success' => false, 'message' => 'Cliente eliminado localmente, pero error al sincronizar con Notion']);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el cliente.'
            ]);
        }
        exit;
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    exit;
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Excepción en la base de datos: " . $e->getMessage()
    ]);
} catch (Exception $e){
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ]);
}
