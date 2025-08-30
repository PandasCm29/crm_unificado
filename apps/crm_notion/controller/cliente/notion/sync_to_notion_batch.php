<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-Type: application/json");

require_once __DIR__ . '/../../../models/notion/ClienteNotion.php';
require_once __DIR__ . '/../../../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $clientes = $input['clientes'] ?? [];

    if (!is_array($clientes) || empty($clientes)) {
        echo json_encode(['success' => false, 'mensaje' => 'No hay clientes para sincronizar.']);
        exit;
    }

    $db = new Database();
    $totalSincronizados = 0;
    $errores = [];

    foreach ($clientes as $clienteData) {
        try {
            $clienteNotion = new ClienteNotion();

            // ENCABEZADOS
            $clienteNotion->cuenta = $clienteData['account'];
            // PERSONAL
            $nombre_completo = $clienteData['nombres'];
            $clienteNotion->nombres = $nombre_completo;
            $clienteNotion->apellidos = $clienteData['apellidos'] ?? '';
            
            $clienteNotion->correo = trim($clienteData['email']);
            $clienteNotion->correo_2 = trim($clienteData['email_2']);
            $clienteNotion->correo_3 = trim($clienteData['email_3']);
            $clienteNotion->correo_4 = trim($clienteData['email_4']);
            $clienteNotion->correo_5 = trim($clienteData['email_5']);

            $clienteNotion->celular = $clienteData['celular'] ?? '';
            $clienteNotion->celular_2 = $clienteData['celular2'] ?? '';
            $clienteNotion->celular_3 = $clienteData['celular3'] ?? '';
            $clienteNotion->celular_4 = $clienteData['celular4'] ?? '';
            $clienteNotion->telefono = $clienteData['telefono'] ?? '';
            $clienteNotion->ciudad = $clienteData['ciudad'] ?? '';
            $clienteNotion->provincia = $clienteData['provincia'] ?? '';
            $clienteNotion->pais = $clienteData['pais'] ?? '';
            $clienteNotion->distrito = $clienteData['distrito'] ?? '';
            $clienteNotion->cliente_desde = !empty($clienteData['fatencion'])
                ? date('Y-m-d H:i:s', strtotime($clienteData['fatencion']))
                : "";
            // TRABAJO
            $clienteNotion->empresa = $clienteData['empresa'] ?? '';
            $clienteNotion->razon = $clienteData['razon'] ?? '';
            $clienteNotion->ruc = $clienteData['ruc'] ?? '';
            $clienteNotion->direccion2 = $clienteData['direccion2'] ?? '';
            $clienteNotion->obs_direccion = $clienteData['obsdireccion'] ?? '';
            $clienteNotion->cargo = $clienteData['cargo'] ?? '';            
            $clienteNotion->aniversario = !empty($clienteData['aniversario'])
            
                ? date('Y-m-d', strtotime($clienteData['aniversario']))
                : "";
            $clienteNotion->rubro = $clienteData['rubro'] ?? '';
            $clienteNotion->web = $clienteData['web'] ?? '';
            $clienteNotion->origen = $clienteData['detalle_origen'] ?? '';
            $clienteNotion->tipo_cliente = $clienteData['origen'] ?? '';
            $clienteNotion->estado_atencion = $clienteData['prioridad'] ?? '';
            $clienteNotion->carta_presentacion = $clienteData['carta_presentacion'] ?? false;
            $clienteNotion->catalogo = $clienteData['catalogo'] ?? false;
            $clienteNotion->pack_promocional = $clienteData['correo'] ?? false;
            // Sincro
            $clienteNotion->sync_source = 'CRM';
            $clienteNotion->notion_page_id = $clienteData['notion_page_id'];
            $idusuario = $clienteData['idusuario'];

            $parts = preg_split('/\s+/', trim($nombre_completo));
            $primer_nombre = $parts[0];
            $clienteNotion->titulo =  $primer_nombre . " (".$clienteNotion->razon.")";
            // PERFIL
            // PERFIL
            $perfilArray = [];
            if (isset($clienteData['perfil'])) {
                if (is_string($clienteData['perfil'])) {
                    $decoded = json_decode($clienteData['perfil'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $perfilArray = $decoded;
                    }
                } elseif (is_array($clienteData['perfil'])) {
                    $perfilArray = $clienteData['perfil'];
                }
            }

            $perfilData = [
                'TIPO DE CLIENTE' => $perfilArray['TIPO DE CLIENTE'] ?? '',
                'POLÍTICA DE PAGO' => $perfilArray['POLÍTICA DE PAGO'] ?? '',
                'TRABAJA CON PROVEEDORES' => $perfilArray['TRABAJA CON PROVEEDORES'] ?? '',
                'PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO' => $perfilArray['PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO'] ?? '',
                'FRECUENCIA DE COMPRA' => $perfilArray['FRECUENCIA DE COMPRA'] ?? '',
                'ADICIONALES' => $perfilArray['ADICIONALES'] ?? '',
            ];

            $clienteNotion->perfil = $perfilData; // <- ESTE es el que usa ClienteNotion::toNotionPayload()

            // Aquí podrías buscar notion_page_id en tu BD si aplica
            if ($idusuario) {
                $query = "SELECT notion_page_id FROM usuarios WHERE idusuario = :idusuario";
                $stmt = $db->getConnection()->prepare($query);
                $stmt->execute(['idusuario' => $idusuario]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $clienteNotion->ejecutiva = $result['notion_page_id'] ?? null;
            }
            

            if($clienteNotion->ejecutiva){
                $result = $clienteNotion->syncToNotion();
                if (isset($result['id'])) {
                    if(empty($clienteData['notion_page_id'])){
                        $query = "UPDATE clientes SET notion_page_id = :notion_page_id WHERE idcliente = :idcliente";
                        $stmt = $db->getConnection()->prepare($query);
                        if ($stmt->execute([
                            'notion_page_id' => $result['id'],
                            'idcliente' => $clienteData['idcliente']
                        ])) {
                            $totalSincronizados++;
                        }else{
                            $errores[] = [
                                'id' => $clienteData['idcliente'] ?? null,
                                'error' => "Error al modificar"
                            ];
                        }
                    }else{
                        $totalSincronizados++;
                    }

                } else {
                    $errores[] = [
                        'id' => $clienteData['idcliente'] ?? null,
                        'error' => $result['message']
                    ];
                }
            }else{
                $errores[] = [
                    'id' => $clienteData['idcliente'] ?? null,
                    'error' => 'No tiene idusuario.'
                ];
            }
        



        } catch (Exception $e) {
            $errores[] = [
                'id' => $clienteData['idcliente'] ?? null,
                'error' => $e->getMessage()
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'total' => $totalSincronizados,
        'mensaje' => $totalSincronizados > 0 
            ? "$totalSincronizados clientes enviados."
            : "⚠ No se enviaron clientes a Notion.",
        'errores' => $errores
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage()
    ]);
}
