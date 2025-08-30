<div id="modalEditarRequerimiento"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
  <div class="bg-white rounded-lg shadow-lg w-[95vw] max-w-[1000px] relative overflow-y-auto max-h-[90vh]">
    <form id="formEditarRequerimiento" action="#" method="POST" class="p-6 text-sm">
      <input type="hidden" name="idconsulta" value="">

      <div class="mb-6 border-b border-gray-200 pb-2">
        <h2 class="text-lg font-semibold text-gray-800">Información Personal</h2>
      </div>

      <div class="flex space-x-2 mb-6 justify-end">
        <button type="button" id="button-actualizar-datos"
          class="bg-primary hover:bg-primary/80 text-white text-xs font-medium py-1 px-3 rounded flex items-center space-x-1">
          <i class="fas fa-sync-alt"></i><span>Actualizar datos</span>
        </button>
        <button type="button" id="button-cancel"
          class="bg-primary hover:bg-primary/80 text-white text-xs font-medium py-1 px-3 rounded flex items-center space-x-1">
          <i class="fas fa-times"></i><span>Cancelar</span>
        </button>
        <button type="button" id="button-add-new"
          class="bg-primary hover:bg-primary/80 text-white text-xs font-medium py-1 px-3 rounded flex items-center space-x-1">
          <i class="fa-solid fa-plus"></i><span>Guardar nuevo</span>
        </button>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-medium text-gray-700">Empresa</label>
          <input type="text" name="empresa"
            class="w-full bg-gray-100 border border-gray-300 rounded px-2 py-2 text-sm text-gray-500"
            readonly>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">F. Nivel urgencia</label>
          <input type="text" name="fecha_consulta"
            class="datepicker w-full bg-gray-100 border border-gray-300 rounded px-2 py-2 text-sm text-gray-500"
            readonly>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-700">Nombres</label>
          <input type="text" name="nombres"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">F. Atención</label>
          <input type="date" name="fatencion"
            class="datepicker w-full bg-yellow-100 border border-gray-300 rounded px-2 py-2 text-sm"
            placeholder="Selecciona fecha">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Teléfono</label>
          <input type="tel" name="telefono"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Apellidos</label>
          <input type="text" name="apellidos"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Celular</label>
          <input type="tel" name="celular_consulta"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Tipo Cliente</label>
          <select name="prioridad"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
            <option value="" disabled selected>Seleccione tipo</option>
            <option value="PC">Pendientes por Cotizar</option>
            <option value="C">Cotizado</option>
            <option value="VNR">Venta No Realizado</option>
            <option value="VR">Venta Realizada</option>
            <option value="In">Contacto Inicial</option>
            <option value="RC">Retomar Contacto</option>
            <option value="PE">Prod. Entregado</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Detalle Origen</label>
          <select name="detalle_origen"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
            <option value="" disabled selected>Seleccione detalle origen</option>
            <option value="1">Página web</option>
            <option value="2">Chat</option>
            <option value="3">Llamada</option>
            <option value="4">Mailing</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Estatus Atención</label>
          <select name="status"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
            <option value="" disabled selected>Seleccione estatus</option>
            <option value="1">Retomar Contacto</option>
            <option value="2">Contacto Inicial</option>
            <option value="3">Seguimiento</option>
            <option value="4">Cerrado</option>
          </select>
        </div>

        <div class="col-span-2">
          <label class="block text-xs font-medium text-gray-700">Email</label>
          <input type="email" name="email"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
        </div>

        <div class="col-span-2 row-span-1">
          <label class="block text-xs font-medium text-gray-700">Requerimientos</label>
          <textarea name="asunto" rows="3"
            class="w-full border border-gray-300 rounded px-2 py-2 text-sm"
            placeholder="Escribe los requerimientos"></textarea>
        </div>
      </div>

      <div class="mt-8">
        <label class="block text-xs font-medium text-gray-700">Comentarios</label>
        <textarea id="textAreaHist" rows="3"
          class="w-full border border-gray-300 rounded px-2 py-2 text-sm"
          placeholder="Agrega un comentario"></textarea>
        <button type="button"
          id="btnAgregarHistorial"
          class="mt-4 bg-primary hover:bg-primary/80 text-white font-medium py-1 px-3 rounded text-xs flex items-center space-x-1">
          <i class="fa-solid fa-plus mr-1"></i>
          <span>Agregar Comentario al historial</span>
        </button>

        <div id="lista-historial-status" class="space-y-4 max-h-48 overflow-y-auto pr-2 mt-4">
        </div>
      </div>
    </form>
  </div>
</div>