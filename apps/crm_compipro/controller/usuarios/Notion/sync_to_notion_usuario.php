<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../../models/notion/UsuarioNotion.php';

$areas_notion = ["Administración", "Comercial", "Logística"];
try {
    // Leer datos JSON desde php://input para ambos métodos (POST y PUT)
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $_SERVER['REQUEST_METHOD'];

    // Validar que los datos se recibieron correctamente
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos JSON inválidos']);
        exit;
    } else if ($method !== "POST" && $method !== "PUT") {
        throw new Exception('Método no soportado');
    }

    // Asignar valores con valores por defecto
    $nombres = trim($data['nombres'] ?? '');
    $apellidos = trim($data['apellidos'] ?? '');
    $usuario = trim($data['usuario'] ?? '');
    $area = trim($data['area'] ?? '');
    $tipoempleado = trim($data['tipoempleado'] ?? '');
    $password = trim($data['password'] ?? '');
    $hora_entrada = trim($data['hora_entrada'] ?? '');
    $hora_salida = trim($data['hora_salida'] ?? '');
    $dni = trim($data['dni'] ?? '');
    $celular = trim($data['celular'] ?? '');
    $correo = trim($data['correo'] ?? '');

    $db = new Database();
    $usuarioModel = nuevoUsuario();
    $usuarioModel->reset();

    $usuarioModel->nombres  = $nombres;
    $usuarioModel->apellidos = $apellidos;
    $usuarioModel->usuario = $usuario;
    $usuarioModel->area = $area;
    $usuarioModel->tipoempleado = $tipoempleado;
    $usuarioModel->password = $password;
    $usuarioModel->hora_entrada = $hora_entrada;
    $usuarioModel->hora_salida = $hora_salida;
    $usuarioModel->dni = $dni;
    $usuarioModel->celular = $celular;
    $usuarioModel->correo = $correo;

    if ($method === "PUT") {
        $idusuario = trim($data['idusuario'] ?? '');
        $estado = trim($data['estado'] ?? '0');
        $usuarioModel->estado = $estado;

        if ($idusuario) {
            $query = "SELECT notion_page_id FROM usuarios WHERE idusuario = :idusuario";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->execute(['idusuario' => $idusuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $usuarioModel->notion_page_id = $result['notion_page_id'] ?? null;
            $usuarioModel->idusuario  = $idusuario;
        }
    }

    $saved = $method === 'POST' ? $usuarioModel->create() : $usuarioModel->update();

    // Copiar datos a Notion
    $usuarioNotion = new UsuarioNotion();
    $usuarioNotion->nombre_completo      = trim($usuarioModel->apellidos . ', ' . ($usuarioModel->nombres ?? ''));
    $usuarioNotion->area         = $usuarioModel->area;
    $usuarioNotion->correo       = $usuarioModel->correo;
    $usuarioNotion->celular      = $usuarioModel->celular;
    $usuarioNotion->sync_source =  $usuarioModel->sync_source;
    $usuarioNotion->notion_page_id = $usuarioModel->notion_page_id;
    $usuarioNotion->status = $usuarioModel->estado;

    if ($saved['success']) {
        // Sincronizar con Notion
        if (in_array($usuarioNotion->area, $areas_notion, true)) {
            $notion_page_id = $usuarioNotion->syncToNotion();
            if ($notion_page_id) {
                if ($method === 'POST') {
                    // Actualizar el Notion Page ID en la base local
                    $usuarioModel->idusuario = $saved['idusuario'];
                    $query = "UPDATE usuarios SET notion_page_id = :notion_page_id WHERE idusuario = :idusuario";
                    $stmt = $db->getConnection()->prepare($query);
                    if ($stmt->execute([
                        'notion_page_id' => $notion_page_id,
                        'idusuario' => $usuarioModel->idusuario
                    ])) {
                        echo json_encode(['success' => true, 'message' => 'Usuario creado y sincronizado en Notion.']);
                    }
                } else {
                    echo json_encode(['success' => true, 'message' => 'Usuario actualizado y sincronizado en Notion.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario guardado localmente, pero error al sincronizar con Notion.']);
            }
        }else{
            echo json_encode(['success' => true, 'message' => 'Usuario creado.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $saved['message']]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
