<?php
?>

<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: #3b82f6;
        /* blue-500 */
    }

    .toggle-checkbox:checked+.toggle-label {
        background-color: #3b82f6;
        /* blue-500 */
    }

    .toggle-checkbox:checked~#estado-label {
        color: #3b82f6;
        /* blue-500 */
        content: "Activo";
    }
</style>
<div id="modal-editar-usuario" class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="bg-primary text-white px-6 py-2 flex justify-between items-center">
            <h3 id="modal-titulo" class="text-sm font-bold">
                <i class="fas fa-user-edit text-xl mr-5"></i>Usuario
            </h3>
            <button id="btn-cerrar-modal" class="text-white hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 m-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-usuario" data-mode="Editado" class="p-6 text-sm">
            <div class="form-container">
                <div class="section">
                    <h2 class="section-title">DATOS PERSONALES</h2>
                    <div class="form-group">
                        <label for="nombres">Ingrese los nombres</label>
                        <input type="text" id="nombres" name="nombres" required>
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Ingrese los apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" required>
                    </div>
                    <div class="form-group">
                        <label for="dni">Ingrese el DNI</label>
                        <input type="tel" maxlength="8" id="dni" name="dni" class="border-gray-300 focus:ring-primary" required>
                        <small class="mensaje-validacion text-gray-500 text-xs block mt-1"></small>
                    </div>
                    <div class="form-group">
                        <label for="celular">Ingrese el celular</label>
                        <input type="tel" id="celular" name="celular" placeholder="923612546" class="border-gray-300 focus:ring-primary" required>
                        <small class="mensaje-validacion text-gray-500 text-xs block mt-1"></small>
                    </div>
                    <div class="form-group">
                        <label for="correo">Ingrese el correo</label>
                        <input type="email" id="correo" placeholder="usuario@compina.net" name="correo" class="border-gray-300 focus:ring-primary" required>
                        <small class="mensaje-validacion text-gray-500 text-xs block mt-1"></small>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="section">
                    <h2 class="section-title">DATOS DE JORNADA</h2>
                    <div class="form-group">
                        <label for="usuario">Ingrese el usuario o autogenérelo</label>
                        <div class="input-with-button">
                            <input type="text" id="usuario" name="usuario" required>
                            <button type="button" class="auto-generate-btn" onclick="generarUsuario()" title="Generar correo automáticamente">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Generar nueva contraseña</label>
                        <div class="input-with-button">
                            <div class="password-container">
                                <input type="password" id="password" name="password">
                                <button type="button" class="toggle-password" onclick="togglePassword()" title="Mostrar/Ocultar contraseña">
                                    <i class="fas fa-eye" id="eye-icon"></i>
                                </button>
                            </div>
                            <button type="button" class="auto-generate-btn" onclick="generarPassword()" title="Generar contraseña automáticamente">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tipoempleado">Ingrese el tipo de usuario</label>
                        <select id="tipoempleado" name="tipoempleado" required>
                            <option value="">Seleccione...</option>
                            <option value="operario">Operario</option>
                            <option value="lider">Líder</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="input-area" class="inline-flex items-center">
                            Ingrese el área
                            <span id="areas-legend" class="ml-2 grid grid-cols-4 gap-0.5"></span>
                        </label>
                        <input type="text" id="input-area" name="area" class="form-control" required placeholder="Escribe el área">
                    </div>
                    <div class="form-group-hour">
                        <label for="hora_entrada">Ingrese la hora de entrada y de salida</label>
                        <input type="time" id="hora_entrada" name="hora_entrada" title="Hora de entrada">
                        <input type="time" id="hora_salida" name="hora_salida" title="Hora de salida">
                    </div>
                    <div class="flex items-center space-x-3">
                        <label for="estado" class="text-sm font-medium text-gray-700">Estado</label>
                        <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="estado" id="estado" value="1"
                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 border-gray-300 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <label for="estado"
                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                        <span class="text-sm text-gray-500" id="estado-label">Inactivo</span>
                    </div>
                </div>
                <button type="submit" class="submit-btn flex items-center justify-center">
                    <span class="button-text">Editar</span>
                    <span class="spinner hidden ml-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8h8a8 8 0 01-8 8 8 8 0 01-8-8z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
<script type="module" src="<?php echo $base ?>assets/js/usuarios/formulario.js"></script>
<script>

    function generarUsuario() {
        const nombres = document.getElementById("nombres").value.trim().toLowerCase();
        const apellidos = document.getElementById("apellidos").value.trim().toLowerCase();
        if (!nombres || !apellidos) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor, ingrese los nombres y apellidos para generar el usuario.',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const nombreSeparado = nombres.split(" ");
        const apellidoSeparado = apellidos.split(" ");
        const inicialNombre = nombreSeparado[0].charAt(0);
        const primerApellido = apellidoSeparado[0];
        const inicialSegundoApellido = apellidoSeparado.length > 1 ? apellidoSeparado[1].charAt(0) : "";

        const usuario = (inicialNombre + primerApellido + inicialSegundoApellido).toLowerCase();
        document.getElementById("usuario").value = usuario;
    }

    function generarPassword() {
        const nombres = document.getElementById("nombres").value.trim().toLowerCase();
        const apellidos = document.getElementById("apellidos").value.trim().toLowerCase();
        if (!nombres || !apellidos) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor, ingrese los nombres y apellidos para generar la contraseña.',
                confirmButtonText: 'Entendido'
            });
            return;
        }


        const nombreSeparado = nombres.split(" ");
        const primerNombre = nombreSeparado[0];
        const primerApellido = apellidos.split(" ")[0];

        const password = (primerNombre + primerApellido + "25").toLowerCase();

        document.getElementById("password").value = password;
    }

    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const eyeIcon = document.getElementById("eye-icon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    }

</script>