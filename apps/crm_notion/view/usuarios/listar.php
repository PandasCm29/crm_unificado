<?php
// listar.php
require_once __DIR__ . '/../../config/auth.php';
requireTIArea();
date_default_timezone_set('America/Lima');

$valoresPermitidos = [10, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500];
echo '<script>let idusuario; </script>';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MÓDULO LISTAR CLIENTES</title>


    <link href="<?php echo $base?>assets/css/static/font-awesome.6.5.0.all.min.css" defer rel="stylesheet">
    <link href="<?php echo $base?>assets/css/static/flatpickr.min.css" defer rel="stylesheet">

    <script src="<?php echo $base?>assets/js/static/sweetalert2.all.min.js" defer></script>
    <link rel="stylesheet" href="<?php echo $base?>assets/css/styles.css">

    <link rel="stylesheet" href="<?php echo $base ?>assets/css/usuarios.css">
    <link rel="stylesheet" href="<?php echo $base?>assets/css/output.css">

 
</head>

<body class="bg-lightAlt min-h-screen flex flex-col overflow-x-auto items-start">
    <div id="preloader" class="fixed inset-0 z-50 bg-white/80  items-center justify-center hidden">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <header class="fixed top-0 left-0 right-0 bg-primary text-white md:py-3 px-6 shadow-md z-50">
        <h1 class="text-2xl font-bold"><i class="fas fa-user mr-3"></i>MÓDULO LISTAR USUARIO</h1>


        <a href="<?php echo $base?>logout.php" class="flex items-center px-3 py-2 rounded-lg filter brightness-100
              transition duration-300 ease-in-out hover:brightness-90 hover:-translate-y-[2px]"
            style="background-color:rgb(211, 134, 18); position: absolute; right: 20px; top: 5px;">
            <i class="fas fa-sign-out-alt mr-2 text-lg"></i>
            Cerrar Sesión
        </a>

    </header>
    <div class="h-[50px]"></div>

    <main class="ml-[50px] min-h-auto py-4 grid grid-cols-1 gap-5 min-w-[95vw] items-start">
        <div class="flex flex-1">
            <div class="w-[50px] bg-primary flex flex-col items-center pt-4 fixed left-0 min-h-screen z-40 flex flex-col items-center -mt-2">
                <div class="group relative grupo-icono">
                    <div class="p-3 cursor-pointer">
                        <i class="fas fa-users text-white text-xl icono-cliente "></i>
                    </div>
                    <div class="absolute left-full top-0 ml-0 w-64 bg-gray-800 rounded-tr-lg rounded-br-lg shadow-lg p-4 hidden group-hover:block z-50">
                        <p class="font-semibold mb-2 text-white">Clientes</p>
                        <ul class="space-y-2 text-sm">
                            <li onclick="window.location.href = '<?php echo $base?>index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Listar Clientes</li>
                            <li onclick="window.location.href = '<?php echo $base?>view/requerimientos/index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Requerimientos por atender</li>
                            <li onclick="window.location.href = '<?php echo $base?>view/cumpleanos-aniversario/index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Cumpleaños/Aniversario Clientes</li>
                        </ul>
                    </div>
                </div>
                <div class="group relative grupo-icono">
                    <div class="p-3 cursor-pointer">
                        <i class="fas fa-user text-white text-xl icono-cliente "></i>
                    </div>
                    <div class="absolute left-full top-0 ml-0 w-64 bg-gray-800 rounded-tr-lg rounded-br-lg shadow-lg p-4 hidden group-hover:block z-50">
                        <p class="font-semibold mb-2 text-white">Usuarios</p>
                        <ul class="space-y-2 text-sm">
                            <li onclick="window.location.href = '<?php echo $base?>view/usuarios/crear.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Crear Nuevo Usuario</li>
                            <li class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Listar Usuarios</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full flex justify-center px-4 gap-2">
            <label class="text-s font-medium text-gray-700">Buscar:</label>
            <input id="texto-buscar" type="text" placeholder="Buscar"
                class="border text-s border-gray-300 rounded-r px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
            <button type="button" id="button-search-users" disabled
                class="bg-primary hover:bg-primary/80  text-s font-medium p-2 cursor-pointer rounded text-white text-xs  flex items-center justify-center  whitespace-nowrap transform transition-all duration-300 ease-in-out shadow-md hover:-translate-y-1 hover:scale-80 hover:shadow-lg">
                <i class="fas fa-search mr-1 text-xs"></i>
                Buscar
            </button>
            <button type="button" id="button-reload-search"
                class="bg-primary hover:bg-primary/80  text-s font-medium p-2 cursor-pointer rounded text-white text-xs  flex items-center justify-center  whitespace-nowrap transform transition-all duration-300 ease-in-out shadow-md hover:-translate-y-1 hover:scale-80 hover:shadow-lg">
                Recargar
                <i class="fas fa-sync ml-1 text-xs"></i>
            </button>
        </div>
        <?php include './editar.php'; ?>
        <?php include 'tablaUsuario.php'; ?>
    </main>
</body>

</html>