<div id="modal-requerimientos" class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center hidden">
<div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="bg-primary text-white px-6 py-2 flex justify-between items-center">
            <h2 id="modalTitulo" class="text-lg font-bold uppercase">Formulario de Requerimiento</h2>
            <button id="btn-cerrar-modal" class="text-white hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 m-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="form-requerimientos" method="POST" enctype="multipart/form-data" class="p-4 text-sm space-y-6">
            <h3 class="text-base font-semibold text-orange-800">Datos del solicitante</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nombre (*)</label>
                    <input type="text" name="nombres" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Apellido</label>
                    <input type="text" name="apellidos"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Empresa</label>
                    <input type="text" name="empresa"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Correo (*)</label>
                    <input type="email" name="email" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Celular (*)</label>
                    <input type="text" name="celular" required pattern="\d{9}" maxlength="9"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" pattern="\d{9}" maxlength="9"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Requerimiento (*)</label>
                    <textarea name="asunto" required
                        class="w-full h-28 p-3 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400 resize-y"></textarea>
                </div>

                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Origen</label>
                    <input type="text" name="derivado"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <input type="text" name="estatus"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary shadow-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Archivo Adjunto (img, pdf)</label>
                    <input name="archivo" type="file"
                        class="block w-full text-sm text-gray-700
               file:mr-4 file:py-2 file:px-4
               file:rounded file:border-0
               file:text-sm file:font-semibold
               file:bg-orange-500 file:text-white
               hover:file:bg-orange-600 hover:cursor-pointer" />
                </div>
            </div>

            <div class="pt-4">
                <button type="submit"
                    class="bg-primary hover:bg-primary/80 text-white font-bold py-2 px-4 rounded w-full transition-colors duration-200">
                    Enviar
                </button>
            </div>
        </form>

    </div>
</div> 