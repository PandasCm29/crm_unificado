<?php
$encabezados = ["ID Cliente", "Razón", "Contactos", "Area/Cargo", "Cumpleaños", "Aniversario", "Acciones", "Opciones"];
include '../cliente/components/utilidades-tabla.php';
?>
<style>
  #tabla-cumpleaneros {
    border-radius: 12px;
    /* Ajusta el valor según tu preferencia (px, rem, etc.) */
    overflow: hidden;
    /* Esto asegura que los bordes redondeados se apliquen correctamente a los hijos */
  }
</style>
<div class="min-h-screen flex items-center justify-center  px-4">
  <div class="w-full max-w-7xl ml-5 justify-center  ">
    <div class="min-h-[100vh]">
      <div class="overflow-x-auto">

        <table id="tabla-cumpleaneros" class="table-responsive w-auto divide-y divide-gray-200 text-xs">
          <thead class="sticky top-0 z-10 bg-gray-800 text-white">
            <tr><?php mostrarTx($encabezados, 0); ?></tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>