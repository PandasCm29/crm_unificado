<!-- Modal Nuevo Distrito -->
<div id="modalNuevoDistrito"
    class="fixed inset-0 bg-black bg-opacity-50 z-[10000] flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold mb-4">Agregar Nuevo Distrito</h3>
        <label for="inputNuevoDistrito" class="block text-sm font-medium text-gray-700 mb-1">
        Nombre del Distrito
        </label>
        <input id="inputNuevoDistrito"
            type="text"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary mb-4"
            placeholder="Ingrese nombre"/>

        <!-- NUEVO: código postal -->
        <label for="inputCodigoPostal" class="block text-sm font-medium text-gray-700 mb-1">
        Código Postal
        </label>
        <input id="inputCodigoPostal"
            type="text"
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary mb-4"
            placeholder="Ingrese código postal"/>

        <div class="flex justify-end space-x-2">
            <button id="btnCancelarNuevoDistrito"
                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded">
                Cancelar
            </button>
            <button id="btnGuardarNuevoDistrito"
                    class="px-4 py-2 bg-primary hover:bg-primary/80 text-white font-medium rounded">
                Guardar
            </button>
        </div>
    </div>
</div>