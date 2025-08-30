<?php
require_once __DIR__ . '/../../config/auth.php';

requireRegularArea();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CUMPLEAÑOS-ANIVERSARIO CLIENTES</title>
  <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"> -->
  <link href="<?php echo $base ?>assets/css/static/font-awesome.6.5.0.all.min.css" defer rel="stylesheet">

  <link href="<?php echo $base ?>assets/css/static/flatpickr.min.css" defer rel="stylesheet">
  <script src="<?php echo $base ?>assets/js/static/flatpickr.min.js" defer></script>
  <script src="<?php echo $base ?>assets/js/static/sweetalert2.all.min.js" defer></script>

  <link rel="stylesheet" href="<?php echo $base ?>assets/css/output.css">

  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: white;
      margin: 15% auto;
      padding: 20px;
      width: 80%;
      max-width: 400px;
      border-radius: 12px;
    }

    .close-btn {
      float: right;
      cursor: pointer;
      font-weight: bold;
      font-size: 20px;
    }

    #tabla-cumpleaneros {
      width: 100% !important;
      /* Ocupa todo el ancho */
      table-layout: auto;
      /* Las celdas se ajustan al contenido */
    }

    #contenidoTabla {
      border-radius: 12px;
    }
  </style>

</head>

<body class="bg-lightAlt min-h-screen flex flex-col">

  <header class="bg-primary text-white py-1 md:py-3 px-6 shadow-md cabecera">
    <h1 class="text-2xl font-bold">
      <i class="fas fa-users mr-3"></i>
      CUMPLEAÑOS - ANIVERSARIO CLIENTES
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
        <main class="flex flex-1 justify-center items-center p-4 ">
        <div id="select-mes" class="max-w-sm bg-white p-4 rounded shadow">
        <div class="flex flex-col items-center text-center w-full max-w-xs mx-auto space-y-3">
  <label class="text-sm font-medium text-gray-700">
    Seleccione un mes:
  </label>

  <select
    class="text-sm w-full border text-lg border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-primary">
    <option value="1">Enero</option>
    <option value="2">Febrero</option>
    <option value="3">Marzo</option>
    <option value="4">Abril</option>
    <option value="5">Mayo</option>
    <option value="6">Junio</option>
    <option value="7">Julio</option>
    <option value="8">Agosto</option>
    <option value="9">Septiembre</option>
    <option value="10">Octubre</option>
    <option value="11">Noviembre</option>
    <option value="12">Diciembre</option>
  </select>

  <button
    class="bg-primary text-white text-sm font-semibold py-1 px-4 rounded hover:bg-orange-600 transition duration-200"
    id="btnBuscar">
    Buscar
  </button>
</div>

      </div>
      <div class="w-max hidden mt-12">
        <div id="contenidoTabla" class="bg-white text-center p-4 rounded shadow mt-1 mx-auto   w-[1000px]">
          <label class="text-sm font-medium text-gray-700">Filtrar por mes tomando en cuenta el cumpleaños y aniversario</label><br><br>
          <button id="btn1" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Enero
          </button>
          <button id="btn2" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Febrero
          </button>
          <button id="btn3" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Marzo
          </button>
          <button id="btn4" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Abril
          </button>
          <button id="btn5" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Mayo
          </button>
          <button id="btn6" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Junio
          </button>
          <button id="btn7" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Julio
          </button>
          <button id="btn8" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Agosto
          </button>
          <button id="btn9" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Septiembre
          </button>
          <button id="btn10" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Octubre
          </button>
          <button id="btn11" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Noviembre
          </button>
          <button id="btn12" class="bg-primary text-white text-sm font-semibold py-1 px-3 rounded hover:bg-orange-600">
            Diciembre
          </button>
        </div>
        <div class="mt-5">
          <?php include 'tabla.php'; ?>
        </div>
      </div>
      <?php include 'modal.php'; ?>
    </main>
  </div>

  <!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
  <script type="module" src="../../assets/js/cumpleanos-aniversario/inicio-modal.js"></script>
</body>

</html>

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

</style>

