<div id="modal-cumpleanios" class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center hidden">
<div class="bg-white rounded-lg shadow-xl w-full max-w-sm min-h-[95vh] max-h-[100vh] overflow-y-auto mx-auto !h-[95vh]">
        <div class="bg-primary text-white px-6 py-2 flex justify-between items-center gap-4 border-md">
            <h2 class="text-md font-bold uppercase">Actualizar acciones y cumpleaños de un cliente</h2>
            <button id="btn-cerrar-modal" class="btn-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="form-cumpleanios" action="controller/cumpleanios.php" method="POST" class="p-3 text-sm">
            <div class="container mt-2">
                <div class="grid grid-cols-1 md:grid-cols-1 gap-3">
                    <div class="flex flex-col items-center w-full">
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Cumpleaños:</label>
                        <input type="text" name="cumpleanios" class="datepicker w-auto border border-gray-300 rounded px-2 py-1 text-sm">
                    </div>
                    <div class="flex flex-col items-center w-full mt-4">
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Acciones:</label>
                        <textarea name="accionescliente" placeholder="Acciones realizadas para el cliente"
                            class="w-5/6 max-w-4xl h-24 p-3 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400 resize-y"></textarea>
                    </div>
                </div>
            </div>
            <div class="w-full flex justify-center space-x-2 pt-2">
                <button type="submit" id="btn-submit" class="bg-blue-600 hover:bg-blue-400 text-white font-bold py-2 px-4 rounded">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Botones con solo íconos (como la X de cerrar modal) */
    .btn-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 0;
        border: none;
        box-shadow: none;
    }

    /* Elimina margen del ícono dentro del botón solo-ícono */
    .btn-icon svg {
        margin-right: 0;
    }
</style>