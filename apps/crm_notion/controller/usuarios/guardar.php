<?php
// controller/usuarios/crear.php
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// Leer datos JSON desde php://input para ambos métodos (POST y PUT)
$data = json_decode(file_get_contents('php://input'), true);

// Validar que los datos se recibieron correctamente
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos JSON inválidos']);
    exit;
}

// Asignar valores con valores por defecto
$idusuario = trim($data['idusuario'] ?? ''); // Solo necesario para PUT
$nombres = trim($data['nombres'] ?? '');
$apellidos = trim($data['apellidos'] ?? '');
$usuario = trim($data['usuario'] ?? '');
$area = trim($data['area'] ?? '');
$puesto = trim($data['puesto'] ?? '');
$tipoempleado = trim($data['tipoempleado'] ?? '');
$password = trim($data['password'] ?? '');
$hora_entrada = trim($data['hora_entrada'] ?? '');
$hora_salida = trim($data['hora_salida'] ?? '');
$dni = trim($data['dni'] ?? '');
$celular = trim($data['celular'] ?? '');
$correo = trim($data['correo'] ?? '');


try {
    $userModel->reset();
    $userModel->nombres  = $nombres;
    $userModel->apellidos = $apellidos;
    $userModel->usuario = $usuario;
    $userModel->area = $area;
    $userModel->puesto= $puesto;
    $userModel->tipoempleado = $tipoempleado;
    $userModel->password = $password;
    $userModel->hora_entrada = $hora_entrada;
    $userModel->hora_salida = $hora_salida;
    $userModel->dni = $dni;
    $userModel->celular = $celular;
    $userModel->correo = $correo;
    switch ($method) {
        case 'POST':
            // CREAR
            $resultado = $userModel->create();
            break;

        case 'PUT':
            $estado = trim($data['estado'] ?? '0');
            $userModel->idusuario  = $idusuario;
            $userModel->estado = $estado;
            // EDITAR
            $resultado = $userModel->update();
            break;

        case 'DELETE':
            // ELIMINAR
            $resultado = $userModel->delete();
            break;

        default:
            throw new Exception('Método no soportado');
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
