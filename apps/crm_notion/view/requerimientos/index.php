<?php
require_once __DIR__ . '/../../config/auth.php';
requireRegularArea();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REQUERIMIENTOS POR ATENDER</title>

    <link href="<?php echo $base ?>assets/css/static/font-awesome.6.5.0.all.min.css" defer rel="stylesheet">
    <link href="<?php echo $base ?>assets/css/static/flatpickr.min.css" defer rel="stylesheet">

    <script src="<?php echo $base ?>assets/js/static/sweetalert2.all.min.js" defer></script>
    <link rel="stylesheet" href="<?php echo $base ?>styles.css">
    <link rel="stylesheet" href="<?php echo $base ?>assets/css/output.css">
</head>

<body class="bg-lightAlt min-h-screen flex flex-col">


    <header class="bg-primary text-white py-1 md:py-3 px-6 shadow-md cabecera">
        <h1 class="text-2xl font-bold">
            <i class="fas fa-users"></i>
            REQUERIMIENTOS POR ATENDER
        </h1>


        <a href="<?php echo $base ?>logout.php" class="flex items-center px-3 py-2 rounded-lg filter brightness-100
              transition duration-300 ease-in-out hover:brightness-90 hover:-translate-y-[2px]"
            style="background-color:rgb(211, 134, 18); position: absolute; right: 20px; top: 5px;">
            <i class="fas fa-sign-out-alt mr-2 text-lg"></i>
            Cerrar Sesión
        </a>
    </header>

    <div class="flex flex-1">
        <div class="w-[50px] bg-primary flex flex-col items-center pt-4 barra">
            <div class="group relative grupo-icono">
                <div class="p-3 cursor-pointer">
                    <i class="fas fa-users text-white text-xl icono-cliente "></i>
                </div>
                <div class="absolute left-full top-0 ml-0 w-64 bg-gray-800 rounded-tr-lg rounded-br-lg shadow-lg p-4 hidden group-hover:block z-50">
                    <p class="font-semibold mb-2 text-white">Clientes</p>
                    <ul class="space-y-2 text-sm">
                        <li onclick="window.location.href = '<?php echo $base ?>index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Listar Clientes</li>
                        <li class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Requerimientos por atender</li>
                        <li onclick="window.location.href = '<?php echo $base ?>view/cumpleanos-aniversario/index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Cumpleaños/Aniversario Clientes</li>
                    </ul>
                </div>
            </div>
            <?php
            if (isTIArea()) {
                echo '<div class="group relative grupo-icono">
                            <div class="p-3 cursor-pointer">
                                <i class="fas fa-user text-white text-xl icono-cliente "></i>
                            </div>
                            <div class="absolute left-full top-0 ml-0 w-64 bg-gray-800 rounded-tr-lg rounded-br-lg shadow-lg p-4 hidden group-hover:block z-50">
                                <p class="font-semibold mb-2 text-white">Usuarios</p>
                                <ul class="space-y-2 text-sm">
                                    <li onclick="window.location.href = \'' . $base . 'view/usuarios/crear.php\'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Crear Nuevo Usuario</li>
                                    <li onclick="window.location.href = \'' . $base . 'view/usuarios/listar.php\'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Listar Usuarios</li>
                                </ul>
                            </div>
                    </div>';
            } ?>
        </div>
        <main class="container mx-auto px-4 py-6">
            <div class="w-fit max-w-full sticky left-[30px] contenedor-filtros px-4">
                <div class="flex flex-wrap items-center gap-4 bg-white p-3 rounded-lg shadow-md mb-2 filtros">
                    <button id="btn-abrir-modal" class="bg-green-400 hover:bg-green-500 text-white font-bold py-2 px-4 rounded text-xs uppercase transition-colors duration-200"
                        title="Agregar Requerimiento">Agregar<br>Requerimiento
                    </button>
                    <div class="flex flex-col">
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Buscar Por:</label>
                    </div>
                    <div class="flex flex-col">
                        <select id="filtro-por" class="border text-xs border-gray-300 rounded-l px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                            <option class="text-xs" value="">Seleccione</option>
                            <option class="text-xs" value="id_cliente">ID / Número cliente</option>
                            <option class="text-xs" value="usuario">Usuario</option>
                            <option class="text-xs" value="fecha_requerimiento">Fecha Requerimiento</option>
                            <option class="text-xs" value="empresa">Empresa</option>
                            <option class="text-xs" value="requerimiento">Requerimientos</option>
                            <option class="text-xs" value="derivado">Origen</option>
                            <option class="text-xs" value="status">Status</option>
                            <option class="text-xs" value="nombres">Nombres</option>
                            <option class="text-xs" value="apellidos">Apellidos</option>
                            <option class="text-xs" value="fecha_respuesta_cliente">Fecha Respuesta Cliente</option>
                            <option class="text-xs" value="fecha_atencion">Fecha Atención</option>
                            <option class="text-xs" value="email">Email</option>
                            <option class="text-xs" value="direccion">Dirección</option>
                            <option class="text-xs" value="telefono">Teléfono</option>
                            <option class="text-xs" value="celular">Celular</option>
                        </select>
                    </div>
                    <input id="texto-buscar" type="text" placeholder="Buscar"
                        class="border text-xs border-gray-300 rounded-r px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                    <button id="button-search-filters"
                        class="bg-primary hover:bg-primary/80 text-white text-xs font-medium py-1 px-3 rounded">
                        <i class="fas fa-search mr-1 text-xs"></i>
                        Buscar
                    </button>
                    <div class="flex flex-col">
                        <button id="btnActualizarResultado"
                            class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-1 px-3 rounded"
                            onclick="limpiarcamposac()">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="inline-block mr-1 w-3 h-3"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-controls text-xs">
                <div class="max-w-[100vw] sticky left-[73px] ml-5">

                    <label class="text-xs" for="rowsPerPage">Mostrar por página:</label>
                    <select id="rowsPerPage" onchange="updateRowsPerPage()">
                        <option class="text-xs" value="10">10</option>
                        <option class="text-xs" value="50">50</option>
                        <option class="text-xs" value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="table-container">
                <table class="custom-table ml-5">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-xs rounded-tl-lg">Acciones</th>
                            <th class="text-xs">N°</th>
                            <th class="text-xs">Usuario</th>
                            <th class="text-xs">Fecha Requerimiento</th>
                            <th class="text-xs">Empresa</th>
                            <th class="text-xs">Requerimiento</th>
                            <th class="text-xs">Origen</th>
                            <th class="text-xs">Status</th>
                            <th class="text-xs">Nombre</th>
                            <th class="text-xs">Apellido</th>
                            <th class="text-xs">Fecha Respuesta Cliente</th>
                            <th class="text-xs">Fecha Atención</th>
                            <th class="text-xs">Email</th>
                            <th class="text-xs">Dirección</th>
                            <th class="text-xs">Telefono</th>
                            <th class="text-xs">Celular</th>
                            <th class="text-xs">Adjunto</th>
                            <th class="text-xs rounded-tr-lg">URL Pagina</th>

                        </tr>
                    </thead>
                    <tbody id="tableBody">

                    </tbody>
                </table>
                <div class="w-screen sticky left-0">
                    <!-- Contenedor principal (debe ir fuera del scroll horizontal de la tabla) -->
                    <div class="max-w-[100vw] ">
                        <div id="pagination-container" class="pagination-controls text-left">
                            <!-- contenido de la paginación -->
                        </div>
                    </div>




                </div>


        </main>


        <?php include 'modal.php'; ?>
        <?php include '../cliente/modal.php'; ?>
        <?php include 'modal-editar.php'; ?>
</body>

</html>
<script type="module" src="<?php echo $base ?>assets/js/requerimientos/dashboard.js"></script>
<script src="<?php echo $base ?>assets/js/modal-cliente.js"></script>
<script src="<?php echo $base ?>assets/js/modal-distrito.js"></script>
<script type="module" src="<?php echo $base ?>assets/js/requerimientos/modal-add-req.js"></script>
<script type="module" src="<?php echo $base ?>assets/js/requerimientos/modal-edit-req.js"></script>



<style>
    thead tr th {
        background-color: black !important;
        color: white !important;
        font-size: 16px;
        font-weight: 700;
    }


    /* Contenedor principal de la tabla */
    .bg-white {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 100%;
        box-sizing: border-box;
    }

    /* Contenedor de la tabla con scroll */
    .table-container {
        overflow-x: auto;
        width: 100%;
    }

    /* Ajustamos la tabla */
    .custom-table {
        width: max-content;
        /* La tabla toma el ancho que necesite */
        min-width: 98.5%;
        /* Nunca menos que el contenedor */
        border-collapse: collapse;
        font-size: 14px;
        border-bottom-right-radius: 12px !important;
        border-bottom-left-radius: 12px !important;
    }

    .custom-table th,
    .custom-table td {
        padding: 8px 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Ajustamos los encabezados */
    .custom-table th {
        background-color: #f3f4f6;
        font-weight: 600;
        white-space: nowrap;
    }

    .custom-table tbody tr:hover {
        background-color: rgb(207, 220, 233);
    }

    /* Controles de la tabla */
    .table-controls {
        display: flex;
        justify-content: flex-start;
        margin-bottom: 16px;
        margin-left: 2.2rem;
    }

    .table-controls label {
        margin-right: 8px;
        font-size: 14px;
        line-height: 32px;
    }

    .table-controls select {
        padding: 6px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
    }

    main.container {
        max-width: none;
        min-width: max-content;
        min-height: max-content;
        overflow: visible;
    }

    body {
        margin: 0;
        padding: 0;
        overflow-x: auto;
        overflow-y: auto;
        height: 100vh;

    }

    .grupo-icono:hover {
        background-color: #1f2937;
        width: 100%;
    }

    .table-container {
        overflow-x: visible;
        width: 100%;
        max-height: calc(100vh - 200px);
        overflow-y: visible;
        margin-left: 2.2rem;
    }

    /* Encabezado fijo */
    .table-container thead th {
        position: sticky;
        top: 0;
        background-color: #f9fafb;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Mejorar el estilo del encabezado */
    .table-container th {
        padding: 12px 8px;
        font-weight: 600;
        text-align: left;
        border-bottom: 2px solid #e5e7eb;

    }

    .cabecera {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        z-index: 1;
        margin: 0;
        padding: 0.75rem 1.5rem;
        /* Ajusta según tu diseño */
        background: #ec9306;
        /* Color del header */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        /* Sombra opcional */
    }

    .barra {
        position: fixed;
        left: 0;
        min-height: 100vh;
        z-index: 50;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 40px;
    }

    .filtros {
        margin-top: 3rem;
        margin-left: 2.2rem;

    }

    .custom-table thead tr th {
        position: sticky;
        top: 55px;
        color: white !important;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }

    /* Estilos para la paginación */
    .pagination-controls {
        display: flex;
        justify-content: center;
        padding: 1rem 0;
        margin-top: 1rem;
        margin-bottom: auto;
        background: #F3F4F6;
        border-top: 1px solid #e5e7eb;
        width: 100%;

    }

    .table-container {
        display: flex;
        flex-direction: column;
        min-height: 0;
        /* Permite que el contenido se expanda correctamente */
    }
</style>