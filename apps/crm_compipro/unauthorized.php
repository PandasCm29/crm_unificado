
<?php
require_once __DIR__ . '/config/auth.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso No Autorizado - Sistema CRM</title>
    <link rel="stylesheet" href="<?php echo $base?>assets/css/output.css">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96 text-center">
        <h1 class="text-2xl font-bold mb-4 text-red-600">Acceso No Autorizado</h1>
        <p class="mb-6">No tienes permisos para acceder a esta página.</p>
        <a href="<?php echo $base?>index.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Volver al Inicio
        </a>
    </div>
</body>

</html>