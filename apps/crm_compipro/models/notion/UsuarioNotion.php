<?php
// require_once __DIR__ . '/../../config/vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/usuarios/conexion.php';

// use GuzzleHttp\Client;

class UsuarioNotion
{
    private $client;
    private $token;
    private $database_id;

    public $notion_page_id;
    public $nombre_completo;
    public $area;
    public $correo;
    public $celular;
    public $status;
    public $sync_source;

    public function __construct()
    {
        // $this->client = new Client();
        $this->token = Database::getNotionToken();
        $this->database_id = Database::getUsuariosNotionDatabaseId();
    }

    private static $statusInvertido = [
        "Activo" => 1,
        "Inactivo" => 0
    ];
    // Sincronizar usuario a Notion (crear o actualizar)
    public function syncToNotion()
    {
        if ($this->sync_source === 'Notion') {
            return false; // Evitar bucle si el cambio viene de Notion
        }

        $version = Database::getNotionVersion();
        $url = $this->notion_page_id
            ? "https://api.notion.com/v1/pages/{$this->notion_page_id}"
            : 'https://api.notion.com/v1/pages';
        $method = $this->notion_page_id ? 'PATCH' : 'POST';

        // Validar y formatear datos
        $correo = $this->correo && filter_var($this->correo, FILTER_VALIDATE_EMAIL) ? $this->correo : null;
        $celular = $this->celular && preg_match('/^\+?\d{7,15}$/', trim($this->celular)) ? trim($this->celular) : null;

        // Log de datos para depuración
        error_log('Datos de solicitud a Notion: ' . json_encode([
            'nombre_completo' => $this->nombre_completo,
            'area' => $this->area,
            'correo' => $correo,
            'celular' => $celular
        ]));

        $body = [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
                'Notion-Version' => $version,
            ],
            'json' => $this->notion_page_id
                // UPDATE - PATCH
                ? [
                    'properties' => [
                        'Nombre' => ['title' => $this->nombre_completo ? [['text' => ['content' => $this->nombre_completo]]] : [[]]],
                        'Puesto' => ['rich_text' => $this->area ? [['text' => ['content' => $this->area]]] : []],
                        'Correo electrónico' => ['email' => $correo],
                        'Teléfono' => ['phone_number' => $celular],                        
                        'Status' => ['select' => ['name' => $this->status == "1" ? "Activo" : "Inactivo"]],
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
                        'Nombre' => ['title' => $this->nombre_completo ? [['text' => ['content' => $this->nombre_completo]]] : [[]]],
                        'Puesto' => ['rich_text' => $this->area ? [['text' => ['content' => $this->area]]] : []],
                        'Correo electrónico' => ['email' => $correo],
                        'Teléfono' => ['phone_number' => $celular],
                        'Status' => ['select' => ['name' => 'Activo']],
                    ],
                ],
        ];

        try {
            $response = $this->client->request($method, $url, $body);
            $result = json_decode($response->getBody(), true);
            $this->notion_page_id = $result['id'];
            return $this->notion_page_id;
        } catch (Exception $e) {
            error_log('Error sincronizando con Notion: ' . $e->getMessage());
            return false;
        }
    }

    public static function syncFromNotion($db)
    {
        // try {
        //     date_default_timezone_set('America/Lima'); 
        //     $client = new Client();
        //     $token = Database::getNotionToken();
        //     $database_id = Database::getUsuariosNotionDatabaseId();
        //     $version = Database::getNotionVersion();

        //     $url = "https://api.notion.com/v1/databases/$database_id/query";
        //     $body = [
        //         'headers' => [
        //             'Authorization' => "Bearer $token",
        //             'Content-Type' => 'application/json',
        //             'Notion-Version' => $version,
        //         ],
        //         'json' => new stdClass() // Cuerpo vacío para consultar todos los registros
        //     ];

        //     $response = $client->post($url, $body);
        //     $data = json_decode($response->getBody(), true);

        //     $today = (new DateTime())->format('Y-m-d');
        //     $contador = 0;

        //     foreach ($data['results'] as $page) {
        //         $page_id = $page['id'];
        //         $last_edited = $page['last_edited_time'];

        //         // Verificar si last_edited es del día actual
        //         $last_edited_date = (new DateTime($last_edited, new DateTimeZone('UTC')))
        //                 ->setTimezone(new DateTimeZone('America/Lima'))
        //                 ->format('Y-m-d');
        //         if ($last_edited_date !== $today) {
        //             continue; // Saltar si no fue editado hoy
        //         }

        //         // Extraer datos de Notion
        //         $nombre_completo = $page['properties']['Nombre']['title'][0]['text']['content'] ?? '';
        //         $area = $page['properties']['Puesto']['rich_text'][0]['text']['content'] ?? '';
        //         $correo = $page['properties']['Correo electrónico']['email'] ?? null;
        //         $celular = $page['properties']['Teléfono']['phone_number'] ?? null;
        //         $status = $page['properties']['Status']['select']['name'] ?? '';
        //         $statusValue = isset(self::$statusInvertido[$status]) ? self::$statusInvertido[$status] : 1; // POR DEFECTO ACTIVO

        //         // Separar nombres y apellidos si es necesario (basado en el nombre completo)
        //         $nombre_parts = explode(', ', trim($nombre_completo), 2);
        //         $nombres = $nombre_parts[1] ?? '';
        //         $apellidos = isset($nombre_parts[0]) ? $nombre_parts[0] : '';
        //         // Buscar usuario por notion_page_id
        //         $query = "SELECT * FROM usuarios WHERE notion_page_id = :notion_page_id";
        //         $stmt = $db->prepare($query);
        //         $stmt->execute(['notion_page_id' => $page_id]);
        //         $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        //         // Instanciar modelo de usuario 
        //         $userModel = nuevoUsuario();
        //         $userModel->reset();
        //         $userModel->nombres = $nombres;
        //         $userModel->apellidos = $apellidos;
        //         $userModel->area = $area;
        //         $userModel->correo = $correo;
        //         $userModel->celular = $celular;
        //         $userModel->notion_page_id = $page_id;
        //         $userModel->sync_source = 'Notion';
        //         $userModel->estado = $statusValue;

        //         if ($usuario && strtotime($usuario['last_sync_time']) < strtotime($last_edited)) {
        //             $userModel->idusuario = $usuario['idusuario'];
        //             $userModel->usuario = $usuario['usuario'];
        //             $userModel->tipoempleado = $usuario['tipoempleado'];
        //             $userModel->hora_entrada = $usuario['hora_entrada'];
        //             $userModel->hora_salida = $usuario['hora_salida'];
        //             $userModel->dni = $usuario['dni'];
        //             $result = $userModel->update();
        //             // error_log('RESULTADO: ' . $result['message']);
        //             $contador++;
        //         } elseif (!$usuario) {
        //             $userModel->password = "Compina2025";
        //             $result = $userModel->create();
        //             // error_log('RESULTADO CREAR: ' . $result['message']);
        //             $contador++;
        //         }
        //     }

        //     return true;
        // } catch (Exception $e) {
        //     error_log('Error sincronizando usuarios desde Notion: ' . $e->getMessage());
        //     return false;
        // }finally{
        //     error_log('Usuarios sincronizados: ' . $contador);
        // }
    }
}