<?php
require_once __DIR__ . '/../../config/vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/cliente_conexion.php';
require_once __DIR__ .'/NotionPropertyHelper.php';
require_once __DIR__ .'/NotionPropertyGetter.php';
require_once __DIR__ .'/NotionClient.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ClienteNotion
{
    private $token;
    private $database_id;

    public $notion_page_id;
    public $sync_source;
    // ENCABEZADO
    public $cuenta;
    public $progreso;
    // PERSONAL #################################
    public $titulo;
    public $nombres;
    public $apellidos;
    public $ejecutiva=null;
    public $correo="";
    public $correo_2="";
    public $correo_3="";
    public $correo_4="";
    public $correo_5="";
    public $celular;
    public $celular_2;
    public $celular_3;
    public $celular_4;
    public $telefono;
    public $ciudad;
    public $provincia;
    public $pais;
    public $distrito;
    public $cliente_desde;
    public $ultimo_contacto;
    // TRABAJO#################################
    public $empresa;
    public $razon;
    public $ruc;
    public $direccion2;
    public $obs_direccion;
    public $cargo;
    public $aniversario;
    public $rubro;
    public $web;
    public $origen;
    public $tipo_cliente;
    public $estado_atencion;
    public $carta_presentacion;
    public $catalogo;
    public $pack_promocional;
    // ASCOCIADO#################################
    public $reuniones;
    public $status_proyecto;
    public $proyectos; 
    public $perfil = []; 


    private static $detallesOrigen = [
        1 => "PÁGINA WEB", //REFERIDO, FIDELIZADO, OP COMERCIAL, MKTG, OTROS
        2 => "MAILING",
        3 => "FACEBOOK",
        4 => "CHAT",
        5 => "LLAMADAS",
        6 => "REFERIDOS",
        7 => "OTROS",
        8 => "CAMPAÑA",
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
        "CAMPAÑA" => 8,
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
    private static $estadoAtencion = [
        "In" =>"Contacto Inicial",
        "RC" =>"Retomar Contacto",
        "PC" =>"Pendientes por Cotizar",
        "C" =>"Cotizado",
        "VR" =>"Venta Realizada",
        "VNR" =>"Venta No Realizada",
        "PE" =>"Producto Entregado",
    ];
                                    
    private static $estadoAtencionInvertido = [
        "Contacto Inicial" =>"In",
        "Retomar Contacto" =>"RC",
        "Pendientes por Cotizar" =>"PC",
        "Cotizado" =>"C",
        "Venta Realizada" =>"VR",
        "Venta No Realizada" =>"VNR",
        "Producto Entregado" =>"PE",
    ];



    public function __construct()
    {
        $this->token = Database::getNotionToken();
        $this->database_id = Database::getClientesNotionDatabaseId();
    }
    public function toNotionPayload(bool $delete): array
    {
        $creating = empty($this->notion_page_id);
        $cuentas = [
            '214fbb81-3b4c-8147-9c63-eb4181a000bd',//'compina'
            '214fbb81-3b4c-81db-a106-ecb412eae33d', // 'compipro'
        ];
        if(!$delete){
            // Validate and format dates
            try {
                $cliente_desde = $this->cliente_desde
                    ? ['start' => (new DateTime($this->cliente_desde, new DateTimeZone('-05:00')))->format('Y-m-d\TH:i:s.vP')]
                    : null;
                $aniversario = $this->aniversario
                    ? ['start' => (new DateTime($this->aniversario, new DateTimeZone('-05:00')))->format('Y-m-d\TH:i:s.vP')]
                    : null;
            } catch (Exception $e) {
                $cliente_desde = null;
                $aniversario = null;
                error_log('Invalid date for Cliente Desde or Aniversario: ' . $e->getMessage());
            }

            // Validate email
            $email = $this->correo && filter_var($this->correo, FILTER_VALIDATE_EMAIL) ? $this->correo : "";
            $email_2 = $this->correo_2 && filter_var($this->correo_2, FILTER_VALIDATE_EMAIL) ? $this->correo_2 : "";
            $email_3 = $this->correo_3 && filter_var($this->correo_3, FILTER_VALIDATE_EMAIL) ? $this->correo_3 : "";
            $email_4 = $this->correo_4 && filter_var($this->correo_4, FILTER_VALIDATE_EMAIL) ? $this->correo_4 : "";
            $email_5 = $this->correo_5 && filter_var($this->correo_5, FILTER_VALIDATE_EMAIL) ? $this->correo_5 : "";

            // Validate phone number
            $telefono = $this->telefono && preg_match('/^\+?\d{7,15}$/', trim($this->telefono)) ? trim($this->telefono) : null;
            $celular = $this->celular && preg_match('/^\+?\d{7,15}$/', trim($this->celular)) ? trim($this->celular) : null;
            $celular_2 = $this->celular_2 && preg_match('/^\+?\d{7,15}$/', trim($this->celular_2)) ? trim($this->celular_2) : null;
            $celular_3 = $this->celular_3 && preg_match('/^\+?\d{7,15}$/', trim($this->celular_3)) ? trim($this->celular_3) : null;
            $celular_4 = $this->celular_4 && preg_match('/^\+?\d{7,15}$/', trim($this->celular_4)) ? trim($this->celular_4) : null;

            // Validate RUC
            $ruc = $this->ruc && is_numeric($this->ruc) ? (float)$this->ruc : null;

            // Nombre completo
            $nombre_completo = $this->nombres . ", " . $this->apellidos;

            //Convertimos de numero a texto (origen )
            $detalleOrigenTexto = isset(self::$detallesOrigen[$this->origen]) ? self::$detallesOrigen[$this->origen] : '';

            //Convertimos de num a texto(tipocliente)
            $tipoClienteTexto = isset(self::$tiposCliente[$this->tipo_cliente]) ? self::$tiposCliente[$this->tipo_cliente] : '';

            //Convertimos de texto a texto(estado de atencion)
            $estadoAtencionTexto = isset(self::$estadoAtencion[$this->estado_atencion]) ? self::$estadoAtencion[$this->estado_atencion] : ''; 
            $cuentasNotion = [
                'compina' => '214fbb81-3b4c-8147-9c63-eb4181a000bd',
                'compipro' => '214fbb81-3b4c-81db-a106-ecb412eae33d'
            ];

            $a = is_array($v=$this->cuenta) ? $v : (($d=json_decode((string)$v,true))!==null ? $d : ($v?[$v]:[]));
            $sel = (array_values($a)===$a) ? $a : array_keys(array_filter($a));
            $accounts_ids = array_values(array_intersect_key($cuentasNotion, array_flip($sel)));
            // $cuentaSeleccionada = $this->cuenta;  // Será 'compina' o 'compipro'
            // $idCuenta = $cuentasNotion[$cuentaSeleccionada] ?? "";  // Por si acaso está vacío, usa Compina como valor estándar.


            // Log input data for debugging
            // error_log('Notion request data: ' . json_encode([
            //     'celular' => $this->celular,
            //     'telefono' => $telefono,
            //     'email' => $email,
            //     'ruc' => $ruc,
            //     'cliente_desde' => $cliente_desde,
            //     'id_ejecutiva'=>$this->ejecutiva
                
            // ]));
            $perfilArray = is_array($this->perfil)
            ? $this->perfil
            : (json_decode((string)$this->perfil, true) ?: []);
        }
        return $delete ? [
                'properties'=>[ 
                    'Status' => NotionPropertyHelper::select('Archivado')
                    ]
                ] : ($creating?
                // CREATE - POST
                [
                    'parent' => ['database_id' => $this->database_id],
                    'icon'=>NotionPropertyHelper::icon('https://www.notion.so/icons/user-circle-filled_brown.svg'),
                    'properties' => [
                        // ENCABEZADO
                        'Cuenta' => NotionPropertyHelper::relation($accounts_ids),
                        'Status' => NotionPropertyHelper::select('Activo'),
                        'Progreso' => NotionPropertyHelper::status('Base de Datos'),
                        // PERSONAL
                        // TODO: Agregar=> Celular Principal, Provincia, País
                        'Nombres' => NotionPropertyHelper::title($this->titulo),
                        'Nombres y Apellidos' => NotionPropertyHelper::richText($nombre_completo),
                        'Ejecutiva' => NotionPropertyHelper::relation([$this->ejecutiva]),
                        'Correo' => NotionPropertyHelper::email($email),
                        'Correo_2' => NotionPropertyHelper::email($email_2),
                        'Correo_3' => NotionPropertyHelper::email($email_3),
                        'Correo_4' => NotionPropertyHelper::email($email_4),
                        'Correo_5' => NotionPropertyHelper::email($email_5),
                        'Celular Principal' => NotionPropertyHelper::phone_number($celular),
                        'Cel. Alternativo_1' => NotionPropertyHelper::phone_number($celular_2),
                        'Cel. Alternativo_2' => NotionPropertyHelper::phone_number($celular_3),
                        'Cel. Alternativo_3' => NotionPropertyHelper::phone_number($celular_4),
                        'Teléfono' => NotionPropertyHelper::phone_number($telefono),
                        'Ciudad' => NotionPropertyHelper::richText($this->ciudad),
                        'Provincia' => NotionPropertyHelper::richText($this->provincia),
                        'País' => NotionPropertyHelper::richText($this->pais),
                        'Distrito' => NotionPropertyHelper::richText($this->distrito),
                        'Cliente Desde' => NotionPropertyHelper::date($cliente_desde),
                        // TRABAJO
                        // TODO: Agregar=> Obs. Dirección, Aniversario, Rubro, Estado de Atención
                        'Razón Comercial' => NotionPropertyHelper::richText($this->empresa),
                        'Razón Social' => NotionPropertyHelper::richText($this->razon),
                        'RUC' => NotionPropertyHelper::number($ruc),
                        'Dirección' => NotionPropertyHelper::richText($this->direccion2),
                        'Obs. Dirección' => NotionPropertyHelper::richText($this->obs_direccion),
                        'Cargo' => NotionPropertyHelper::richText($this->cargo),
                        'Aniversario' => NotionPropertyHelper::date($aniversario),
                        'Rubro' => NotionPropertyHelper::richText($this->rubro),
                        'Página Web' => NotionPropertyHelper::richText($this->web),
                        'Detalle Origen' => NotionPropertyHelper::select($detalleOrigenTexto), //$this->origen ?? 
                        'Tipo de Cliente' => NotionPropertyHelper::select($tipoClienteTexto),
                        'Estado de Atención' => NotionPropertyHelper::select($estadoAtencionTexto),
                        'Carta Presentación' => NotionPropertyHelper::checkbox($this->carta_presentacion),
                        'Catálogo' => NotionPropertyHelper::checkbox($this->catalogo),
                        'Pack Promocional' => NotionPropertyHelper::checkbox($this->pack_promocional),
                        //perfil 
                        'Tipo_cliente_CRM'                             => NotionPropertyHelper::richText($perfilArray['TIPO DE CLIENTE'] ?? ''),
                        'Política de Pago'                             => NotionPropertyHelper::richText($perfilArray['POLÍTICA DE PAGO'] ?? ''),
                        'Trabaja con Proveedores'                      => NotionPropertyHelper::richText($perfilArray['TRABAJA CON PROVEEDORES'] ?? ''),
                        'Procedim. Especial en facturación y despacho' => NotionPropertyHelper::richText($perfilArray['PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO'] ?? ''),
                        'Frecuencia de compra'                         => NotionPropertyHelper::richText($perfilArray['FRECUENCIA DE COMPRA'] ?? ''),
                        'Adicionales'                                  => NotionPropertyHelper::richText($perfilArray['ADICIONALES'] ?? ''),

                        // ASOCIADO
                        // TODO: Agregar=> 1er Pro. Asociados, 2da Pro. Asociados
                        // 'Proyectos' => NotionPropertyHelper::relation(['']),
                        // 'Reuniones' => NotionPropertyHelper::relation([]),
                        // 'Status Proy.' => NotionPropertyHelper::status('En Curso'),

                    ],
                ]:                
                // UPDATE - PATCH
                [
                    'properties' => [
                        // ENCABEZADO
                        // 'Status' => NotionPropertyHelper::select('Activo'),
                        // PERSONAL
                        'Nombres' => NotionPropertyHelper::title($this->titulo),
                        'Nombres y Apellidos' => NotionPropertyHelper::richText($nombre_completo),
                        'Ejecutiva' => NotionPropertyHelper::relation([$this->ejecutiva]),
                        'Correo' => NotionPropertyHelper::email($email),
                        'Correo_2' => NotionPropertyHelper::email($email_2),
                        'Correo_3' => NotionPropertyHelper::email($email_3),
                        'Correo_4' => NotionPropertyHelper::email($email_4),
                        'Correo_5' => NotionPropertyHelper::email($email_5),
                        'Celular Principal' => NotionPropertyHelper::phone_number($celular),
                        'Cel. Alternativo_1' => NotionPropertyHelper::phone_number($celular_2),
                        'Cel. Alternativo_2' => NotionPropertyHelper::phone_number($celular_3),
                        'Cel. Alternativo_3' => NotionPropertyHelper::phone_number($celular_4),
                        'Teléfono' => NotionPropertyHelper::phone_number($telefono),
                        'Ciudad' => NotionPropertyHelper::richText($this->ciudad),
                        'Provincia' => NotionPropertyHelper::richText($this->provincia),
                        'País' => NotionPropertyHelper::richText($this->pais),
                        'Distrito' => NotionPropertyHelper::richText($this->distrito),
                        // TRABAJO
                        'Razón Comercial' => NotionPropertyHelper::richText($this->empresa),
                        'Razón Social' => NotionPropertyHelper::richText($this->razon),
                        'RUC' => NotionPropertyHelper::number($ruc),
                        'Dirección' => NotionPropertyHelper::richText($this->direccion2),
                        'Obs. Dirección' => NotionPropertyHelper::richText($this->obs_direccion),
                        'Cargo' => NotionPropertyHelper::richText($this->cargo),
                        'Aniversario' => NotionPropertyHelper::date($aniversario),
                        'Rubro' => NotionPropertyHelper::richText($this->rubro),
                        'Página Web' => NotionPropertyHelper::richText($this->web),
                        'Detalle Origen' => NotionPropertyHelper::select($detalleOrigenTexto), 
                        'Tipo de Cliente' => NotionPropertyHelper::select($tipoClienteTexto),
                        'Estado de Atención' => NotionPropertyHelper::select($estadoAtencionTexto),
                        'Carta Presentación' => NotionPropertyHelper::checkbox($this->carta_presentacion),
                        'Catálogo' => NotionPropertyHelper::checkbox($this->catalogo),
                        'Pack Promocional' => NotionPropertyHelper::checkbox($this->pack_promocional),
                        //perfil 
                        'Tipo_cliente_CRM'                             => NotionPropertyHelper::richText($perfilArray['TIPO DE CLIENTE'] ?? ''),
                        'Política de Pago'                             => NotionPropertyHelper::richText($perfilArray['POLÍTICA DE PAGO'] ?? ''),
                        'Trabaja con Proveedores'                      => NotionPropertyHelper::richText($perfilArray['TRABAJA CON PROVEEDORES'] ?? ''),
                        'Procedim. Especial en facturación y despacho' => NotionPropertyHelper::richText($perfilArray['PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO'] ?? ''),
                        'Frecuencia de compra'                         => NotionPropertyHelper::richText($perfilArray['FRECUENCIA DE COMPRA'] ?? ''),
                        'Adicionales'                                  => NotionPropertyHelper::richText($perfilArray['ADICIONALES'] ?? ''),

                        // ASOCIADO
                    ],
                ]);
    }

    // Sincronizar cliente a Notion (crear o actualizar)
    public function syncToNotion($delete=false)
    {
        if ($this->sync_source === 'Notion') {
            return false; // Avoid loop if change came from Notion
        }
        try {
            $token = $this->token;
            $version = Database::getNotionVersion();

            $clientToNotion = new NotionClient($token, $version);

            $method = $this->notion_page_id ? 'PATCH' : 'POST';
            $uri = $this->notion_page_id ? "pages/{$this->notion_page_id}" : "pages";
            $payload= $this->toNotionPayload($delete);

            $result = $clientToNotion->saveToNotion($method, $uri, $payload);
            $this->notion_page_id = $result['id'];
            return ["status"=> 200, "id" => $this->notion_page_id];
        } catch (RequestException $e) {
            $error_message = $e->getMessage();
            $error_detail = 'No response body';

            if ($e->getResponse()) {
                $body = (string) $e->getResponse()->getBody();
                $json = json_decode($body, true);
                $error_detail = $json ? json_encode($json, JSON_PRETTY_PRINT) : $body;
            }

            // error_log("Error sincronizando con Notion:\nMensaje: $error_message\nDetalle: $error_detail");
            return $json;
        }catch (Exception $e) {
            // error_log('Error genérico: ' . $e->getMessage());
            return ["stats" => 400, "message" => $e->getMessage()];
        }
    }

    // Sincronizar desde Notion al CRM
    public static function syncFromNotion($db, $lastSyncTime)
    {
        $success = true;
        $message = '';
        $contador = 0;
        // $clientes_agregados = [];
        // $clientes_editados = [];

        try {
            date_default_timezone_set('America/Lima'); // Zona horaria
            $client = new Client();
            $token = Database::getNotionToken();
            $database_id = Database::getClientesNotionDatabaseId();
            $version = Database::getNotionVersion();
            $url = "https://api.notion.com/v1/databases/$database_id/query";

            $today = (new DateTime())->format('Y-m-d');
            $filterToday = [
                'filter' => [
                    'timestamp' => 'last_edited_time',
                    'last_edited_time' => [
                        'on_or_after' => $lastSyncTime
                    ]
                    // 'property' => 'Último Contacto',
                    // 'date' => [
                    //     'on_or_after' => $lastSyncTime
                    // ]
                ]
            ];
            $body = [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Content-Type' => 'application/json',
                    'Notion-Version' => $version,
                ],
                'json' =>$filterToday //new stdClass() // Cuerpo vacío para consultar todos los registros
            ];

            $response = $client->post($url, $body);
            $data = json_decode($response->getBody(), true);

            foreach ($data['results'] as $page) {
                $page_id = $page['id'];
                $last_edited = $page['last_edited_time'];
                
                // Verificar si last_edited es del día actual
                $last_edited_with_time_zone = (new DateTime($last_edited, new DateTimeZone('UTC')))
                ->setTimezone(new DateTimeZone('America/Lima'));
                $last_edited_date = $last_edited_with_time_zone->format('Y-m-d');
                if ($last_edited_date !== $today) {
                    continue; // Saltar si no fue editado hoy
                }
                $last_edited_time = $last_edited_with_time_zone->format('Y-m-d H:i:s');
                $properties = $page['properties'];
                $propertyGetter = new NotionPropertyGetter($properties);
                // PERSONAL
                $nombre_completo =  $propertyGetter->get('Nombres y Apellidos');
                $nombre_parts = explode(', ', trim($nombre_completo), 2);
                $nombres = $nombre_parts[0] ?? '';
                $apellidos = isset($nombre_parts[1]) ? $nombre_parts[1] : '';
                $ejecutiva = null;
                $ejecutivas = $propertyGetter->get('Ejecutiva');
                if (!empty($ejecutivas)) {
                    $ejecutiva = $ejecutivas[0];
                }
                $email = $propertyGetter->get('Correo');
                $email_2 = $propertyGetter->get('Correo_2');
                $email_3 = $propertyGetter->get('Correo_3');
                $email_4 = $propertyGetter->get('Correo_4');
                $email_5 = $propertyGetter->get('Correo_5');
                $celular_principal = $propertyGetter->get('Celular Principal');
                $celular_alt_1 = $propertyGetter->get('Cel. Alternativo_1');
                $celular_alt_2 = $propertyGetter->get('Cel. Alternativo_2');
                $celular_alt_3 = $propertyGetter->get('Cel. Alternativo_3');
                $telefono =$propertyGetter->get('Teléfono');
                $ciudad = $propertyGetter->get('Ciudad');
                $provincia = $propertyGetter->get('Provincia');
                $pais = $propertyGetter->get('País');
                $distrito = $propertyGetter->get('Distrito');
                $cliente_desde = $page['properties']['Cliente Desde']['date']['start'] ?? null;
                $fecha_atencion_mysql = $cliente_desde
                    ? (new DateTime($cliente_desde, new DateTimeZone('UTC')))
                        ->setTimezone(new DateTimeZone('America/Lima'))
                        ->format('Y-m-d H:i:s') :null;
                $ultimo_contacto = $page['properties']['Último Contacto']['last_edited_time'] ?? null;
                $fecha_aviso_mysql = $ultimo_contacto
                ? (new DateTime($ultimo_contacto, new DateTimeZone('UTC')))
                ->setTimezone(new DateTimeZone('America/Lima'))
                ->format('Y-m-d H:i:s')
                : null;
                // TRABAJO
                $empresa = $propertyGetter->get('Razón Comercial');
                $razon = $propertyGetter->get('Razón Social');
                $ruc = $propertyGetter->get('RUC');
                $direccion = $propertyGetter->get('Dirección');
                $obs_direccion = $propertyGetter->get('Obs. Dirección');
                $cargo = $propertyGetter->get('Cargo');
                $aniversario = $page['properties']['Aniversario']['date']['start'] ?? null;
                $rubro = $propertyGetter->get('Rubro');
                $web = $propertyGetter->get('Página Web');

                $origen = $page['properties']['Detalle Origen']['select']['name'] ?? '';
                $tipo_cliente = $page['properties']['Tipo de Cliente']['select']['name'] ?? '';
                $estado_atencion = $page['properties']['Estado de Atención']['select']['name'] ?? '';
                // Convierte texto de Notion a número para guardar en la base de datos
                $detalleOrigenID = isset(self::$detallesOrigenInvertido[$origen]) ? self::$detallesOrigenInvertido[$origen] : 7; // Por defecto: OTROS
                // Converte texto de Tipo Cliente a número
                $tipoClienteID = isset(self::$tiposClienteInvertido[$tipo_cliente]) ? self::$tiposClienteInvertido[$tipo_cliente] : 1;
                // Converte texto de Estado de Atencion a texto
                $estadoAtencionID = isset(self::$estadoAtencionInvertido[$estado_atencion]) ? self::$estadoAtencionInvertido[$estado_atencion] : '';
                $carta_presentacion = $propertyGetter->get('Carta Presentación');
                $catalogo = $propertyGetter->get('Catálogo');
                $pack_promocional = $propertyGetter->get('Pack Promocional');
                // PERFIL
                $perfilData = [
                    'TIPO DE CLIENTE' => $propertyGetter->get('Tipo_cliente_CRM'),
                    'POLÍTICA DE PAGO' => $propertyGetter->get('Política de Pago'),
                    'TRABAJA CON PROVEEDORES' => $propertyGetter->get('Trabaja con Proveedores'),
                    'PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO' => $propertyGetter->get('Procedim. Especial en facturación y despacho'),
                    'FRECUENCIA DE COMPRA' => $propertyGetter->get('Frecuencia de compra'),
                    'ADICIONALES' => $propertyGetter->get('Adicionales'),
                ];
                
                $status_proyecto = $page['properties']['Status Proyecto']['status']['name'] ?? '';
                $proyectos = $page['properties']['Proyectos']['relation'] ?? [];
                $cuenta = $page['properties']['Cuenta']['relation'][0]['id'] ?? '';
                $reuniones = $page['properties']['Reuniones']['relation'] ?? [];
                $progreso = $page['properties']['Progreso']['status']['name'] ?? '';

                $status = $page['properties']['Status']['select']['name'] ?? '';
                $statusValue = isset(self::$statusInvertido[$status]) ? self::$statusInvertido[$status] : 1; // POR DEFECTO ACTIVO

                // Buscar cliente por notion_page_id
                $query = "SELECT * FROM clientes WHERE notion_page_id = :notion_page_id";
                $stmt = $db->prepare($query);
                $stmt->execute(['notion_page_id' => $page_id]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                $clienteModel = nuevoCliente();
                $clienteModel->reset();
                $clienteModel->fatencion = $fecha_atencion_mysql;
                $clienteModel->fechaaviso = $fecha_aviso_mysql;
                $clienteModel->estadousuario = $statusValue;
                $clienteModel->notion_page_id = $page_id;
                $clienteModel->sync_source = 'Notion';
                // PERSONAL
                $clienteModel->nombres = $nombres;
                $clienteModel->apellidos = $apellidos;
                
                // Unimos los valores de las variables en una sola línea
                $emails = array_filter(
                    [$email, $email_2, $email_3, $email_4, $email_5],
                    fn($value) => trim($value) !== ''
                );

                // Implode solo si hay emails, si no, se establece en null (opcional)
                $clienteModel->email = !empty($emails) ? implode(',', $emails) : null;
                $clienteModel->celular = $celular_principal;
                $clienteModel->celular2 = $celular_alt_1;
                $clienteModel->celular3 = $celular_alt_2;
                $clienteModel->celular4 = $celular_alt_3;
                $clienteModel->telefono = $telefono;
                $clienteModel->ciudad = $ciudad;
                $clienteModel->provincia = $provincia;
                $clienteModel->pais = $pais;
                $clienteModel->distrito = $distrito;
                // TRABAJO
                $clienteModel->empresa = $empresa;
                $clienteModel->razon = $razon;
                $clienteModel->ruc = $ruc;
                $clienteModel->direccion2 = $direccion;
                $clienteModel->obsdireccion = $obs_direccion;
                $clienteModel->cargo = $cargo;
                $clienteModel->aniversario = $aniversario;
                $clienteModel->rubro = $rubro;
                $clienteModel->web = $web;
                $clienteModel->detalle_origen = $detalleOrigenID;
                $clienteModel->origen = $tipoClienteID;
                $clienteModel->prioridad = $estadoAtencionID;
                $clienteModel->carta_presentacion = $carta_presentacion;
                $clienteModel->catalogo = $catalogo;
                $clienteModel->correo = $pack_promocional;
                $clienteModel->perfil = json_encode($perfilData, JSON_UNESCAPED_UNICODE);
                
                if($ejecutiva){                    
                    $query = "SELECT idusuario FROM usuarios WHERE notion_page_id = :notion_page_id";
                    $stmt = $db->prepare($query);
                    $stmt->execute(['notion_page_id' => $ejecutiva]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $clienteModel->idusuario = $result['idusuario'] ?? 0;
                    $clienteModel->creadopor = $clienteModel->idusuario;
                }

                if ($cliente && strtotime($cliente['last_sync_time']) < strtotime($last_edited_time)) {
                    // Actualizar cliente existente
                    $clienteModel->idcliente = $cliente['idcliente'];
                    $result = $clienteModel->update();
                    // error_log('RESULTADO: ' . $result['message']);
                    if($result['success']){
                        if($cliente['estadousuario'] && !$statusValue){
                            $last_edited_date_time = $last_edited_with_time_zone->format('Y-m-d H:i:s');
                            $clienteModel->delete($last_edited_date_time);
                        }
                        // $clientes_editados[] = $cliente['idcliente'];
                    }
                    $contador=$contador+1;
                } elseif (!$cliente && $clienteModel->idusuario) {
                    // Crear nuevo cliente
                    if($ejecutiva){
                        $result = $clienteModel->create();
                        // error_log("RESUTLADO CREAR:" . $result['message']);
                        if ($result['success']) {
                            // $clientes_agregados[] = $result['idcliente']; // asegúrate que lo devuelva
                            $contador=$contador+1;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Error sincronizando desde Notion: ' . $e->getMessage());
            $message = 'Error sincronizando desde Notion: ' . $e->getMessage();
            $success = false;
        }finally{
            if ($success && $contador > 0) {
                $message = "Clientes sincronizados: $contador";
            } elseif (!$success) {
                $message = $message ?: 'Error en la sincronización';
            } else {
                $message = ''; // Sin cambios, sin mostrar mensaje
            }
            return [
                'success' =>  $success,
                'message' => $message,
                'cantidad' => $contador,
                // 'timestamp' => date('c'),
                // 'clientes_nuevos' => $clientes_agregados,
                // 'clientes_editados' => $clientes_editados
            ];
        }
    }
}