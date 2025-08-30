<?php
header('Content-Type: application/json');
ob_start();

try {
    require_once __DIR__ . '/conexion.php';
    $requerimiento->reset();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido. Usa POST.'
        ]);
        exit;
    }

    // Asignar los campos comunes
    foreach (['nombres','apellidos','empresa','email','celular','telefono','asunto','derivado','estatus'] as $f) {
        $requerimiento->{$f} = $_POST[$f] ?? '';
    }

    // Manejo de archivo
    $archivoGuardado = null;
    if (!empty($_FILES['archivo']['name'])) {
        if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al recibir el archivo.');
        }

        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','pdf'];
        if (!in_array($ext, $allowed)) {
            throw new Exception('Extensión no permitida. Solo imágenes o PDF.');
        }

        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $archivoGuardado = md5(time() . $_FILES['archivo']['name']) . ".$ext";
        $destino = $uploadDir . $archivoGuardado;

        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
            throw new Exception('No se pudo mover el archivo.');
        }
        $requerimiento->archivo = 'uploads/' . $archivoGuardado;
    } else {
        $requerimiento->archivo = null; 
    }

    $requerimiento->create();

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Requerimiento registrado correctamente.'
    ]);

} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
