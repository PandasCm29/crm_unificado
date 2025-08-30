<?php
// index.php
require_once __DIR__.'/config/session.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();

date_default_timezone_set('America/Lima');
$valoresPermitidos = [10, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500];


?>
<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">
<link rel="icon" href="<?php echo $base?>icons/favicon.png" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MÓDULO LISTAR CLIENTES</title>


<link href="<?php echo $base?>assets/css/static/font-awesome.6.5.0.all.min.css" defer rel="stylesheet"> <!--IMPORTANTE-->
<link href="<?php echo $base?>assets/css/static/flatpickr.min.css" defer rel="stylesheet">
<link rel="stylesheet" href="<?php echo $base?>styles.css">
<link rel="stylesheet" href="<?php echo $base?>assets/css/output.css">
<link rel="stylesheet" href="<?php echo $base?>assets/css/styles.css">
<script src="<?php echo $base?>assets/js/static/sweetalert2.all.min.js" defer></script>
<!-- <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#ec9306',
                    dark: '#000000',
                    light: '#ffeac8',
                    lightAlt: '#f8f2e8',
                },
                fontFamily: {
                    sans: ['Poppins', 'sans-serif'],
                }
            }
        }
    }
</script> -->

</head>

<body class="bg-lightAlt min-h-screen flex flex-col overflow-x-auto items-start">
    <div id="preloader" class="fixed inset-0 z-50 bg-white/80 flex items-center justify-center hidden">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <header class="fixed top-0 left-0 right-0 bg-primary text-white md:py-3 px-6 shadow-md z-50">
        <h1 class="text-2xl font-bold"><i class="fas fa-users mr-3"></i>MÓDULO LISTAR CLIENTES</h1>

        <a href="<?php echo $base?>logout.php" class="flex items-center px-3 py-2 rounded-lg filter brightness-100
              transition duration-300 ease-in-out hover:brightness-90 hover:-translate-y-[2px]"
            style="background-color:rgb(211, 134, 18); position: absolute; right: 20px; top: 5px;">
            <i class="fas fa-sign-out-alt mr-2 text-lg"></i>
            Cerrar Sesión
        </a>

    </header>
    <div class="h-[50px]"></div>
    <div class="flex flex-1">
        <div class="w-[50px] bg-primary flex flex-col items-center pt-4 fixed left-0 min-h-screen z-40 flex flex-col items-center -mt-2">
            <div class="group relative grupo-icono">
                <div class="p-3 cursor-pointer">
                    <i class="fas fa-users text-white text-xl icono-cliente "></i>
                </div>
                <div class="absolute left-full top-0 ml-0 w-64 bg-gray-800 rounded-tr-lg rounded-br-lg shadow-lg p-4 hidden group-hover:block z-50">
                    <p class="font-semibold mb-2 text-white">Clientes</p>
                    <ul class="space-y-2 text-sm">
                        <li class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Listar Cliente</li>
                        <li onclick="window.location.href = '<?php echo $base?>view/requerimientos/index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Requerimientos por atender</li>
                        <li onclick="window.location.href = '<?php echo $base?>view/cumpleanos-aniversario/index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Cumpleaños/Aniversario Clientes</li>
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
                                <li onclick="window.location.href = \''.$base.'view/usuarios/crear.php\'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Crear Nuevo Usuario</li>
                                <li onclick="window.location.href = \''.$base.'view/usuarios/listar.php\'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Listar Usuarios</li>
                            </ul>
                        </div>
                </div>';
            } ?>
        </div>
        <main class=" min-h-screen py-4 grid grid-cols-1 min-w-[4000px] items-start border">
            <div class="max-w-[95vw] sticky left-[51px] z-30 px-1 mb-4 ">
                <div class="bg-white p-3 rounded-lg shadow-md mb-2">
                    <div class="w-full">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div class="flex flex-col space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Buscar Por:</label>
                                    <div class="flex">
                                        <select id="filtro-por"
                                            class="w-1/2 border text-xs border-gray-300 rounded-l px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                                            <option class="text-xs" value="todos">Todos</option>
                                            <option class="text-xs" value="num-cliente">ID / Numero cliente</option>
                                            <option class="text-xs" value="empresa">Razón Comercial "Empresa"</option>
                                            <option class="text-xs" value="razon_social">Razón Social</option>
                                            <option class="text-xs" value="nombres">Nombres</option>
                                            <option class="text-xs" value="apellidos">Apellidos</option>
                                            <option class="text-xs" value="usuarios">Usuarios</option>
                                            <option class="text-xs" value="rubro">Rubro</option>
                                            <option class="text-xs" value="ruc">RUC</option>
                                            <option class="text-xs" value="telefono">Teléfono</option>
                                            <option class="text-xs" value="celular">Celular</option>
                                            <option class="text-xs" value="fecha_atencion">Fecha de Atención</option>
                                            <option class="text-xs" value="direccion_cliente">Dirección Cliente</option>
                                            <option class="text-xs" value="direccion_empresa">Dirección Empresa</option>
                                            <option class="text-xs" value="emails">Emails</option>
                                            <option class="text-xs" value="web">Web</option>
                                        </select>

                                        <div class="relative w-full">
                                            <input id="texto-buscar" type="text" placeholder="Buscar"
                                                class="w-full border text-xs border-gray-300 border-l-0 rounded-r px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                                            <i class="fas fa-times absolute right-2 top-1 text-xs cursor-pointer" onclick="clearInputBP()"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Numero de cliente:</label>
                                    <input
                                        id="filtro-cliente"
                                        type="text"
                                        placeholder="Ingrese número"
                                        class="w-full text-xs border border-gray-300 rounded px-2 py-1 pr-6 focus:outline-none focus:ring-1 focus:ring-primary">
                                    <i id="icon-x" class="fas fa-times absolute right-2 top-6 text-xs cursor-pointer" onclick="clearInputNC()"></i>
                                    <i id="icon-spinner" class="fas fa-spinner fa-spin absolute right-2 top-6 text-xs hidden"></i>
                                </div>
                                <div class="form-group py-0" id="registros-por-pagina">
                                    <label class="text-xs" for="rowsPerPage">Registros por página:</label>
                                    <select id="rowsPerPage" class="form-control text-xs">
                                        <?php foreach ($valoresPermitidos as $v) {
                                            echo "<option class='text-xs' value='$v'>$v</option>";
                                        } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 h-full">
                                <div class="form-group">
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Filtrar por fecha:</label>
                                    <div class="space-y-1">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="tipoFecha" value="aviso" class="form-radio text-primary h-3 w-3">
                                            <span class="ml-1 text-xs">Fecha de Aviso</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="tipoFecha" value="atencion" class="form-radio text-primary h-3 w-3">
                                            <span class="ml-1 text-xs">Fecha de Atención</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Tipo de Cliente:</label>
                                    <select id="filtro-tipo-cliente" name="origen" class="w-full border border-gray-300 text-xs rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                                        <option class="text-xs" value="">Todos</option>
                                        <option class="text-xs" value="Ninguno">Ninguno</option>
                                        <option class="text-xs" value="Potencial">Potenciales</option>
                                        <option class="text-xs" value="Frecuentes">Frecuentes</option>
                                        <option class="text-xs" value="Ocasionales">Ocasionales</option>
                                        <option class="text-xs" value="Tercerizadores">Tercerizadores</option>
                                        <option class="text-xs" value="Prospecto">Prospecto</option>
                                        <option class="text-xs" value="No Potencial">No Potencial</option>
                                        <option class="text-xs" value="Mal Cliente">Mal Cliente</option>
                                    </select>
                                </div>
                                <button id="btnAgregarCliente" class="bg-primary hover:bg-primary/80 text-white text-xs font-bold py-1 px-3 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    AGREGAR CLIENTE
                                </button>
                            </div>

                            <div class="flex flex-col space-y-2 h-full">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Rango de Fechas:</label>
                                    <input
                                        type="text"
                                        id="fecha-rango"
                                        class="w-full border text-xs border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary"
                                        placeholder="Seleccione fecha"
                                        value="" />
                                </div>
                                <div class="status">
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Status Atención:</label>
                                    <select id="filtro-status" name="prioridad" class="w-full border border-gray-300 text-xs rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                                        <option class="text-xs" value="">Todos</option>
                                        <option class="text-xs" value="Contacto Inicial">Contacto Inicial</option>
                                        <option class="text-xs" value="Retomar Contacto">Retomar Contacto</option>
                                        <option class="text-xs" value="Pendientes por Cotizar">Pendientes por Cotizar</option>
                                        <option class="text-xs" value="Cotizado">Cotizado</option>
                                        <option class="text-xs" value="Venta Realizado">Venta Realizado</option>
                                        <option class="text-xs" value="Venta No Realizado">Venta No Realizado</option>
                                        <option class="text-xs" value="Prod. Entregado">Prod. Entregado</option>
                                    </select>
                                </div>
                                <button onclick="abrirModalEliminados()" class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-medium font-bold py-1 px-3 rounded-full flex items-center justify-center mt-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    REGISTROS ELIMINADOS
                                </button>
                            </div>
                            <div class="flex flex-col space-y-2 h-full h-[28px]">
                                <div class="h-[42.5px] flex items-center justify-between">
                                    <div class="text-xs text-gray-700 space-x-0">
                                        <span id="fecha-desde" class="inline-block mr-4">Desde: </span><br>
                                        <span id="fecha-hasta">Hasta: </span>
                                    </div>
                                    <button id="button-search"
                                        class="flex-shrink-0 bg-primary hover:bg-primary/80 text-white text-xs font-medium py-1 px-3 rounded flex items-center">
                                        <i class="fas fa-search mr-1 text-xs"></i>
                                        Buscar
                                    </button>
                                </div>
                                <div class="origen h-[28px]">
                                    <label class="block text-xs font-medium text-gray-700 mb-0.5">Origen:</label>
                                    <select id="filtro-origen" name="detalle_origen" class="w-full border text-xs border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                                        <option class="text-xs" value="">Todos</option>
                                        <option class="text-xs" value="REFERIDOS">REFERIDOS</option>
                                        <option class="text-xs" value="PAGINA WEB">PAGINA WEB</option>
                                        <option class="text-xs" value="MAILING">MAILING</option>
                                        <option class="text-xs" value="FACEBOOK">FACEBOOK</option>
                                        <option class="text-xs" value="CHAT">CHAT</option>
                                        <option class="text-xs" value="LLAMADAS">LLAMADAS</option>
                                        <option class="text-xs" value="OTROS">OTROS</option>
                                    </select>
                                </div>
                                <div class="mt-auto h-[55px]">
                                    <button id="btnActualizarResultado" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium py-1 px-3 rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        ACTUALIZAR RESULTADO
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-between items-center">
                    <div class="text-xs text-gray-600">
                        Puede modificar el número de Registros por Página:
                        <span id="cantidad-registros" class="font-bold">10</span>
                    </div>
                    <div class="bg-yellow-100 text-xs border-l-4 border-yellow-500 text-yellow-700 p-1 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Actualmente Hay un total de
                        <span id="contador" class="font-bold ml-1">
                            Clientes
                        </span>
                    </div>
                </div>
            </div>
            <?php include 'view/cliente/tabla.php'; ?>
<!-- Este div debe estar dentro del mismo contenedor que la tabla -->
            <div id="pagination-container" 
            class="fixed bottom-0 left-0 w-full bg-gray-100 px-4 py-3 flex items-center justify-center border-t border-gray-200 sm:px-6">


            </div>

            <?php include 'view/cliente/modal.php'; ?>
            <div id="modalPermiso" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-[240px] relative px-4 py-2">
                    <button class="btn-cerrar absolute top-1 right-1 text-gray-500 hover:text-red-500 bg-transparent text-sm">
                        &times;
                    </button>
                    <h2 class="text-xs font-semibold text-red-600 mb-1 mt-2">Sin permisos</h2>
                    <p class="text-[10px] text-gray-700 mb-2">No tienes acceso a este cliente porque no está asociado a tu usuario.</p>
                    <div class="text-right">
                        <button class="btn-cerrar text-[10px] px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Cerrar</button>
                    </div>
                </div>
            </div>
        </main>
        <script src="<?php echo $base?>assets/js/modal-cliente.js"></script>
        <script src="<?php echo $base?>assets/js/modal-distrito.js"></script>
        <script type="module" src="<?php echo $base?>assets/js/listar-clientes/dashboard-clientes.js"></script>
        <script src="<?php echo $base?>script.js"></script>
        <script src="<?php echo $base?>assets/js/listar-clientes/modal-form.js"></script>
        <script src="<?php echo $base?>assets/js/listar-clientes/tabla.js"></script>
        <script src="<?php echo $base?>assets/js/listar-clientes/tabla-eliminados.js"></script>
</body>

</html>
