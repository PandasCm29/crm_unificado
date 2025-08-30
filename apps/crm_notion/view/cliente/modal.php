<?php

include 'components/modal_nuevo_distrito.php';
?>
<!-- SweetAlert2 -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<div id="modalCliente" class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-md shadow w-full max-w-5xl max-h-[90vh] overflow-y-auto">
         <div class="bg-primary text-white px-6 py-2 flex justify-between items-center rounded-t-md">
            <h3 id="modalTitulo" class="text-sm font-bold">
                <i class="fas fa-user-plus text-xl"></i>Cliente
            </h3>
            <button id="btnCerrarModal" class="text-white hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 m-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="px-6 pt-4">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                    <ul id="tabsList" class="flex border-b text-sm mb-4">
                        <li class="-mb-px mr-1">
                            <button data-tab="personal" class="tab-btn bg-white inline-block py-2 px-4 font-semibold border-l border-t border-r rounded-t">Información Personal</button>
                        </li>
                        <li class="mr-1">
                            <button data-tab="trabajo" class="tab-btn bg-white inline-block py-2 px-4 text-gray-600 hover:text-gray-800">Trabajo</button>
                        </li>
                        <li class="mr-1">
                            <button data-tab="historial-status" class="tab-btn bg-white inline-block py-2 px-4 text-gray-600 hover:text-gray-800">Historial de Status</button>
                        </li>
                        <li class="mr-1">
                            <button data-tab="historial-antecedentes" class="tab-btn bg-white inline-block py-2 px-4 text-gray-600 hover:text-gray-800">Antecedentes de Clientes</button>
                        </li>
                    </ul>

                </div>

            </div>
        </div>



        <form id="formCliente" method="POST" class="flex flex-col md:flex-row gap-3 px-4" data-mode="" novalidate>
            <div class="flex-[4] w-full">
                <input type="hidden" id="seccionActiva" name="seccion_activa" value="personal">
                <div id="tab-personal" class="tab-content">
                    <div class="grid grid-cols-1 gap-3">
                        <div class="flex space-x-3">
                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">ID CLIENTE</label>
                                <input type="text" name="idcliente" class="w-full bg-gray-100 border border-gray-300 rounded px-2 py-1 text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                            </div>

                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Fecha de Registro</label>
                                <input type="text" name="fatencion" class="w-full bg-gray-100 border border-gray-300 rounded px-2 py-1 text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                            </div>

                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Fecha Aviso</label>
                                <input type="text" name="actual" readonly class="w-full bg-yellow-100 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Nombres<span class="text-red-500">*</span></label>
                                <input type="text" name="nombres" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary" required>
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Apellidos <span class="text-red-500">*</span></label>
                                <input type="text" name="apellidos" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary" required>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Email<span class="text-red-500">*</span></label>
                                <div id="emailFields" class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input type="email" name="email-1" placeholder="ejemplo@gmail.com" required
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                                        <button id="agregar-campo-email" type="button" class="p-1.5 bg-green-100 rounded-full text-green-600 hover:bg-green-200 transition-transform hover:scale-110" title="Agregar otro email">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 m-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Celular Principal <small id="mensaje-cels" class="mensaje-validacion text-sm text-gray-500"></small></label>

                                <div id="celularFields" class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="tel"
                                            name="celular"
                                            placeholder="Formato: +[código de país][número]"
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />

                                        <button
                                            id="agregar-campo-celular"
                                            type="button"
                                            class="p-1.5 bg-green-100 rounded-full text-green-600 hover:bg-green-200 transition-transform hover:scale-110"
                                            title="Agregar otro celular">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4 m-0"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>


                                </div>
                            </div>

                        </div>
                        <div class="flex space-x-3">
                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Teléfono</label>
                                <input type="tel" name="telefono" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-medium text-gray-700">Distrito</label>
                                <div class="flex items-center space-x-2">
                                    <select name="distrito" id="selectDistrito" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                        <option value="">Seleccione distrito</option>
                                    </select>
                                    <a href="#" id="linkAgregarDistrito" class="text-primary hover:underline text-xs">Agregar</a>
                                </div><!--  -->
                            </div>
                        </div>

                        <!--<div class="flex space-x-3">
                        <div class="w-2/3">
                            <label class="block text-xs font-medium text-gray-700">Dirección</label>
                            <input type="text" name="direccion" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="w-1/3">
                            <label class="block text-xs font-medium text-gray-700">Skype</label>
                            <input type="text" name="skype" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>-->

                        <!--<div>
                        <label class="block text-xs font-medium text-gray-700">Referencia</label>
                        <textarea rows="2" name="referencia" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>-->
                        <!-- <div class="flex space-x-4"> -->

                        <!--<div class="w-1/2">
                            <label class="block text-xs font-medium text-gray-700">Código Postal</label>
                            <input type="text" name="postal" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>-->
                        <!-- </div> -->

                        <div class="flex space-x-3">
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700">Ciudad</label>
                                <input type="text" name="ciudad" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700">Provincia</label>
                                <input type="text" name="provincia" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700">País</label>
                                <input type="text" name="pais" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary" value="Perú">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="button" id="btn-update-1" class="bg-primary hover:bg-primary/80 text-white font-bold py-2 px-4 rounded">
                            Actualizar Cliente
                        </button>
                    </div>
                </div>
                <div id="tab-trabajo" class="tab-content hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">ID CLIENTE</label>
                            <input type="text" name="idcliente" class="w-full bg-gray-100 border border-gray-300 rounded px-2 py-1 text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Razón Comercial</label>
                            <input type="text" name="empresa" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Razón Social <span class="text-red-500">*</span></label>
                            <input type="text" name="razon" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">R.U.C.</label>
                            <input id="RUC" type="text" name="ruc" maxlength="11" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">

                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Dirección</label>
                            <input type="text" name="direccion2" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Obs. Dirección</label>
                            <input type="text" name="obsdireccion" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="flex space-x-4 md:col-span-2">
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Cargo</label>
                                <input type="text" name="cargo" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Rubro</label>
                                <input type="text" name="rubro" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Aniversario</label>
                                <input type="text" readonly name="aniversario" class="datepicker w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                        </div>

                        <!-- <div>
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Nº Trabajadores</label>
                            <input type="number" name="num_empleados" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary" min="0">
                        </div> -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-0.5">Página Web</label>
                            <input type="text" name="web"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>



                        <div class="flex space-x-4 md:col-span-2">
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Detalle de Origen</label>
                                <select name="detalle_origen" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Seleccionar</option>
                                    <option value="6">Referidos</option>
                                    <option value="1">Página web</option>
                                    <option value="2">Mailing</option>
                                    <option value="3">Facebook</option>
                                    <option value="4">Chat</option>
                                    <option value="5">Llamadas</option>
                                    <option value="8">Campaña</option>
                                    <option value="7">Otros</option>
                                </select>
                            </div>

                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Tipo Cliente</label>
                                <select name="origen" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Seleccionar</option>
                                    <option value="1">Potenciales</option>
                                    <option value="2">Frecuentes</option>
                                    <option value="3">Ocasionales</option>
                                    <option value="4">Tercerizadores</option>
                                    <option value="5">Prospecto</option>
                                    <option value="6">No Potencial</option>
                                    <option value="7">Mal Cliente</option>
                                </select>
                            </div>
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Estatus Atención</label>
                                <select name="prioridad" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="">Seleccionar</option>
                                    <option value="In">Contacto Inicial</option>
                                    <option value="RC">Retomar Contacto</option>
                                    <option value="PC">Pendientes por Cotizar</option>
                                    <option value="C">Cotizado</option>
                                    <option value="VR">Venta Realizada</option>
                                    <option value="VNR">Venta No Realizada</option>
                                    <option value="PE">Producto Entregado</option>
                                </select>
                            </div>
                            <div class="w-1/3">
                             <label class="block text-xs font-medium text-gray-700 mb-0.5">Cuenta</label>
                              <div class="space-y-1 text-sm">
                                <label class="block">
                                <input type="checkbox" name="Cuenta[]" value="compina" class="mr-1">
                                Compina
                                </label>
                                <label class="block">
                                <input type="checkbox" name="Cuenta[]" value="compipro" class="mr-1">
                                Compipro
                                </label>
                             </div>
                            </div>


                        </div>
                    </div>
                    <br>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Acciones Realizadas</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="carta_presentacion" class="text-primary focus:ring-primary">
                                <span class="ml-1 text-sm">Carta de Presentación</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="catalogo" class="text-primary focus:ring-primary">
                                <span class="ml-1 text-sm">Catálogo</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="correo" class="text-primary focus:ring-primary">
                                <span class="ml-1 text-sm ">Pack Promocional</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between">
                        <!-- <button type="button" id="btn-prev" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Anterior página
                        </button> -->
                        <div class="space-x-2">
                            <button type="button" id="btn-cancel-step" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancelar
                            </button>
                            <button type="submit" id="btn-submit" class="bg-primary hover:bg-primary/80 text-white font-bold py-2 px-4 rounded">
                                Guardar Cliente
                                <span class="spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8h8a8 8 0 01-8 8 8 8 0 01-8-8z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="tab-historial-status" class="tab-content hidden">
                    <div class="mb-5">
                        <div class="flex items-center justify-start mb-2">
                            <h2 class="text-xl font-semibold text-gray-700 mb-2">Historial de Status - ID
                                <input type="text" name="idcliente"
                                    class="bg-transparent border-none px-0 py-0 text-xl font-semibold text-gray-700 w-[90px] pointer-events-none select-none"
                                    readonly>
                            </h2>
                        </div>
                        <textarea name="nuevo_status"
                            class="w-full h-24 p-3 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400 resize-y"
                            placeholder="Escribe el nuevo Status..."></textarea>
                        <button type="button"
                            id="btnAgregarHistorial"
                            class="mt-3 px-4 py-2 bg-orange-500 text-white text-sm font-semibold rounded-md hover:bg-orange-600 transition">
                            ➕ Agregar al historial
                        </button>
                    </div>
                    <div id="lista-historial-status" class="space-y-4 max-h-40 overflow-y-auto pr-1">
                    </div>
                </div>
                <div id="tab-historial-antecedentes" class="tab-content hidden">
                    <div class="mb-5">
                        <div class="flex items-center justify-start mb-2">
                            <h2 class="text-xl font-semibold text-gray-700 mb-2">Antecedentes de Cliente - ID
                                <input type="text" name="idcliente"
                                    class="bg-transparent border-none px-0 py-0 text-xl font-semibold text-gray-700 w-[90px] pointer-events-none select-none"
                                    readonly>
                            </h2>
                        </div>

                        <textarea name="nuevo_antecedente"
                            class="w-full h-24 p-3 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400 resize-y"
                            placeholder="Escribe el nuevo Antecedente..."></textarea>
                        <button type="button"
                            id="btnAgregarAntecedente"
                            class="mt-3 px-4 py-2 bg-orange-500 text-white text-sm font-semibold rounded-md hover:bg-orange-600 transition">
                            ➕ Agregar Antecedente
                        </button>
                    </div>

                    <div id="lista-historial-antecedentes" class="space-y-4 max-h-40 overflow-y-auto pr-1">

                    </div>
                </div>


            </div>

            <!-- Linea divisora del formulario -->
            <div class=" md:block w-px bg-primary"></div>

            <aside class="w-full md:w-1/4 max-w-xs sticky top-4 self-start h-fit bg-gray-50 border border-gray-200 rounded-lg p-4 shadow">
                <p class="text-sm font-semibold mb-2">Resumen del Perfil:</p>
                <div id="tab-perfil" class="tab-content space-y-4 text-sm">
                    <div>
                        <label class="block text-xs font-bold text-black-700 mb-1">TIPO DE CLIENTE<span class="text-red-500"> *</span></label>
                        <textarea name="tipo_cliente" class="w-full text-sm font-semibold border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-black-700 mb-1">POLÍTICA DE PAGO<span class="text-red-500"> *</span></label>
                        <textarea name="politica_pago" class="w-full text-sm font-semibold border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-black-700 mb-1">TRABAJA CON PROVEEDORES<span class="text-red-500"> *</span></label>
                        <textarea name="trabaja_proveedores" class="w-full text-sm font-semibold border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-black-700 mb-1">PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO<span class="text-red-500"> *</span></label>
                        <textarea name="procedimiento_facturacion" class="w-full text-sm font-semibold border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-black-700 mb-1">FRECUENCIA DE COMPRA<span class="text-red-500"> *</span></label>
                        <textarea name="frecuencia_compra" class="w-full text-sm font-semibold border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-black-700 mb-1">ADICIONALES<span class="text-red-500">*</span></label>
                        <textarea name="adicionales" class="w-full text-sm font-semibold border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</div>
<script src="<?php echo $base ?>assets/js/static/flatpickr.min.js" defer></script>
<style>
    .swal-icon-small {
        font-size: 0.8rem !important;
        margin-bottom: 0.2rem !important;
    }

    .swal-title-small {
        font-size: 1rem !important;
        margin-bottom: 0.2rem !important;
        line-height: 1.2 !important;
    }

    .swal-popup-horizontal {
        height: auto !important;
        max-height: 7rem !important;
        border-radius: 0.5rem !important;
    }

    .swal-html-compact {
        margin: 0 !important;
        padding: 0.2rem !important;
        font-size: 0.9rem !important;
        line-height: 1.2 !important;
    }

    .swal2-confirm {
        padding: 0.3rem 0.4rem !important;
        font-size: 0.9rem !important;
    }
</style>