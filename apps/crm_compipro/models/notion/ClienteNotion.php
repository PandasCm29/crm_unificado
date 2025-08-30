<?php
// require_once __DIR__ . '/../../config/vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/cliente_conexion.php';

// use GuzzleHttp\Client;

class ClienteNotion
{
    private $client;
    private $token;
    private $database_id;

    public $notion_page_id;
    public $nombres;
    public $apellidos;
    public $cargo;
    public $celular;
    public $email;
    public $direccion2;
    public $ciudad;
    public $distrito;
    public $empresa;
    public $razon;
    public $tipo_cliente;
    public $origen;
    public $cliente_desde;
    public $ultimo_contacto;
    public $ruc;
    public $web;
    public $status_proyecto;
    public $proyectos;
    public $cuenta;
    public $ejecutiva=null;
    public $reuniones;
    public $progreso;
    public $sync_source;

    private static $detallesOrigen = [
        1 => "PÁGINA WEB", //REFERIDO, FIDELIZADO, OP COMERCIAL, MKTG, OTROS
        2 => "MAILING",
        3 => "FACEBOOK",
        4 => "CHAT",
        5 => "LLAMADAS",
        6 => "REFERIDOS",
        7 => "OTROS",
    ];

    private static $statusInvertido = [
        "Activo" => 1,
        "Archivado" => 0
    ];
    private static $detallesOrigenInvertido = [
        "PÁGINA WEB" => 1,
        "MAILING" => 2,
        "FACEBOOK" => 3,
        "CHAT" => 4,
        "LLAMADAS" => 5,
        "REFERIDOS" => 6,
        "OTROS" => 7,
    ];

    private static $tiposCliente = [
        1 => "POTENCIALES",
        2 => "FRECUENTES",
        3 => "OCASIONALES",
        4 => "TERCERIZADORES",
        5 => "PROSPECTO",
        6 => "NO POTENCIAL",
        7 => "MAL CLIENTE",
    ];

    private static $tiposClienteInvertido = [
        "POTENCIALES" => 1,
        "FRECUENTES" => 2,
        "OCASIONALES" => 3,
        "TERCERIZADORES" => 4,
        "PROSPECTO" => 5,
        "NO POTENCIAL" => 6,
        "MAL CLIENTE" => 7,
    ];



    public function __construct()
    {
        // $this->client = new Client();
        $this->token = Database::getNotionToken();
        $this->database_id = Database::getClientesNotionDatabaseId();
    }

    // Sincronizar cliente a Notion (crear o actualizar)
    public function syncToNotion($delete=false)
    {
        if ($this->sync_source === 'Notion') {
            return false; // Avoid loop if change came from Notion
        }

        $version = Database::getNotionVersion();
        $url = $this->notion_page_id
            ? "https://api.notion.com/v1/pages/{$this->notion_page_id}"
            : 'https://api.notion.com/v1/pages';
        $method = $this->notion_page_id ? 'PATCH' : 'POST';

        if(!$delete){
            // Validate and format dates
            try {
                $cliente_desde = $this->cliente_desde
                    ? ['start' => (new DateTime($this->cliente_desde, new DateTimeZone('-05:00')))->format('Y-m-d\TH:i:s.vP')]
                    : null;
            } catch (Exception $e) {
                $cliente_desde = null;
                error_log('Invalid date for Cliente Desde: ' . $e->getMessage());
            }

            // Validate email
            $email = $this->email && filter_var($this->email, FILTER_VALIDATE_EMAIL) ? $this->email : null;

            // Validate phone number
            $telefono = $this->celular && preg_match('/^\+?\d{7,15}$/', trim($this->celular)) ? trim($this->celular) : null;

            // Validate RUC
            $ruc = $this->ruc && is_numeric($this->ruc) ? (float)$this->ruc : null;

            //Convertimos de numero a texto (origen )
            $detalleOrigenTexto = isset(self::$detallesOrigen[$this->origen]) ? self::$detallesOrigen[$this->origen] : 'OTROS';

            //Convertimos de num a texto(tipocliente)
            $tipoClienteTexto = isset(self::$tiposCliente[$this->tipo_cliente]) ? self::$tiposCliente[$this->tipo_cliente] : 'POTENCIALES';

            // Log input data for debugging
            error_log('Notion request data: ' . json_encode([
                'celular' => $this->celular,
                'telefono' => $telefono,
                'email' => $email,
                'ruc' => $ruc,
                'cliente_desde' => $cliente_desde,
                'id_ejecutiva'=>$this->ejecutiva
            ]));
        }

        $body = [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
                'Notion-Version' => $version,
            ],
            'json' => $delete ? [
                'properties'=>[ 
                    'Status' => ['select' => ['name' => 'Archivado']]
                    ]
                ] : ($this->notion_page_id
                // UPDATE - PATCH
                ? [
                    'properties' => [
                        'Nombres' => ['title' => $this->nombres ? [['text' => ['content' => $this->nombres]]] : [[]]],
                        'Apellidos' => ['rich_text' => $this->apellidos ? [['text' => ['content' => $this->apellidos]]] : []],
                        'Cargo' => ['rich_text' => $this->cargo ? [['text' => ['content' => $this->cargo]]] : []],
                        'Teléfono' => ['phone_number' => $telefono],
                        'Correo' => ['email' => $email],
                        'Dirección' => ['rich_text' => $this->direccion2 ? [['text' => ['content' => $this->direccion2]]] : []],
                        'Ciudad' => ['rich_text' => $this->ciudad ? [['text' => ['content' => $this->ciudad]]] : []],
                        'Distrito' => ['rich_text' => $this->distrito ? [['text' => ['content' => $this->distrito]]] : []],
                        'Razón Comercial' => ['rich_text' => $this->empresa ? [['text' => ['content' => $this->empresa]]] : []],
                        'Razón Social' => ['rich_text' => $this->razon ? [['text' => ['content' => $this->razon]]] : []],
                        'Tipo de Cliente' => ['select' => ['name' => $tipoClienteTexto]],
                        // 'Último Contacto' => ['last_edited_time' => $ultimo_contacto],
                        'RUC' => ['number' => $ruc],
                        'Sitio Web' => ['rich_text' => $this->web ? [['text' => ['content' => $this->web]]] : []],
                        'Ejecutiva' => ['relation' => [['id' => $this->ejecutiva]]],
                        'Origen' => ['select' => ['name' => $detalleOrigenTexto]],
                    ],
                ]
                // CREATE - POST
                : [
                    'parent' => ['database_id' => $this->database_id],
                    'icon' => [
                        'external' => [
                            'url' => 'https://www.notion.so/icons/user-circle-filled_brown.svg',
                        ],
                    ],
                    'properties' => [
                        'Nombres' => ['title' => $this->nombres ? [['text' => ['content' => $this->nombres]]] : [[]]],
                        'Apellidos' => ['rich_text' => $this->apellidos ? [['text' => ['content' => $this->apellidos]]] : []],
                        'Cargo' => ['rich_text' => $this->cargo ? [['text' => ['content' => $this->cargo]]] : []],
                        'Teléfono' => ['phone_number' => $telefono],
                        'Correo' => ['email' => $email],
                        'Dirección' => ['rich_text' => $this->direccion2 ? [['text' => ['content' => $this->direccion2]]] : []],
                        'Ciudad' => ['rich_text' => $this->ciudad ? [['text' => ['content' => $this->ciudad]]] : []],
                        'Distrito' => ['rich_text' => $this->distrito ? [['text' => ['content' => $this->distrito]]] : []],
                        'Razón Comercial' => ['rich_text' => $this->empresa ? [['text' => ['content' => $this->empresa]]] : []],
                        'Razón Social' => ['rich_text' => $this->razon ? [['text' => ['content' => $this->razon]]] : []],
                        'Tipo de Cliente' => ['select' => ['name' => $tipoClienteTexto]],
                        'Cliente Desde' => ['date' => $cliente_desde],
                        'RUC' => ['number' => $ruc],
                        'Sitio Web' => ['rich_text' => $this->web ? [['text' => ['content' => $this->web]]] : []],
                        'Status Proy.' => ['status' => ['name' => 'En Curso']],
                        'Proyectos' => ['relation' => []],
                        'Cuenta' => ['relation' => [['id' => '214fbb81-3b4c-8147-9c63-eb4181a000bd']]],
                        'Progreso' => ['status' => ['name' => 'Tentativo']],
                        'Ejecutiva' => ['relation' => [['id' => $this->ejecutiva]]],
                        'Reuniones' => ['relation' => []],
                        'Origen' => ['select' => ['name' => $detalleOrigenTexto]], //$this->origen ??
                    ],
                ])
        ];

        try {
            $response = $this->client->request($method, $url, $body);
            $result = json_decode($response->getBody(), true);
            $this->notion_page_id = $result['id'];
            return $this->notion_page_id;
        } catch (Exception $e) {
            // $error_response = method_exists($e, 'getResponse') && $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            error_log('Error sincronizando con Notion: ' . $e->getMessage());
            return false;
        }
    }

    // Sincronizar desde Notion al CRM
    public static function syncFromNotion($db)
    {
        // try {
        //     date_default_timezone_set('America/Lima'); // Zona horaria
        //     $client = new Client();
        //     $token = Database::getNotionToken();
        //     $database_id = Database::getClientesNotionDatabaseId();
        //     $version = Database::getNotionVersion();

        //     $url = "https://api.notion.com/v1/databases/$database_id/query";
        //     $body = [
        //         'headers' => [
        //             'Authorization' => "Bearer $token",
        //             'Content-Type' => 'application/json',
        //             'Notion-Version' => $version,
        //         ],
        //     ];

        //     $response = $client->post($url, $body);
        //     $data = json_decode($response->getBody(), true);

        //     $today = (new DateTime())->format('Y-m-d');
        //     $contador = 0;
        //     foreach ($data['results'] as $page) {
        //         $page_id = $page['id'];
        //         $last_edited = $page['last_edited_time'];
        //         $nombres = $page['properties']['Nombres']['title'][0]['text']['content'] ?? '';
                
        //         // Verificar si last_edited es del día actual
        //         $last_edited_with_time_zone = (new DateTime($last_edited, new DateTimeZone('UTC')))
        //                 ->setTimezone(new DateTimeZone('America/Lima'));
        //         $last_edited_date = $last_edited_with_time_zone->format('Y-m-d');
        //         if ($last_edited_date !== $today) {
        //             continue; // Saltar si no fue editado hoy
        //         }
        //         // if ($nombres !== "PRUEBA 2") continue; //|| $nombres !== "PRUEBA 2"
        //         $apellidos = $page['properties']['Apellidos']['rich_text'][0]['text']['content'] ?? '';
        //         $cargo = $page['properties']['Cargo']['rich_text'][0]['text']['content'] ?? '';
        //         $telefono = $page['properties']['Teléfono']['phone_number'] ?? null;
        //         $email = $page['properties']['Correo']['email'] ?? null;
        //         $direccion = $page['properties']['Dirección']['rich_text'][0]['text']['content'] ?? '';
        //         $ciudad = $page['properties']['Ciudad']['rich_text'][0]['text']['content'] ?? '';
        //         $distrito = $page['properties']['Distrito']['rich_text'][0]['text']['content'] ?? '';
        //         $empresa = $page['properties']['Razón Comercial']['rich_text'][0]['text']['content'] ?? '';
        //         $razon = $page['properties']['Razón Social']['rich_text'][0]['text']['content'] ?? '';
        //         $origen = $page['properties']['Origen']['select']['name'] ?? '';
        //         $tipo_cliente = $page['properties']['Tipo de Cliente']['select']['name'] ?? '';
        //         // Convierte texto de Notion a número para guardar en la base de datos
        //         $detalleOrigenID = isset(self::$detallesOrigenInvertido[$origen]) ? self::$detallesOrigenInvertido[$origen] : 7; // Por defecto: OTROS
        //         // Converte texto de Tipo Cliente a número
        //         $tipoClienteID = isset(self::$tiposClienteInvertido[$tipo_cliente]) ? self::$tiposClienteInvertido[$tipo_cliente] : 1;
        //         $cliente_desde = $page['properties']['Cliente Desde']['date']['start'] ?? null;
        //         $ultimo_contacto = $page['properties']['Último Contacto']['last_edited_time'] ?? null;
        //         $fecha_aviso_mysql = $ultimo_contacto
        //             ? (new DateTime($ultimo_contacto, new DateTimeZone('UTC')))
        //                 ->setTimezone(new DateTimeZone('America/Lima'))
        //                 ->format('Y-m-d H:i:s')
        //             : null;
        //         $ruc = $page['properties']['RUC']['number'] ?? 0;
        //         $web = $page['properties']['Sitio Web']['rich_text'][0]['text']['content'] ?? '';
        //         $status_proyecto = $page['properties']['Status Proyecto']['status']['name'] ?? '';
        //         $proyectos = $page['properties']['Proyectos']['relation'] ?? [];
        //         $cuenta = $page['properties']['Cuenta']['relation'][0]['id'] ?? '';
        //         $ejecutiva = $page['properties']['Ejecutiva']['relation'][0]['id'] ?? '';
        //         $reuniones = $page['properties']['Reuniones']['relation'] ?? [];
        //         $progreso = $page['properties']['Progreso']['status']['name'] ?? '';
        //         $status = $page['properties']['Status']['select']['name'] ?? '';
        //         $statusValue = isset(self::$statusInvertido[$status]) ? self::$statusInvertido[$status] : 1; // POR DEFECTO ACTIVO

        //         // Buscar cliente por notion_page_id
        //         $query = "SELECT * FROM clientes WHERE notion_page_id = :notion_page_id";
        //         $stmt = $db->prepare($query);
        //         $stmt->execute(['notion_page_id' => $page_id]);
        //         $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        //         $clienteModel = nuevoCliente();
        //         $clienteModel->reset();
        //         $clienteModel->nombres = $nombres;
        //         $clienteModel->apellidos = $apellidos;
        //         $clienteModel->cargo = $cargo;
        //         $clienteModel->telefono = $telefono;
        //         $clienteModel->email = $email;
        //         $clienteModel->direccion = $direccion;
        //         $clienteModel->ciudad = $ciudad;
        //         $clienteModel->distrito = $distrito;
        //         $clienteModel->empresa = $empresa;
        //         $clienteModel->razon = $razon;
        //         $clienteModel->detalle_origen = $detalleOrigenID;
        //         $clienteModel->origen = $tipoClienteID;
        //         $clienteModel->fatencion = $cliente_desde;
        //         $clienteModel->fechaaviso = $fecha_aviso_mysql;
        //         $clienteModel->ruc = $ruc;
        //         $clienteModel->web = $web;
        //         $clienteModel->estadousuario = $statusValue;
        //         $clienteModel->notion_page_id = $page_id;
        //         $clienteModel->sync_source = 'Notion';
        //         // error_log("EJECUTIVA ID: " . $ejecutiva);
        //         if($ejecutiva){
        //             $query = "SELECT idusuario FROM usuarios WHERE notion_page_id = :notion_page_id";
        //             $stmt = $db->prepare($query);
        //             $stmt->execute(['notion_page_id' => $ejecutiva]);
        //             $result = $stmt->fetch(PDO::FETCH_ASSOC);
        //             $clienteModel->idusuario = $result['idusuario'] ?? 0;
        //         }

        //         if ($cliente && strtotime($cliente['last_sync_time']) < strtotime($last_edited)) {
        //             // Actualizar cliente existente
        //             $clienteModel->idcliente = $cliente['idcliente'];
        //             $result = $clienteModel->update();
        //             // error_log('RESULTADO: ' . $result['message']);
        //             if($result['success'] && $cliente['estadousuario'] && !$statusValue){
        //                 $last_edited_date_time = $last_edited_with_time_zone->format('Y-m-d H:i:s');
        //                 error_log( $ultimo_contacto === $last_edited_date_time);
        //                 $clienteModel->delete($last_edited_date_time);
        //             }
        //             $contador=$contador+1;
        //         } elseif (!$cliente) {
        //             // Crear nuevo cliente
        //             // $clienteModel->estadousuario = 1;
        //             $result =$clienteModel->create();
        //             // error_log("RESUTLADO CREAR:" . $result['message']);
        //             $contador=$contador+1;
        //         }
        //     }
        //     return true;
        // } catch (Exception $e) {
        //     error_log('Error sincronizando desde Notion: ' . $e->getMessage());
        //     return false;
        // }finally{
        //     error_log('Clientes sincronizados: ' . $contador);
        // }
    }

    
}
