<?php
require_once __DIR__ . '/../../config/auth.php';

requireLogin();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <link rel="icon" href="<?php echo $base?>icons/favicon.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CUMPLEAÑOS-ANIVERSARIO CLIENTES</title>
  <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"> -->
  <link href="<?php echo $base?>assets/css/static/font-awesome.6.5.0.all.min.css" defer rel="stylesheet">
  
  <link href="<?php echo $base?>assets/css/static/flatpickr.min.css" defer rel="stylesheet">
  <script src="<?php echo $base?>assets/js/static/flatpickr.min.js" defer></script>
  <script src="<?php echo $base?>assets/js/static/sweetalert2.all.min.js" defer></script>

  <link rel="stylesheet" href="<?php echo $base?>assets/css/output.css">

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

    <a href="<?php echo $base?>logout.php" class="flex items-center px-3 py-2 rounded-lg filter brightness-100
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
            <li onclick="window.location.href = '<?php echo $base?>index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Listar Clientes</li>
            <li onclick="window.location.href = '<?php echo $base?>view/requerimientos/index.php'" class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Requerimientos por atender</li>
            <li class="hover:bg-gray-700 text-white p-2 rounded cursor-pointer">Cumpleaños/Aniversario Clientes</li>
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

    <main class="flex flex-1 justify-center items-center p-4">
      <div id="select-mes" class="container w-auto bg-white p-2 rounded shadow">
        <div>
          <label class="text-sm font-medium text-gray-700">Seleccione un mes:</label>
          <select class="text-sm w-auto border text-lg border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-primary">
            <option class="text-sm" value="1">Enero</option>
            <option class="text-sm" value="2">Febrero</option>
            <option class="text-sm" value="3">Marzo</option>
            <option class="text-sm" value="4">Abril</option>
            <option class="text-sm" value="5">Mayo</option>
            <option class="text-sm" value="6">Junio</option>
            <option class="text-sm" value="7">Julio</option>
            <option class="text-sm" value="8">Agosto</option>
            <option class="text-sm" value="9">Septiembre</option>
            <option class="text-sm" value="10">Octubre</option>
            <option class="text-sm" value="11">Noviembre</option>
            <option class="text-sm" value="12">Diciembre</option>
          </select>
          <div class="text-center mt-2">
            <button class="text-sm bg-primary text-white text-lg font-semibold py-1 px-2 rounded hover:bg-orange-600" id="btnBuscar">
              Buscar
            </button>
          </div>
        </div>
      </div>
      <div class="w-full hidden">
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