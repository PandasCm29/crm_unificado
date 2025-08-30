<?php
$encabezados = ["ID", "Nombre Completo", "Usuario", "Area", "Estado", "Acciones"];
include_once '../cliente/components/utilidades-tabla.php';
?>

<div class="w-full flex justify-center px-4">
  <div class="w-full max-w-6xl bg-white rounded-2xl shadow-xl overflow-hidden">
    <table class="tabla-mini w-full text-sm text-gray-800">
      <thead class="sticky top-0 z-20 bg-yellow-500 text-white">
        <tr>
          <?php mostrarTx($encabezados, 0); ?>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200" id="tabla-usuarios-body">

      </tbody>
    </table>

    <div id="pagination-container" class="max-w-[95vw] sticky left-[51px] bg-gray-100 px-4 py-3 flex items-center justify-center border-t border-gray-200 sm:px-6">
    </div>
  </div>
</div>

<script type="module" src="<?php echo $base?>assets/js/usuarios/tabla.js"></script>